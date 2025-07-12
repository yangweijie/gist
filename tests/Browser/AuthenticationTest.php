<?php

use Laravel\Dusk\Browser;
use App\Models\User;

test('user can access login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(1000)
                ->assertPathIs('/login')
                ->assertSee('登录到您的账户')
                ->assertPresent('form')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
    });
});

test('user can access register page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->pause(1000)
                ->assertPathIs('/register')
                ->assertSee('创建新账户')
                ->assertPresent('form')
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('input[name="password_confirmation"]');
    });
});

test('user can register with valid data', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->pause(1000)
                ->type('name', 'Test User')
                ->type('email', 'test@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('注册')
                ->pause(2000)
                ->assertPathIs('/dashboard');
    });
});

test('user cannot register with invalid email', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->pause(1000)
                ->type('name', 'Test User')
                ->type('email', 'invalid-email')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('注册')
                ->pause(1000)
                ->assertPathIs('/register')
                ->assertSee('邮箱格式不正确');
    });
});

test('user cannot register with mismatched passwords', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->pause(1000)
                ->type('name', 'Test User')
                ->type('email', 'test2@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'different_password')
                ->press('注册')
                ->pause(1000)
                ->assertPathIs('/register')
                ->assertSee('密码确认不匹配');
    });
});

test('user can login with valid credentials', function () {
    // Create a test user
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(1000)
                ->type('email', 'login@example.com')
                ->type('password', 'password123')
                ->press('登录')
                ->pause(2000)
                ->assertPathIs('/dashboard')
                ->assertSee('控制台');
    });
});

test('user cannot login with invalid credentials', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(1000)
                ->type('email', 'wrong@example.com')
                ->type('password', 'wrongpassword')
                ->press('登录')
                ->pause(1000)
                ->assertPathIs('/login')
                ->assertSee('登录信息不正确');
    });
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(1000)
                ->assertPathIs('/dashboard')
                ->assertSee('控制台')
                ->assertSee($user->name);
    });
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(1000)
                ->click('button[type="submit"]') // Logout button
                ->pause(1000)
                ->assertPathIs('/')
                ->assertSee('登录');
    });
});

test('unauthenticated user is redirected to login', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
                ->pause(1000)
                ->assertPathIs('/login');
    });
});

test('github oauth button is present on login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(1000)
                ->assertSee('GitHub 登录')
                ->assertPresent('a[href*="auth/github"]');
    });
});

test('user can navigate between login and register pages', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(1000)
                ->clickLink('注册')
                ->pause(1000)
                ->assertPathIs('/register')
                ->clickLink('登录')
                ->pause(1000)
                ->assertPathIs('/login');
    });
});
