@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">创建新标签</h1>
            <p class="text-gray-600 mt-2">创建一个新的标签来分类 Gist</p>
        </div>

        <!-- 表单 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form method="POST" action="{{ route('tags.store') }}" class="space-y-6">
                @csrf

                <!-- 标签名称 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        标签名称 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           maxlength="50"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                           placeholder="输入标签名称...">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">标签名称应该简洁明了，最多 50 个字符</p>
                </div>

                <!-- 标签描述 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        标签描述
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              maxlength="255"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                              placeholder="描述这个标签的用途...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">可选，最多 255 个字符</p>
                </div>

                <!-- 标签颜色 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        标签颜色 <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($colors as $colorKey => $colorName)
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" 
                                       name="color" 
                                       value="{{ $colorKey }}"
                                       {{ old('color', 'blue') === $colorKey ? 'checked' : '' }}
                                       class="sr-only">
                                <div class="color-option {{ $colorKey === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}{{ $colorKey === 'green' ? 'bg-green-100 text-green-800' : '' }}{{ $colorKey === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}{{ $colorKey === 'red' ? 'bg-red-100 text-red-800' : '' }}{{ $colorKey === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}{{ $colorKey === 'pink' ? 'bg-pink-100 text-pink-800' : '' }}{{ $colorKey === 'indigo' ? 'bg-indigo-100 text-indigo-800' : '' }}{{ $colorKey === 'gray' ? 'bg-gray-100 text-gray-800' : '' }} px-3 py-2 rounded-md text-sm font-medium text-center border-2 border-transparent transition-all">
                                    {{ $colorName }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 特色标签 -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">设为特色标签</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">特色标签会在标签页面顶部显示</p>
                </div>

                <!-- 提交按钮 -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('tags.index') }}" 
                       class="text-gray-600 hover:text-gray-800 transition-colors">
                        取消
                    </a>
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                        创建标签
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.color-option {
    transition: all 0.2s;
}

input[type="radio"]:checked + .color-option {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.color-option:hover {
    opacity: 0.8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 实时预览标签名称
    const nameInput = document.getElementById('name');
    const colorInputs = document.querySelectorAll('input[name="color"]');
    
    function updatePreview() {
        const name = nameInput.value || '标签预览';
        const selectedColor = document.querySelector('input[name="color"]:checked')?.value || 'blue';
        
        // 可以在这里添加实时预览功能
        console.log('Preview:', name, selectedColor);
    }
    
    nameInput.addEventListener('input', updatePreview);
    colorInputs.forEach(input => {
        input.addEventListener('change', updatePreview);
    });
    
    // 初始预览
    updatePreview();
});
</script>
@endsection
