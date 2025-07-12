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

test('can load public php gist in runner', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Public PHP Gist',
        'content' => '<?php echo "Hello from public gist!"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('@code-editor', 5)
                ->assertCodeContains($browser, 'Hello from public gist!')
                ->assertSee('返回 Gist')
                ->assertSee($gist->title)
                ->screenshot('php-runner-public-gist');
    });
});

test('can run loaded gist code immediately', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Runnable PHP Gist',
        'content' => '<?php 
$message = "Gist execution successful!";
echo $message;
?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->runCode($browser)
                ->assertExecutionSuccess($browser, 'Gist execution successful!')
                ->screenshot('php-runner-gist-execution');
    });
});

test('private gist requires owner authentication', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $owner->id,
        'title' => 'Private PHP Gist',
        'content' => '<?php echo "Private content"; ?>',
        'language' => 'php',
        'is_public' => false,
    ]);

    // Test unauthorized access
    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->assertSee('403')
                ->screenshot('php-runner-private-gist-unauthorized');
    });

    // Test other user access
    $this->browse(function (Browser $browser) use ($otherUser, $gist) {
        $browser->loginAs($otherUser)
                ->visit("/php-runner/gist/{$gist->id}")
                ->assertSee('403')
                ->screenshot('php-runner-private-gist-other-user');
    });

    // Test owner access
    $this->browse(function (Browser $browser) use ($owner, $gist) {
        $browser->loginAs($owner)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('@code-editor', 5)
                ->assertCodeContains($browser, 'Private content')
                ->screenshot('php-runner-private-gist-owner');
    });
});

test('non-php gist shows error and redirects', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'JavaScript Gist',
        'content' => 'console.log("This is JavaScript");',
        'language' => 'javascript',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->assertPathIs("/gists/{$gist->id}")
                ->assertSee('只支持运行 PHP 代码')
                ->screenshot('php-runner-non-php-gist');
    });
});

test('can save modified gist code as new gist', function () {
    $user = User::factory()->create();
    $originalGist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Gist',
        'content' => '<?php echo "Original code"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($user, $originalGist) {
        $browser->loginAs($user)
                ->visit("/php-runner/gist/{$originalGist->id}")
                ->waitForPhpReady($browser)
                ->typeCode($browser, '<?php echo "Modified code from gist"; ?>')
                ->click('#save-as-new-gist-btn')
                ->waitFor('.save-gist-modal', 5)
                ->type('#gist-title', 'Modified Gist')
                ->type('#gist-description', 'Modified from original gist')
                ->click('#save-gist-confirm')
                ->waitForText('新 Gist 创建成功', 10)
                ->screenshot('php-runner-save-modified-gist');
    });
});

test('can update original gist if owner', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Updatable Gist',
        'content' => '<?php echo "Original content"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->typeCode($browser, '<?php echo "Updated content"; ?>')
                ->click('#update-gist-btn')
                ->waitFor('.update-confirmation', 5)
                ->click('#confirm-update')
                ->waitForText('Gist 更新成功', 10)
                ->screenshot('php-runner-update-gist');
    });
});

test('cannot update gist if not owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $owner->id,
        'title' => 'Others Gist',
        'content' => '<?php echo "Original content"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($otherUser, $gist) {
        $browser->loginAs($otherUser)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->assertMissing('#update-gist-btn')
                ->assertPresent('#save-as-new-gist-btn')
                ->screenshot('php-runner-no-update-permission');
    });
});

test('gist metadata is displayed correctly', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Sample PHP Algorithm',
        'description' => 'A sample PHP algorithm for demonstration',
        'content' => '<?php echo "Algorithm demo"; ?>',
        'language' => 'php',
        'is_public' => true,
        'created_at' => now()->subDays(5),
    ]);

    $this->browse(function (Browser $browser) use ($gist, $user) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('.gist-metadata', 5)
                ->assertSee($gist->title)
                ->assertSee($gist->description)
                ->assertSee($user->name)
                ->assertSee('5 天前')
                ->assertSee('PHP')
                ->screenshot('php-runner-gist-metadata');
    });
});

test('can fork gist from php runner', function () {
    $originalOwner = User::factory()->create(['name' => 'Original Owner']);
    $forker = User::factory()->create(['name' => 'Forker']);
    $gist = Gist::factory()->create([
        'user_id' => $originalOwner->id,
        'title' => 'Forkable Gist',
        'content' => '<?php echo "Original code to fork"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($forker, $gist) {
        $browser->loginAs($forker)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->click('#fork-gist-btn')
                ->waitFor('.fork-confirmation', 5)
                ->click('#confirm-fork')
                ->waitForText('Gist 分叉成功', 10)
                ->assertPathBeginsWith('/php-runner/gist/')
                ->assertSee('分叉自')
                ->screenshot('php-runner-fork-gist');
    });
});

test('gist execution history is tracked', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tracked Gist',
        'content' => '<?php echo "Execution tracking test"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->runCode($browser)
                ->assertExecutionSuccess($browser)
                ->visit("/gists/{$gist->id}")
                ->assertSee('最近运行')
                ->assertSee('刚刚')
                ->screenshot('php-runner-execution-tracked');
    });
});

test('can share gist execution results', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Shareable Results',
        'content' => '<?php echo "Shareable execution result"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/php-runner/gist/{$gist->id}")
                ->waitForPhpReady($browser)
                ->runCode($browser)
                ->assertExecutionSuccess($browser)
                ->click('#share-results-btn')
                ->waitFor('.share-modal', 5)
                ->assertSee('分享执行结果')
                ->assertSee($gist->title)
                ->assertSee('Shareable execution result')
                ->screenshot('php-runner-share-results');
    });
});

test('gist tags are preserved when running in php runner', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Tagged Gist',
        'content' => '<?php echo "Tagged code"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    // Add tags to the gist
    $gist->tags()->create(['name' => 'algorithm']);
    $gist->tags()->create(['name' => 'demo']);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/php-runner/gist/{$gist->id}")
                ->waitFor('.gist-tags', 5)
                ->assertSee('algorithm')
                ->assertSee('demo')
                ->screenshot('php-runner-gist-tags');
    });
});

test('can navigate between related gists in php runner', function () {
    $user = User::factory()->create();
    $gist1 = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'First Gist',
        'content' => '<?php echo "First gist"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);
    $gist2 = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Second Gist',
        'content' => '<?php echo "Second gist"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist1, $gist2) {
        $browser->visit("/php-runner/gist/{$gist1->id}")
                ->waitFor('.related-gists', 5)
                ->assertSee('相关 Gist')
                ->assertSee($gist2->title)
                ->click(".related-gist[data-gist-id='{$gist2->id}']")
                ->waitFor('@code-editor', 5)
                ->assertCodeContains($browser, 'Second gist')
                ->assertSee($gist2->title)
                ->screenshot('php-runner-navigate-related');
    });
});
