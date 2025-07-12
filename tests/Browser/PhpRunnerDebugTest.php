<?php

use Laravel\Dusk\Browser;

test('debug what browser sees on test endpoint', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-test')
                ->screenshot('debug-php-runner-test');
        
        // 获取页面内容
        $content = $browser->driver->getPageSource();
        echo "Page content:\n";
        echo substr($content, 0, 1000) . "\n";
        
        // 获取页面标题
        $title = $browser->driver->getTitle();
        echo "Page title: $title\n";
        
        // 获取当前 URL
        $url = $browser->driver->getCurrentURL();
        echo "Current URL: $url\n";
    });
});

test('debug what browser sees on simple test endpoint', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->screenshot('debug-test');
        
        // 获取页面内容
        $content = $browser->driver->getPageSource();
        echo "Simple test page content:\n";
        echo substr($content, 0, 500) . "\n";
    });
});
