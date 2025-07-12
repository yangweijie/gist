@php
    $localizationService = app(App\Services\LocalizationService::class);
    $currentLocale = app()->getLocale();
    $currentLocaleInfo = $localizationService->getCurrentLocaleInfo();
    $languages = $localizationService->getEnabledLocales();
@endphp

<!-- 移动端语言切换器 -->
<div class="block sm:hidden" 
     x-data="{ 
         open: false, 
         switching: false,
         currentLocale: '{{ $currentLocale }}'
     }"
     @click.away="open = false">
     
    <!-- 触发按钮 -->
    <button @click="open = !open" 
            type="button" 
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors duration-200"
            :class="{ 'bg-gray-100 text-gray-500': open }"
            aria-label="{{ __('common.language.select_language') }}">
        
        <div x-show="!switching" class="flex items-center">
            <span class="text-lg">{{ $currentLocaleInfo['flag'] }}</span>
            <span class="ml-1 text-xs font-medium">{{ strtoupper($currentLocaleInfo['code']) }}</span>
        </div>
        
        <div x-show="switching" class="flex items-center">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </button>

    <!-- 全屏覆盖层 -->
    <div x-show="open" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black bg-opacity-25"
         @click="open = false"></div>

    <!-- 底部弹出菜单 -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-y-0"
         x-transition:leave-end="transform translate-y-full"
         class="fixed inset-x-0 bottom-0 z-50 bg-white dark:bg-gray-800 rounded-t-xl shadow-xl">
         
        <!-- 拖拽指示器 -->
        <div class="flex justify-center pt-3 pb-2">
            <div class="w-8 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>
        
        <!-- 标题 -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('common.language.select_language') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('common.language.auto_detected') }}
            </p>
        </div>
        
        <!-- 语言选项 -->
        <div class="px-4 py-2 max-h-64 overflow-y-auto">
            @foreach($languages as $locale => $language)
                <a href="{{ route('locale.switch', $locale) }}"
                   onclick="this.style.pointerEvents='none'; this.style.opacity='0.5';"
                   class="flex items-center py-4 border-b border-gray-100 dark:border-gray-700 last:border-b-0 {{ $currentLocale === $locale ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                   
                    <!-- 国旗和语言信息 -->
                    <div class="flex items-center flex-1">
                        <span class="text-2xl mr-4">{{ $language['flag'] }}</span>
                        <div class="flex flex-col">
                            <span class="text-base font-medium text-gray-900 dark:text-white">{{ $language['native'] }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $language['name'] }}</span>
                        </div>
                    </div>
                    
                    <!-- 当前语言标识 -->
                    @if($currentLocale === $locale)
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ __('common.language.current') }}</span>
                        </div>
                    @else
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
        
        <!-- 底部安全区域 -->
        <div class="h-safe-area-inset-bottom bg-white dark:bg-gray-800"></div>
    </div>
</div>

@push('styles')
<style>
/* 安全区域支持 */
.h-safe-area-inset-bottom {
    height: env(safe-area-inset-bottom);
}

/* 触摸优化 */
@media (hover: none) and (pointer: coarse) {
    .mobile-language-option {
        min-height: 44px; /* iOS 推荐的最小触摸目标 */
    }
}
</style>
@endpush
