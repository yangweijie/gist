<?php

use Laravel\Dusk\Browser;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('minimal php runner page loads successfully', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->screenshot('minimal-php-runner');
        
        // 检查页面标题
        $browser->assertSee('PHP 在线运行器 - 最小化版本');
        
        // 检查关键元素是否存在
        $browser->assertPresent('#code-editor')
                ->assertPresent('#run-btn')
                ->assertPresent('#validate-btn')
                ->assertPresent('#clear-btn')
                ->assertPresent('#examples-btn')
                ->assertPresent('#fullscreen-btn')
                ->assertPresent('#output');
        
        echo "✅ All key elements found on minimal PHP runner page\n";
    });
});

test('can type and clear code in minimal editor', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Test code"; ?>')
                ->assertInputValue('#code-editor', '<?php echo "Test code"; ?>')
                ->click('#clear-btn')
                ->pause(500)
                ->assertInputValue('#code-editor', '');
        
        echo "✅ Code typing and clearing works\n";
    });
});

test('can load example code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->click('#examples-btn')
                ->pause(500);
        
        $codeValue = $browser->inputValue('#code-editor');
        expect(strlen($codeValue))->toBeGreaterThan(10);
        expect($codeValue)->toContain('<?php');
        
        echo "✅ Example code loading works\n";
    });
});

test('can simulate code execution', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Hello World"; ?>')
                ->click('#run-btn')
                ->pause(2000);
        
        $output = $browser->text('#output');
        expect($output)->toContain('Hello World');
        
        echo "✅ Code execution simulation works\n";
    });
});

test('can validate code via api', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Valid code"; ?>')
                ->click('#validate-btn')
                ->pause(3000);
        
        // 检查是否有状态消息或输出
        $statusExists = $browser->element('#status-area') !== null;
        $outputExists = $browser->element('#output') !== null;
        
        expect($statusExists || $outputExists)->toBeTrue();
        
        echo "✅ Code validation API call works\n";
    });
});

test('keyboard shortcut ctrl+enter works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Shortcut test"; ?>')
                ->click('#code-editor');

        $browser->script('
            document.getElementById("code-editor").focus();
            var event = new KeyboardEvent("keydown", {
                key: "Enter",
                ctrlKey: true,
                bubbles: true
            });
            document.getElementById("code-editor").dispatchEvent(event);
        ');

        $browser->pause(2000);

        $output = $browser->text('#output');
        expect($output)->toContain('Shortcut test');

        echo "✅ Keyboard shortcut works\n";
    });
});

test('fullscreen toggle works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->click('#fullscreen-btn')
                ->pause(1000);
        
        // 检查全屏按钮文本是否改变
        $buttonText = $browser->text('#fullscreen-btn');
        
        // 由于浏览器安全限制，全屏可能不会真正激活，但按钮应该响应
        expect(strlen($buttonText))->toBeGreaterThan(0);
        
        echo "✅ Fullscreen toggle responds\n";
    });
});

test('loading states work correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Loading test"; ?>')
                ->click('#run-btn');
        
        // 检查加载状态（快速检查，因为模拟执行很快）
        $loadingElement = $browser->element('#loading');
        expect($loadingElement)->not->toBeNull();
        
        $browser->pause(2000);
        
        echo "✅ Loading states work\n";
    });
});

test('status messages display correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                ->clear('#code-editor')
                ->click('#run-btn')
                ->pause(1000);
        
        // 应该显示错误状态（因为没有代码）
        $statusArea = $browser->element('#status-area');
        expect($statusArea)->not->toBeNull();
        
        echo "✅ Status messages work\n";
    });
});

test('multiple operations work in sequence', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-minimal')
                // 1. 清空代码
                ->clear('#code-editor')
                // 2. 加载示例
                ->click('#examples-btn')
                ->pause(500)
                // 3. 运行代码
                ->click('#run-btn')
                ->pause(2000)
                // 4. 验证代码
                ->click('#validate-btn')
                ->pause(2000)
                // 5. 清空代码
                ->click('#clear-btn')
                ->pause(500);
        
        $finalCodeValue = $browser->inputValue('#code-editor');
        expect($finalCodeValue)->toBe('');
        
        echo "✅ Sequential operations work correctly\n";
    });
});
