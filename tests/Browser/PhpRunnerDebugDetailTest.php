<?php

use Laravel\Dusk\Browser;

test('debug full page content for php runner test endpoint', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-test')
                ->screenshot('debug-full-php-runner-test');
        
        // 获取完整页面内容
        $content = $browser->driver->getPageSource();
        echo "Full page content length: " . strlen($content) . " bytes\n";
        
        // 查找 JSON 内容
        if (strpos($content, 'PHP Runner test route working') !== false) {
            echo "✅ Found expected JSON content in page\n";
        } else {
            echo "❌ JSON content not found in page\n";
        }
        
        // 查找特定的 JSON 字段
        $jsonFields = ['code_validation', 'code_execution', 'security_checks', 'gist_integration'];
        foreach ($jsonFields as $field) {
            if (strpos($content, $field) !== false) {
                echo "✅ Found field: $field\n";
            } else {
                echo "❌ Missing field: $field\n";
            }
        }
        
        // 检查是否有错误信息
        if (strpos($content, 'error') !== false || strpos($content, 'Error') !== false) {
            echo "⚠️ Page contains error information\n";
        }
        
        // 输出页面的前 2000 个字符
        echo "\nFirst 2000 characters of page:\n";
        echo substr($content, 0, 2000) . "\n";
    });
});

test('debug simple test endpoint content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->screenshot('debug-simple-test');
        
        $content = $browser->driver->getPageSource();
        
        if (strpos($content, 'Test route working') !== false) {
            echo "✅ Found expected content in simple test endpoint\n";
        } else {
            echo "❌ Expected content not found in simple test endpoint\n";
        }
        
        echo "Simple test page content (first 1000 chars):\n";
        echo substr($content, 0, 1000) . "\n";
    });
});
