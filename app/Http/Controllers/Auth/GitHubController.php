<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    /**
     * 重定向到 GitHub OAuth 页面
     */
    public function redirectToGitHub()
    {
        return Socialite::driver('github')
            ->scopes(['user:email', 'gist'])
            ->redirect();
    }

    /**
     * 处理 GitHub OAuth 回调
     */
    public function handleGitHubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            // 查找已存在的用户
            $user = User::where('github_id', $githubUser->id)
                ->orWhere('email', $githubUser->email)
                ->first();

            if ($user) {
                // 更新用户的 GitHub 信息
                $user->update([
                    'github_id' => $githubUser->id,
                    'github_username' => $githubUser->nickname,
                    'github_token' => $githubUser->token,
                    'avatar_url' => $githubUser->avatar,
                    'name' => $user->name ?: $githubUser->name,
                ]);
            } else {
                // 创建新用户
                $user = User::create([
                    'name' => $githubUser->name ?: $githubUser->nickname,
                    'email' => $githubUser->email,
                    'github_id' => $githubUser->id,
                    'github_username' => $githubUser->nickname,
                    'github_token' => $githubUser->token,
                    'avatar_url' => $githubUser->avatar,
                    'password' => Hash::make(uniqid()), // 随机密码
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);

            return redirect()->intended('/dashboard')
                ->with('success', 'GitHub 登录成功！');

        } catch (\Exception $e) {
            return redirect('/login')
                ->with('error', 'GitHub 登录失败：' . $e->getMessage());
        }
    }

    /**
     * 绑定 GitHub 账户（已登录用户）
     */
    public function bindGitHub()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        return Socialite::driver('github')
            ->scopes(['user:email', 'gist'])
            ->redirect();
    }

    /**
     * 处理 GitHub 绑定回调
     */
    public function handleBindCallback()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        try {
            $githubUser = Socialite::driver('github')->user();
            $user = Auth::user();

            // 检查 GitHub 账户是否已被其他用户绑定
            $existingUser = User::where('github_id', $githubUser->id)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return redirect('/profile')
                    ->with('error', '该 GitHub 账户已被其他用户绑定');
            }

            // 绑定 GitHub 账户
            $user->update([
                'github_id' => $githubUser->id,
                'github_username' => $githubUser->nickname,
                'github_token' => $githubUser->token,
                'avatar_url' => $githubUser->avatar,
            ]);

            return redirect('/profile')
                ->with('success', 'GitHub 账户绑定成功！');

        } catch (\Exception $e) {
            return redirect('/profile')
                ->with('error', 'GitHub 绑定失败：' . $e->getMessage());
        }
    }

    /**
     * 解绑 GitHub 账户
     */
    public function unbindGitHub()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $user->update([
            'github_id' => null,
            'github_username' => null,
            'github_token' => null,
        ]);

        return redirect('/profile')
            ->with('success', 'GitHub 账户解绑成功！');
    }
}
