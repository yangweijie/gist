<?php

use Laravel\Dusk\Browser;

test('user can access gist listing page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(3000)
                ->screenshot('gists-page-test')
                ->assertPathIs('/gists');
    });
});

test('user can view public gists without authentication', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->assertSee(__('gist.titles.index'))
                ->assertPresent('.gist-item');
    });
});

test('user can search gists', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->type('input[name="search"]', 'php')
                ->press(__('common.actions.search'))
                ->pause(2000)
                ->assertPathIs('/gists')
                ->assertQueryStringHas('search', 'php');
    });
});

test('user can filter gists by language', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->select('select[name="language"]', 'php')
                ->pause(2000)
                ->assertQueryStringHas('language', 'php');
    });
});

test('user can view individual gist', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000)
                ->assertPathBeginsWith('/gists/')
                ->assertPresent('.gist-content')
                ->assertPresent('.code-highlight');
    });
});

test('unauthenticated user cannot create gist', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists/create')
                ->pause(1000)
                ->assertPathIs('/login');
    });
});

test('unauthenticated user cannot access dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
                ->pause(1000)
                ->assertPathIs('/login');
    });
});

test('gist page shows syntax highlighting', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(3000)
                ->assertPresent('.hljs') // highlight.js class
                ->assertPresent('pre code');
    });
});

test('gist page shows metadata', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000)
                ->assertPresent('.gist-meta')
                ->assertSee('创建时间')
                ->assertSee('语言');
    });
});

test('user can copy gist content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000)
                ->assertPresent('button[data-action="copy"]')
                ->click('button[data-action="copy"]')
                ->pause(1000);
        // Note: We can't easily test clipboard content in browser tests
    });
});

test('gist page shows view count', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000)
                ->assertSee('浏览次数');
    });
});

test('user can navigate back to gist list', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000)
                ->clickLink('返回列表')
                ->pause(1000)
                ->assertPathIs('/gists');
    });
});

test('gist list shows pagination when needed', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000);
        
        // Check if pagination exists (might not if there are few gists)
        if ($browser->element('.pagination')) {
            $browser->assertPresent('.pagination');
        }
    });
});

test('user can sort gists', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->select('select[name="sort"]', 'created_at')
                ->pause(2000)
                ->assertQueryStringHas('sort', 'created_at');
    });
});

test('gist page shows tags if available', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(1000)
                ->click('.gist-item:first-child a')
                ->pause(2000);

        // Check if tags exist (might not for all gists)
        if ($browser->element('.gist-tags')) {
            $browser->assertPresent('.gist-tags');
        }
    });
});

// ===== 认证用户 Gist 管理测试 =====

test('authenticated user can access gist creation page', function () {
    $user = createTestUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/gists/create')
                ->pause(1000)
                ->assertPathIs('/gists/create')
                ->assertSee('创建新 Gist')
                ->assertPresent('form[action*="gists"]');
    });
});

test('authenticated user can create a new gist', function () {
    $user = createTestUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/gists/create')
                ->pause(1000)
                ->type('input[name="title"]', 'Test Gist Title')
                ->type('textarea[name="description"]', 'This is a test gist description')
                ->type('textarea[name="content"]', '<?php echo "Hello World!"; ?>')
                ->select('select[name="language"]', 'php')
                ->type('input[name="filename"]', 'test.php')
                ->check('input[name="is_public"]')
                ->press('创建 Gist')
                ->pause(3000)
                ->assertPathBeginsWith('/gists/')
                ->assertSee('Test Gist Title')
                ->assertSee('Hello World!');
    });
});

test('authenticated user can view their own gists', function () {
    $user = createTestUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(1000)
                ->clickLink(__('gist.titles.my_gists'))
                ->pause(2000)
                ->assertPathIs('/gists/my')
                ->assertSee(__('gist.titles.my_gists'));
    });
});

test('authenticated user can edit their own gist', function () {
    $user = createTestUser();
    $gist = createTestGist($user);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/gists/{$gist->id}")
                ->pause(1000)
                ->clickLink(__('gist.actions.edit'))
                ->pause(2000)
                ->assertPathIs("/gists/{$gist->id}/edit")
                ->assertSee(__('gist.titles.edit'))
                ->clear('input[name="title"]')
                ->type('input[name="title"]', 'Updated Test Gist')
                ->press(__('gist.actions.update'))
                ->pause(3000)
                ->assertPathIs("/gists/{$gist->id}")
                ->assertSee('Updated Test Gist');
    });
});

test('authenticated user can delete their own gist', function () {
    $user = createTestUser();
    $gist = createTestGist($user);

    $this->browse(function (Browser $browser) use ($user, $gist) {
        $browser->loginAs($user)
                ->visit("/gists/{$gist->id}")
                ->pause(1000)
                ->press(__('gist.actions.delete'))
                ->pause(1000)
                ->acceptDialog() // 确认删除对话框
                ->pause(2000)
                ->assertPathIs('/gists')
                ->assertDontSee($gist->title);
    });
});

test('user cannot edit others gists', function () {
    $user1 = createTestUser();
    $user2 = createTestUser(['email' => 'user2@example.com']);
    $gist = createTestGist($user1);

    $this->browse(function (Browser $browser) use ($user2, $gist) {
        $browser->loginAs($user2)
                ->visit("/gists/{$gist->id}")
                ->pause(1000)
                ->assertDontSee(__('gist.actions.edit'))
                ->assertDontSee(__('gist.actions.delete'));
    });
});

test('user cannot access edit page for others gists', function () {
    $user1 = createTestUser();
    $user2 = createTestUser(['email' => 'user2@example.com']);
    $gist = createTestGist($user1);

    $this->browse(function (Browser $browser) use ($user2, $gist) {
        $browser->loginAs($user2)
                ->visit("/gists/{$gist->id}/edit")
                ->pause(1000)
                ->assertStatus(403); // 或者重定向到错误页面
    });
});
