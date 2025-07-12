<?php

use Laravel\Dusk\Browser;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('php runner page loads and basic elements are present', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->assertSee('PHP 在线运行器')
                ->assertPresent('#code-editor')
                ->assertPresent('.output-panel')
                ->assertPresent('#run-btn')
                ->assertPresent('#clear-btn')
                ->assertPresent('#examples-btn')
                ->screenshot('php-runner-basic-load');
    });
});

test('can type code in editor', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Hello Test"; ?>')
                ->assertInputValue('#code-editor', '<?php echo "Hello Test"; ?>')
                ->screenshot('php-runner-type-code');
    });
});

test('can clear code editor', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->type('#code-editor', 'Some test code')
                ->click('#clear-btn')
                ->assertInputValue('#code-editor', '')
                ->screenshot('php-runner-clear-code');
    });
});

test('examples modal opens', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#examples-btn', 10)
                ->click('#examples-btn')
                ->waitFor('#examples-modal', 5)
                ->assertSee('示例代码')
                ->screenshot('php-runner-examples-modal');
    });
});

test('can run simple php code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Test Output"; ?>')
                ->click('#run-btn')
                ->waitFor('#output-content', 15)
                ->assertSeeIn('.output-panel', 'Test Output')
                ->screenshot('php-runner-simple-run');
    });
});

test('execution time is displayed after running code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Time test"; ?>')
                ->click('#run-btn')
                ->waitFor('#output-content', 15)
                ->assertPresent('#execution-time')
                ->screenshot('php-runner-execution-time');
    });
});

test('can handle php syntax errors', function () {
    $this->browse(function (Browser $browser) {
        $invalidCode = '<?php echo "Missing semicolon" ?>';

        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->clear('#code-editor')
                ->type('#code-editor', $invalidCode)
                ->click('#run-btn')
                ->waitFor('#output-content', 15)
                ->assertSeeIn('.output-panel', 'Error')
                ->screenshot('php-runner-syntax-error');
    });
});

test('fullscreen toggle works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#fullscreen-btn', 10)
                ->click('#fullscreen-btn')
                ->pause(1000)
                ->assertPresent('.fullscreen-mode')
                ->click('#fullscreen-btn')
                ->pause(1000)
                ->assertMissing('.fullscreen-mode')
                ->screenshot('php-runner-fullscreen');
    });
});

test('responsive design on mobile viewport', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(375, 667) // iPhone size
                ->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->assertPresent('.grid-cols-1')
                ->screenshot('php-runner-mobile');
    });
});

test('keyboard shortcut ctrl+enter runs code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Shortcut test"; ?>')
                ->keys('#code-editor', ['{ctrl}', '{enter}'])
                ->waitFor('#output-content', 15)
                ->assertSeeIn('.output-panel', 'Shortcut test')
                ->screenshot('php-runner-keyboard-shortcut');
    });
});
