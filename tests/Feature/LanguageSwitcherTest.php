<?php

use App\Models\User;
use Illuminate\Support\Facades\App;

describe('Language Switcher Component', function () {
    it('renders language switcher component', function () {
        $response = $this->get('/gists');

        $response->assertOk();
        $response->assertSee('language-menu-button');
    });

    it('shows current language in switcher', function () {
        App::setLocale('zh');
        
        $response = $this->get('/');
        
        $response->assertSee('🇨🇳'); // 中文国旗
        $response->assertSee('中文');
    });

    it('shows available languages in dropdown', function () {
        $response = $this->get('/');
        
        $response->assertSee('🇨🇳'); // 中文
        $response->assertSee('🇺🇸'); // 英文
        $response->assertSee('中文');
        $response->assertSee('English');
    });

    it('highlights current language in dropdown', function () {
        App::setLocale('en');
        
        $response = $this->get('/');
        
        // 当前语言应该有特殊样式
        $response->assertSee('bg-blue-50');
    });

    it('provides language switch links', function () {
        $response = $this->get('/');
        
        $response->assertSee(route('locale.switch', 'zh'));
        $response->assertSee(route('locale.switch', 'en'));
    });

    it('works for authenticated users', function () {
        $user = User::factory()->create(['locale' => 'en']);
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertOk();
        $response->assertSee('language-menu-button');
    });

    it('works for guest users', function () {
        $response = $this->get('/');
        
        $response->assertOk();
        $response->assertSee('language-menu-button');
    });
});

describe('Language Switcher JavaScript', function () {
    it('includes AJAX switch function', function () {
        $response = $this->get('/');

        $response->assertSee('switchLanguageAjax');
        $response->assertSee('/api/locale/switch');
    });

    it('includes CSRF token for AJAX requests', function () {
        $response = $this->get('/');
        
        $response->assertSee('X-CSRF-TOKEN');
        $response->assertSee('csrf-token');
    });
});

describe('Language Persistence', function () {
    it('remembers language choice in session', function () {
        $this->get('/locale/en');
        
        $response = $this->get('/');
        
        expect(session('locale'))->toBe('en');
    });

    it('saves language to user profile when logged in', function () {
        $user = User::factory()->create(['locale' => 'zh']);
        
        $this->actingAs($user)->get('/locale/en');
        
        $user->refresh();
        expect($user->locale)->toBe('en');
    });

    it('preserves language across page navigation', function () {
        $this->get('/locale/en');
        
        $response = $this->get('/gists');
        
        expect(App::getLocale())->toBe('en');
    });
});
