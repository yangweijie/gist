@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 页面标题和操作 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">我的 Gist</h1>
            <a href="{{ route('gists.create') }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                创建新 Gist
            </a>
        </div>

        <!-- 搜索和筛选 -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('gists.my') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 搜索框 -->
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="搜索我的 Gist..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- 可见性筛选 -->
                    <div>
                        <select name="visibility" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有可见性</option>
                            <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>公开</option>
                            <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>私有</option>
                        </select>
                    </div>

                    <!-- 同步状态筛选 -->
                    <div>
                        <select name="sync_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有状态</option>
                            <option value="synced" {{ request('sync_status') === 'synced' ? 'selected' : '' }}>已同步</option>
                            <option value="local" {{ request('sync_status') === 'local' ? 'selected' : '' }}>仅本地</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                        筛选
                    </button>
                    @if(request()->hasAny(['search', 'visibility', 'sync_status']))
                        <a href="{{ route('gists.my') }}" class="text-gray-500 hover:text-gray-700">
                            清除筛选
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 批量操作 -->
        @if($gists->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <form id="bulk-form" action="{{ route('gists.bulk-action') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <input type="checkbox" id="select-all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="select-all" class="text-sm text-gray-700">全选</label>
                            <span id="selected-count" class="text-sm text-gray-500">已选择 0 项</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select name="action" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">选择操作</option>
                                <option value="make_public">设为公开</option>
                                <option value="make_private">设为私有</option>
                                @if(Auth::user()->github_token)
                                    <option value="sync_to_github">同步到 GitHub</option>
                                @endif
                                <option value="delete">删除</option>
                            </select>
                            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors text-sm">
                                执行
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Gist 列表 -->
        @if($gists->count() > 0)
            <div class="space-y-4">
                @foreach($gists as $gist)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start">
                                <!-- 选择框 -->
                                <input type="checkbox" name="gist_ids[]" value="{{ $gist->id }}" 
                                       class="gist-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1 mr-4">

                                <div class="flex-1">
                                    <!-- Gist 标题和描述 -->
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <a href="{{ route('gists.show', $gist) }}" class="hover:text-indigo-600">
                                                    {{ $gist->title }}
                                                </a>
                                            </h3>
                                            @if($gist->description)
                                                <p class="text-gray-600 text-sm">{{ $gist->description }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2 ml-4">
                                            <a href="{{ route('gists.edit', $gist) }}" 
                                               class="text-indigo-600 hover:text-indigo-800 text-sm">编辑</a>
                                            <form action="{{ route('gists.destroy', $gist) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('确定要删除这个 Gist 吗？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">删除</button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- 语言和文件名 -->
                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium mr-2">
                                            {{ $gist->language }}
                                        </span>
                                        <span>{{ $gist->filename }}</span>
                                    </div>

                                    <!-- 标签 -->
                                    @if($gist->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1 mb-3">
                                            @foreach($gist->tags->take(5) as $tag)
                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                            @if($gist->tags->count() > 5)
                                                <span class="text-gray-500 text-xs">+{{ $gist->tags->count() - 5 }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- 统计信息和状态 -->
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <div class="flex items-center space-x-4">
                                            <span>👁 {{ $gist->views_count }}</span>
                                            <span>❤️ {{ $gist->likes_count }}</span>
                                            <span>💬 {{ $gist->comments_count }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($gist->is_synced)
                                                <span class="text-green-600 text-xs bg-green-100 px-2 py-1 rounded">🔗 已同步</span>
                                            @else
                                                <span class="text-gray-600 text-xs bg-gray-100 px-2 py-1 rounded">📱 仅本地</span>
                                            @endif
                                            @if($gist->is_public)
                                                <span class="text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">🌐 公开</span>
                                            @else
                                                <span class="text-orange-600 text-xs bg-orange-100 px-2 py-1 rounded">🔒 私有</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 时间信息 -->
                                    <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-500">
                                        <span>创建于 {{ $gist->created_at->format('Y-m-d H:i') }}</span>
                                        @if($gist->updated_at->ne($gist->created_at))
                                            <span class="mx-2">•</span>
                                            <span>更新于 {{ $gist->updated_at->format('Y-m-d H:i') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 分页 -->
            <div class="mt-8">
                {{ $gists->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-500 text-lg mb-4">
                    @if(request()->hasAny(['search', 'visibility', 'sync_status']))
                        没有找到符合条件的 Gist
                    @else
                        你还没有创建任何 Gist
                    @endif
                </div>
                <a href="{{ route('gists.create') }}" 
                   class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                    创建第一个 Gist
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// 批量选择功能
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const gistCheckboxes = document.querySelectorAll('.gist-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkForm = document.getElementById('bulk-form');

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.gist-checkbox:checked');
        selectedCountSpan.textContent = `已选择 ${checkedBoxes.length} 项`;
        
        // 将选中的 ID 添加到表单中
        const existingInputs = bulkForm.querySelectorAll('input[name="gist_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'gist_ids[]';
            input.value = checkbox.value;
            bulkForm.appendChild(input);
        });
    }

    selectAllCheckbox.addEventListener('change', function() {
        gistCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    gistCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            
            // 更新全选状态
            const checkedCount = document.querySelectorAll('.gist-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === gistCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < gistCheckboxes.length;
        });
    });

    // 表单提交验证
    bulkForm.addEventListener('submit', function(e) {
        const selectedGists = document.querySelectorAll('.gist-checkbox:checked');
        const action = bulkForm.querySelector('select[name="action"]').value;
        
        if (selectedGists.length === 0) {
            e.preventDefault();
            alert('请选择要操作的 Gist');
            return;
        }
        
        if (!action) {
            e.preventDefault();
            alert('请选择要执行的操作');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm(`确定要删除选中的 ${selectedGists.length} 个 Gist 吗？此操作不可恢复。`)) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endsection
