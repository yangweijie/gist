<?php

use Laravel\Dusk\Browser;

test('debug minimal php runner page content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->screenshot('debug-minimal-php-runner');
        
        // 获取页面标题
        $title = $browser->driver->getTitle();
        echo "Page title: $title\n";
        
        // 获取页面内容
        $content = $browser->driver->getPageSource();
        echo "Page content length: " . strlen($content) . " bytes\n";
        
        // 检查是否包含关键元素
        $keyElements = [
            'PHP 在线运行器',
            'code-editor',
            'run-btn',
            'validate-btn',
            'clear-btn',
            'examples-btn',
            'fullscreen-btn',
            'output'
        ];
        
        foreach ($keyElements as $element) {
            if (strpos($content, $element) !== false) {
                echo "✅ Found element pattern: $element\n";
            } else {
                echo "❌ Missing element pattern: $element\n";
            }
        }
        
        // 检查是否有 JavaScript 错误
        $logs = $browser->driver->manage()->getLog('browser');
        if (!empty($logs)) {
            echo "Browser console logs:\n";
            foreach ($logs as $log) {
                echo "  [{$log['level']}] {$log['message']}\n";
            }
        } else {
            echo "No browser console errors\n";
        }
        
        // 输出页面的前 1000 个字符
        echo "\nPage content preview:\n";
        echo substr($content, 0, 1000) . "\n";
    });
});
