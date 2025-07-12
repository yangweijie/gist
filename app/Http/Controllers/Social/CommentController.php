<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Gist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 获取 Gist 的评论列表
     */
    public function index(Gist $gist, Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);

        $comments = Comment::where('gist_id', $gist->id)
            ->approved()
            ->rootComments()
            ->with(['user', 'replies' => function ($query) {
                $query->approved()->with('user')->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalComments = Comment::where('gist_id', $gist->id)
            ->approved()
            ->rootComments()
            ->count();

        $hasMore = ($offset + $limit) < $totalComments;

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $html = view('partials.comments', compact('comments', 'gist'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $comments->count(),
                'hasMore' => $hasMore,
                'total' => $totalComments
            ]);
        }

        return view('partials.comments', compact('comments', 'gist'));
    }

    /**
     * 发表评论
     */
    public function store(Request $request, Gist $gist)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id'
        ]);

        $userId = Auth::id();

        // 防刷机制：检查最近的评论频率
        $recentComments = Comment::where('user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(1))
            ->count();

        if ($recentComments >= 5) {
            return response()->json([
                'success' => false,
                'message' => '评论过于频繁，请稍后再试'
            ], 429);
        }

        // 检查父评论是否存在且属于同一个 Gist
        if ($request->parent_id) {
            $parentComment = Comment::find($request->parent_id);
            if (!$parentComment || $parentComment->gist_id !== $gist->id) {
                return response()->json([
                    'success' => false,
                    'message' => '父评论不存在'
                ], 404);
            }

            // 检查是否可以回复（最多3层嵌套）
            if (!$parentComment->canReply()) {
                return response()->json([
                    'success' => false,
                    'message' => '回复层级过深'
                ], 400);
            }
        }

        // 简单的垃圾评论检测
        $content = $request->content;
        if ($this->isSpamContent($content)) {
            return response()->json([
                'success' => false,
                'message' => '评论内容不符合规范'
            ], 400);
        }

        $comment = Comment::create([
            'user_id' => $userId,
            'gist_id' => $gist->id,
            'parent_id' => $request->parent_id,
            'content' => $content,
            'is_approved' => true, // 暂时自动审核通过
        ]);

        $comment->load('user');

        // 更新 Gist 的评论数缓存
        $commentCount = Comment::getCommentCount($gist->id);
        $gist->update(['comments_count' => $commentCount]);

        return response()->json([
            'success' => true,
            'message' => '评论发表成功',
            'comment' => $comment
        ]);
    }

    /**
     * 删除评论
     */
    public function destroy(Comment $comment)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $userId = Auth::id();

        // 检查权限：只能删除自己的评论
        if ($comment->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => '无权删除此评论'
            ], 403);
        }

        $gistId = $comment->gist_id;
        
        // 删除评论及其所有回复
        $this->deleteCommentAndReplies($comment);

        // 更新 Gist 的评论数缓存
        $gist = Gist::find($gistId);
        if ($gist) {
            $commentCount = Comment::getCommentCount($gistId);
            $gist->update(['comments_count' => $commentCount]);
        }

        return response()->json([
            'success' => true,
            'message' => '评论删除成功'
        ]);
    }

    /**
     * 审核评论（管理员功能）
     */
    public function approve(Comment $comment)
    {
        // 这里可以添加管理员权限检查
        $comment->approve();

        return response()->json([
            'success' => true,
            'message' => '评论审核通过'
        ]);
    }

    /**
     * 拒绝评论（管理员功能）
     */
    public function reject(Comment $comment)
    {
        // 这里可以添加管理员权限检查
        $comment->reject();

        return response()->json([
            'success' => true,
            'message' => '评论已拒绝'
        ]);
    }

    /**
     * 简单的垃圾评论检测
     */
    private function isSpamContent($content)
    {
        $spamKeywords = [
            '广告', '推广', '加微信', '加QQ', '刷赞', '刷粉',
            '代刷', '代练', '外挂', '私服', '色情', '赌博'
        ];

        $content = strtolower($content);
        
        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        // 检查是否包含过多的链接
        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 2) {
            return true;
        }

        // 检查是否重复字符过多
        if (preg_match('/(.)\1{10,}/', $content)) {
            return true;
        }

        return false;
    }

    /**
     * 递归删除评论及其回复
     */
    private function deleteCommentAndReplies(Comment $comment)
    {
        // 先删除所有回复
        foreach ($comment->replies as $reply) {
            $this->deleteCommentAndReplies($reply);
        }
        
        // 删除评论本身
        $comment->delete();
    }
}
