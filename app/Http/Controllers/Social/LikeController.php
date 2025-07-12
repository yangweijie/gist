<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Gist;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * 切换点赞状态
     */
    public function toggle(Request $request, Gist $gist)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $userId = Auth::id();
        
        // 检查是否可以点赞（不能给自己的 Gist 点赞）
        if ($gist->user_id === $userId) {
            return response()->json([
                'success' => false,
                'message' => '不能给自己的 Gist 点赞'
            ], 403);
        }

        // 防刷机制：检查最近的点赞频率
        $recentLikes = Like::where('user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(1))
            ->count();

        if ($recentLikes >= 10) {
            return response()->json([
                'success' => false,
                'message' => '操作过于频繁，请稍后再试'
            ], 429);
        }

        $isLiked = Like::toggle($userId, $gist->id);
        $likeCount = Like::getLikeCount($gist->id);

        // 更新 Gist 的点赞数缓存
        $gist->update(['likes_count' => $likeCount]);

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'like_count' => $likeCount,
            'message' => $isLiked ? '点赞成功' : '取消点赞'
        ]);
    }

    /**
     * 获取点赞状态
     */
    public function status(Gist $gist)
    {
        $isLiked = Auth::check() ? Like::isLiked(Auth::id(), $gist->id) : false;
        $likeCount = Like::getLikeCount($gist->id);

        return response()->json([
            'is_liked' => $isLiked,
            'like_count' => $likeCount
        ]);
    }

    /**
     * 获取用户的点赞列表
     */
    public function userLikes(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $likes = Like::where('user_id', Auth::id())
            ->with(['gist.user', 'gist.tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'likes' => $likes
        ]);
    }

    /**
     * 批量获取多个 Gist 的点赞状态
     */
    public function batchStatus(Request $request)
    {
        $request->validate([
            'gist_ids' => 'required|array',
            'gist_ids.*' => 'integer|exists:gists,id'
        ]);

        $gistIds = $request->gist_ids;
        $userId = Auth::id();

        $likes = [];
        $counts = [];

        if ($userId) {
            $userLikes = Like::where('user_id', $userId)
                ->whereIn('gist_id', $gistIds)
                ->pluck('gist_id')
                ->toArray();
            
            foreach ($gistIds as $gistId) {
                $likes[$gistId] = in_array($gistId, $userLikes);
            }
        } else {
            foreach ($gistIds as $gistId) {
                $likes[$gistId] = false;
            }
        }

        // 获取点赞数
        $likeCounts = Like::whereIn('gist_id', $gistIds)
            ->groupBy('gist_id')
            ->selectRaw('gist_id, count(*) as count')
            ->pluck('count', 'gist_id')
            ->toArray();

        foreach ($gistIds as $gistId) {
            $counts[$gistId] = $likeCounts[$gistId] ?? 0;
        }

        return response()->json([
            'success' => true,
            'likes' => $likes,
            'counts' => $counts
        ]);
    }
}
