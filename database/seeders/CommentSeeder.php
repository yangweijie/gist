<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Gist;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $gists = Gist::all();

        if ($users->isEmpty() || $gists->isEmpty()) {
            $this->command->info('No users or gists found. Skipping comment seeding.');
            return;
        }

        // 为每个 Gist 创建一些评论
        foreach ($gists as $gist) {
            // 创建主评论
            $mainComments = [];
            for ($i = 0; $i < rand(2, 5); $i++) {
                $user = $users->random();
                $comment = Comment::create([
                    'user_id' => $user->id,
                    'gist_id' => $gist->id,
                    'content' => $this->getRandomComment(),
                    'is_approved' => true,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
                $mainComments[] = $comment;
            }

            // 为一些主评论创建回复
            foreach ($mainComments as $mainComment) {
                if (rand(0, 1)) { // 50% 概率有回复
                    for ($j = 0; $j < rand(1, 3); $j++) {
                        $user = $users->random();
                        Comment::create([
                            'user_id' => $user->id,
                            'gist_id' => $gist->id,
                            'parent_id' => $mainComment->id,
                            'content' => $this->getRandomReply(),
                            'is_approved' => true,
                            'created_at' => $mainComment->created_at->addMinutes(rand(10, 1440)),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Comments seeded successfully!');
    }

    /**
     * 获取随机评论内容
     */
    private function getRandomComment(): string
    {
        $comments = [
            '这个代码片段很有用，谢谢分享！',
            '我遇到了类似的问题，这个解决方案很棒。',
            '代码写得很清晰，学到了新东西。',
            '有没有考虑过性能优化的问题？',
            '这个方法在我的项目中也能用到。',
            '感谢分享，正好解决了我的问题。',
            '代码风格很好，值得学习。',
            '这个实现方式很巧妙！',
            '有没有更简洁的写法？',
            '测试过兼容性吗？',
            '这个功能很实用，收藏了。',
            '代码逻辑很清楚，容易理解。',
        ];

        return $comments[array_rand($comments)];
    }

    /**
     * 获取随机回复内容
     */
    private function getRandomReply(): string
    {
        $replies = [
            '同意你的观点！',
            '我也是这么想的。',
            '谢谢你的建议。',
            '确实需要考虑这个问题。',
            '我试试看这个方法。',
            '很好的补充！',
            '学到了，谢谢！',
            '有道理，我会注意的。',
            '这个想法不错。',
            '感谢回复！',
            '我也遇到过类似情况。',
            '值得深入研究。',
        ];

        return $replies[array_rand($replies)];
    }
}
