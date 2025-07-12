<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Gist;
use Tests\Browser\Pages\PhpRunnerPage;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('php runner page loads correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 10)
                ->assertSee('PHP 在线运行器')
                ->assertSee('在浏览器中直接运行 PHP 代码')
                ->assertPresent('#code-editor')
                ->assertPresent('.output-panel')
                ->assertPresent('#run-btn')
                ->assertPresent('#clear-btn')
                ->screenshot('php-runner-page');
    });
});

test('php runner status indicator works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#php-status', 10)
                ->waitUntilMissing('#loading-spinner', 15)
                ->assertSeeIn('#php-status-text', '准备就绪')
                ->assertPresent('.status-ready')
                ->screenshot('php-runner-ready-status');
    });
});

test('default hello world code is present', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->assertInputValue('#code-editor', function ($value) {
                    return str_contains($value, 'Hello, World!') && 
                           str_contains($value, '<?php');
                })
                ->screenshot('php-runner-default-code');
    });
});

test('can run simple php code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, '<?php echo "Test Output"; ?>', 'Test Output')
                ->screenshot('php-runner-simple-execution');
    });
});

test('can run php code with variables and loops', function () {
    $this->browse(function (Browser $browser) {
        $code = '<?php
$numbers = [1, 2, 3, 4, 5];
foreach ($numbers as $num) {
    echo "Number: " . $num . "\n";
}
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, $code)
                ->runCode($browser)
                ->assertExecutionSuccess($browser)
                ->assertOutputContains($browser, 'Number: 1')
                ->assertOutputContains($browser, 'Number: 5')
                ->screenshot('php-runner-loop-execution');
    });
});

test('displays php syntax errors correctly', function () {
    $this->browse(function (Browser $browser) {
        $invalidCode = '<?php echo "Missing semicolon" ?>';

        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->waitUntilMissing('.status-loading', 15)
                ->clear('#code-editor')
                ->type('#code-editor', $invalidCode)
                ->click('#run-btn')
                ->waitFor('.output-content', 10)
                ->assertSeeIn('#output-panel', 'Error')
                ->screenshot('php-runner-syntax-error');
    });
});

test('can clear code editor', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->type('#code-editor', 'Some test code')
                ->click('#clear-btn')
                ->assertInputValue('#code-editor', '');
    });
});

test('can clear output panel', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->waitUntilMissing('.status-loading', 15)
                ->type('#code-editor', '<?php echo "Test"; ?>')
                ->click('#run-btn')
                ->waitFor('.output-content', 10)
                ->click('#clear-output-btn')
                ->assertDontSeeIn('#output-panel', 'Test');
    });
});

test('examples dropdown works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#examples-btn', 5)
                ->click('#examples-btn')
                ->waitFor('.examples-dropdown', 2)
                ->assertSee('Hello World')
                ->assertSee('数组操作')
                ->assertSee('函数定义')
                ->screenshot('php-runner-examples-dropdown');
    });
});

test('can load example code', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#examples-btn', 5)
                ->click('#examples-btn')
                ->waitFor('.examples-dropdown', 2)
                ->click('.example-card:first-child')
                ->waitFor('#code-editor', 2)
                ->assertInputValue('#code-editor', function ($value) {
                    return str_contains($value, 'Hello, World!');
                })
                ->screenshot('php-runner-example-loaded');
    });
});

test('execution time is displayed', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->waitUntilMissing('.status-loading', 15)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Quick test"; ?>')
                ->click('#run-btn')
                ->waitFor('.output-content', 10)
                ->assertSeeIn('#output-panel', 'ms')
                ->screenshot('php-runner-execution-time');
    });
});

test('can run gist code in php runner', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test PHP Gist',
        'content' => '<?php echo "Gist content test"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('#code-editor', 5)
                ->assertInputValue('#code-editor', function ($value) {
                    return str_contains($value, 'Gist content test');
                })
                ->assertSee('返回 Gist')
                ->screenshot('php-runner-gist-loaded');
    });
});

test('non-php gist redirects with error', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'JavaScript Gist',
        'content' => 'console.log("Hello");',
        'language' => 'javascript',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->assertPathIs("/gists/{$gist->id}")
                ->assertSee('只支持运行 PHP 代码');
    });
});

test('private gist requires authentication', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Private PHP Gist',
        'content' => '<?php echo "Private content"; ?>',
        'language' => 'php',
        'is_public' => false,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->assertSee('403');
    });
});

test('authenticated user can access own private gist', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Private PHP Gist',
        'content' => '<?php echo "Private content"; ?>',
        'language' => 'php',
        'is_public' => false,
    ]);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('#code-editor', 5)
                ->assertInputValue('#code-editor', function ($value) {
                    return str_contains($value, 'Private content');
                })
                ->screenshot('php-runner-private-gist');
    });
});

test('fullscreen mode works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#fullscreen-btn', 5)
                ->click('#fullscreen-btn')
                ->pause(1000)
                ->assertPresent('.fullscreen-mode')
                ->click('#fullscreen-btn')
                ->pause(1000)
                ->assertMissing('.fullscreen-mode')
                ->screenshot('php-runner-fullscreen-toggle');
    });
});

test('keyboard shortcuts work', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->waitUntilMissing('.status-loading', 15)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Shortcut test"; ?>')
                ->keys('#code-editor', ['{ctrl}', '{enter}'])
                ->waitFor('.output-content', 10)
                ->assertSeeIn('#output-panel', 'Shortcut test')
                ->screenshot('php-runner-keyboard-shortcut');
    });
});

test('code validation works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner')
                ->waitFor('#validate-btn', 5)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Valid code"; ?>')
                ->click('#validate-btn')
                ->waitFor('.validation-result', 5)
                ->assertSee('代码语法正确')
                ->screenshot('php-runner-validation-success');
    });
});

test('security check prevents dangerous code', function () {
    $this->browse(function (Browser $browser) {
        $dangerousCode = '<?php file_get_contents("/etc/passwd"); ?>';
        
        $browser->visit('/php-runner')
                ->waitFor('#validate-btn', 5)
                ->clear('#code-editor')
                ->type('#code-editor', $dangerousCode)
                ->click('#validate-btn')
                ->waitFor('.validation-result', 5)
                ->assertSee('不允许使用函数')
                ->screenshot('php-runner-security-check');
    });
});

test('responsive design works on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(375, 667) // iPhone 6/7/8 size
                ->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->assertPresent('.grid-cols-1')
                ->assertMissing('.lg\\:grid-cols-2')
                ->screenshot('php-runner-mobile-view');
    });
});

test('php runner logs execution for authenticated users', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/php-runner')
                ->waitFor('#code-editor', 5)
                ->waitUntilMissing('.status-loading', 15)
                ->clear('#code-editor')
                ->type('#code-editor', '<?php echo "Logged execution"; ?>')
                ->click('#run-btn')
                ->waitFor('.output-content', 10)
                ->assertSeeIn('#output-panel', 'Logged execution');
        
        // 验证日志记录（通过检查网络请求）
        $logs = $browser->driver->manage()->getLog('browser');
        $hasLogRequest = false;
        foreach ($logs as $log) {
            if (str_contains($log['message'], '/php-runner/log')) {
                $hasLogRequest = true;
                break;
            }
        }
        
        expect($hasLogRequest)->toBeTrue();
    });
});
