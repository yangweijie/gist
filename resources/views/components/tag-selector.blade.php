<div class="tag-selector" data-name="{{ $name }}" data-multiple="{{ $multiple ? 'true' : 'false' }}" data-max-tags="{{ $maxTags }}">
    <!-- 隐藏的输入字段 -->
    @if($multiple)
        @foreach($selectedTags as $tag)
            <input type="hidden" name="{{ $name }}[]" value="{{ is_array($tag) ? $tag['id'] : $tag }}" class="tag-input">
        @endforeach
    @else
        <input type="hidden" name="{{ $name }}" value="{{ !empty($selectedTags) ? (is_array($selectedTags[0]) ? $selectedTags[0]['id'] : $selectedTags[0]) : '' }}" class="tag-input">
    @endif

    <!-- 标签输入框 -->
    <div class="tag-input-container">
        <div class="selected-tags">
            @foreach($selectedTags as $tag)
                <span class="selected-tag" data-tag-id="{{ is_array($tag) ? $tag['id'] : $tag }}">
                    <span class="tag-name">{{ is_array($tag) ? $tag['name'] : $tag }}</span>
                    <button type="button" class="remove-tag" title="移除标签">×</button>
                </span>
            @endforeach
        </div>

        <input type="text"
               class="tag-search-input"
               placeholder="{{ count($selectedTags) === 0 ? $placeholder : '' }}"
               autocomplete="off">
    </div>

    <!-- 标签建议下拉框 -->
    <div class="tag-suggestions" style="display: none;">
        <div class="suggestions-header">
            <span class="suggestions-title">选择标签</span>
            @if($allowCreate)
                <button type="button" class="create-tag-btn" style="display: none;">
                    <span class="create-icon">+</span>
                    <span class="create-text">创建新标签</span>
                </button>
            @endif
        </div>

        <div class="suggestions-list">
            <!-- 动态加载的建议列表 -->
        </div>

        @if($popularTags->count() > 0)
            <div class="popular-tags">
                <div class="popular-tags-title">热门标签</div>
                <div class="popular-tags-list">
                    @foreach($popularTags as $tag)
                        <button type="button"
                                class="popular-tag"
                                data-tag-id="{{ $tag->id }}"
                                data-tag-name="{{ $tag->name }}"
                                data-tag-color="{{ $tag->color }}">
                            <span class="tag-badge {{ $tag->color_class }}">
                                {{ $tag->name }}
                            </span>
                            <span class="tag-count">({{ $tag->usage_count }})</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.tag-selector {
    position: relative;
    width: 100%;
}

.tag-input-container {
    min-height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 6px;
    background: white;
    cursor: text;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
}

.tag-input-container:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    flex: 1;
}

.selected-tag {
    display: inline-flex;
    align-items: center;
    background: #e5e7eb;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 14px;
    color: #374151;
}

.selected-tag .tag-name {
    margin-right: 4px;
}

.remove-tag {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-tag:hover {
    color: #ef4444;
}

.tag-search-input {
    border: none;
    outline: none;
    flex: 1;
    min-width: 120px;
    padding: 4px;
    font-size: 14px;
}

.tag-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.suggestions-header {
    padding: 8px 12px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
}

.suggestions-title {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
}

.create-tag-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
}

.create-tag-btn:hover {
    background: #2563eb;
}

.suggestions-list {
    max-height: 150px;
    overflow-y: auto;
}

.suggestion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.suggestion-item:hover {
    background: #f3f4f6;
}

.suggestion-tag {
    display: flex;
    align-items: center;
    gap: 8px;
}

.suggestion-count {
    font-size: 12px;
    color: #6b7280;
}

.popular-tags {
    border-top: 1px solid #e5e7eb;
    padding: 8px 12px;
}

.popular-tags-title {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.popular-tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.popular-tag {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px;
    border-radius: 4px;
}

.popular-tag:hover {
    background: #f3f4f6;
}

.tag-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.tag-count {
    font-size: 11px;
    color: #6b7280;
}

/* 响应式设计 */
@media (max-width: 640px) {
    .tag-suggestions {
        max-height: 250px;
    }

    .popular-tags-list {
        max-height: 100px;
        overflow-y: auto;
    }
}
</style>