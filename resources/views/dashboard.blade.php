@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-4">欢迎，{{ Auth::user()->name }}！</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- 用户信息卡片 -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">账户信息</h3>
                        <p class="text-blue-600">邮箱: {{ Auth::user()->email }}</p>
                        @if(Auth::user()->github_username)
                            <p class="text-blue-600">GitHub: {{ Auth::user()->github_username }}</p>
                        @endif
                        <p class="text-blue-600">注册时间: {{ Auth::user()->created_at->format('Y-m-d') }}</p>
                    </div>

                    <!-- GitHub 绑定状态 -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800 mb-2">GitHub 集成</h3>
                        @if(Auth::user()->github_id)
                            <p class="text-green-600 mb-2">✓ 已绑定 GitHub 账户</p>
                            <a href="{{ route('auth.github.unbind') }}" 
                               class="text-red-600 hover:text-red-800 text-sm">解绑账户</a>
                        @else
                            <p class="text-yellow-600 mb-2">未绑定 GitHub 账户</p>
                            <a href="{{ route('auth.github.bind') }}" 
                               class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                                绑定 GitHub
                            </a>
                        @endif
                    </div>

                    <!-- 快速操作 -->
                    <div class="bg-purple-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-purple-800 mb-2">快速操作</h3>
                        <div class="space-y-2">
                            <a href="#" class="block text-purple-600 hover:text-purple-800">创建新 Gist</a>
                            <a href="#" class="block text-purple-600 hover:text-purple-800">浏览我的 Gist</a>
                            <a href="#" class="block text-purple-600 hover:text-purple-800">同步 GitHub Gist</a>
                        </div>
                    </div>
                </div>

                <!-- 最近活动 -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">最近活动</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">暂无活动记录</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
