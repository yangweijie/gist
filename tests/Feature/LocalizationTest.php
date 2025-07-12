<?php

use App\Models\User;
use App\Services\LocalizationService;
use App\Services\GeoLocationService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

describe('Localization', function () {
    beforeEach(function () {
        // 重置语言设置
        App::setLocale('zh');
        Session::forget('locale');
    });

    describe('Language Detection', function () {
        it('detects language from URL parameter', function () {
            $response = $this->get('/?lang=en');
            
            expect(App::getLocale())->toBe('en');
            expect(Session::get('locale'))->toBe('en');
        });

        it('falls back to default language for unsupported locale', function () {
            // 清理 session 状态
            Session::flush();

            // 确保测试环境中的配置正确
            config(['localization.default_locale' => 'zh']);
            config(['app.locale' => 'zh']);
            config(['localization.supported_locales' => [
                'zh' => ['code' => 'zh', 'name' => 'Chinese'],
                'en' => ['code' => 'en', 'name' => 'English']
            ]]);

            $response = $this->get('/?lang=fr');

            // 检查是否回退到配置的默认语言
            expect(App::getLocale())->toBe('zh');
        });

        it('remembers language in session', function () {
            Session::put('locale', 'en');
            
            $response = $this->get('/');
            
            expect(App::getLocale())->toBe('en');
        });

        it('uses user preference when logged in', function () {
            $user = User::factory()->create(['locale' => 'en']);
            
            $response = $this->actingAs($user)->get('/');
            
            expect(App::getLocale())->toBe('en');
        });

        it('detects browser language from Accept-Language header', function () {
            $response = $this->withHeaders([
                'Accept-Language' => 'en-US,en;q=0.9,zh;q=0.8'
            ])->get('/');
            
            expect(App::getLocale())->toBe('en');
        });

        it('prioritizes URL parameter over session', function () {
            Session::put('locale', 'zh');
            
            $response = $this->get('/?lang=en');
            
            expect(App::getLocale())->toBe('en');
            expect(Session::get('locale'))->toBe('en');
        });
    });

    describe('Language Switching', function () {
        it('switches language via route', function () {
            $response = $this->get('/locale/en');
            
            $response->assertRedirect();
            expect(Session::get('locale'))->toBe('en');
        });

        it('rejects invalid language codes', function () {
            $response = $this->get('/locale/invalid');
            
            $response->assertRedirect();
            $response->assertSessionHas('error');
        });

        it('updates user locale when logged in', function () {
            $user = User::factory()->create(['locale' => 'zh']);
            
            $response = $this->actingAs($user)->get('/locale/en');
            
            $user->refresh();
            expect($user->locale)->toBe('en');
        });

        it('switches language via API', function () {
            $response = $this->withoutMiddleware(VerifyCsrfToken::class)
                ->postJson('/api/locale/switch', [
                    'locale' => 'en'
                ]);

            $response->assertOk();
            $response->assertJson(['success' => true]);
            expect(Session::get('locale'))->toBe('en');
        });

        it('validates API language switch request', function () {
            $response = $this->withoutMiddleware(VerifyCsrfToken::class)
                ->postJson('/api/locale/switch', [
                    'locale' => 'invalid'
                ]);

            $response->assertStatus(422);
        });
    });

    describe('Localization Service', function () {
        it('gets supported locales', function () {
            $service = app(LocalizationService::class);
            $locales = $service->getSupportedLocales();
            
            expect($locales)->toBeArray();
            expect($locales)->toHaveKey('zh');
            expect($locales)->toHaveKey('en');
        });

        it('checks if locale is supported', function () {
            $service = app(LocalizationService::class);
            
            expect($service->isLocaleSupported('zh'))->toBeTrue();
            expect($service->isLocaleSupported('en'))->toBeTrue();
            expect($service->isLocaleSupported('fr'))->toBeFalse();
        });

        it('gets current locale info', function () {
            App::setLocale('zh');
            $service = app(LocalizationService::class);
            $info = $service->getCurrentLocaleInfo();
            
            expect($info)->toBeArray();
            expect($info['code'])->toBe('zh');
            expect($info)->toHaveKey('name');
            expect($info)->toHaveKey('native');
            expect($info)->toHaveKey('flag');
        });

        it('formats dates according to locale', function () {
            $service = app(LocalizationService::class);
            $date = now();
            
            $zhFormat = $service->formatDate($date, 'date', 'zh');
            $enFormat = $service->formatDate($date, 'date', 'en');
            
            expect($zhFormat)->toBeString();
            expect($enFormat)->toBeString();
            expect($zhFormat)->not->toBe($enFormat);
        });

        it('formats numbers according to locale', function () {
            $service = app(LocalizationService::class);
            
            $zhFormat = $service->formatNumber(1234.56, 2, 'zh');
            $enFormat = $service->formatNumber(1234.56, 2, 'en');
            
            expect($zhFormat)->toBeString();
            expect($enFormat)->toBeString();
        });

        it('formats currency according to locale', function () {
            $service = app(LocalizationService::class);
            
            $zhFormat = $service->formatCurrency(100, 'zh');
            $enFormat = $service->formatCurrency(100, 'en');
            
            expect($zhFormat)->toBeString();
            expect($enFormat)->toBeString();
            expect($zhFormat)->toContain('¥');
            expect($enFormat)->toContain('$');
        });
    });

    describe('GeoLocation Service', function () {
        it('detects country from IP', function () {
            $service = app(GeoLocationService::class);
            
            // 测试美国 IP
            $country = $service->getCountryByIp('8.8.8.8');
            expect($country)->toBe('us');
            
            // 测试本地 IP 返回 null
            $localCountry = $service->getCountryByIp('127.0.0.1');
            expect($localCountry)->toBeNull();
        });

        it('maps country to locale', function () {
            $service = app(GeoLocationService::class);
            
            expect($service->getLocaleByCountry('cn'))->toBe('zh');
            expect($service->getLocaleByCountry('us'))->toBe('en');
            expect($service->getLocaleByCountry('unknown'))->toBeNull();
        });

        it('detects locale from IP', function () {
            $service = app(GeoLocationService::class);
            
            $locale = $service->getLocaleByIp('8.8.8.8');
            expect($locale)->toBe('en');
        });

        it('gets real IP address', function () {
            $service = app(GeoLocationService::class);
            $ip = $service->getRealIp();
            
            expect($ip)->toBeString();
            expect(filter_var($ip, FILTER_VALIDATE_IP))->not->toBeFalse();
        });
    });

    describe('Translation Keys', function () {
        it('has all required common translations', function () {
            $requiredKeys = [
                'common.navigation.home',
                'common.navigation.gists',
                'common.navigation.login',
                'common.navigation.logout',
                'common.actions.create',
                'common.actions.edit',
                'common.actions.delete',
                'common.messages.success',
                'common.messages.error',
            ];
            
            foreach (['zh', 'en'] as $locale) {
                App::setLocale($locale);
                
                foreach ($requiredKeys as $key) {
                    $translation = __($key);
                    expect($translation)->not->toBe($key); // 确保有翻译
                    expect($translation)->toBeString();
                }
            }
        });

        it('has all required auth translations', function () {
            $requiredKeys = [
                'auth.titles.login',
                'auth.titles.register',
                'auth.fields.email',
                'auth.fields.password',
                'auth.buttons.login',
                'auth.buttons.register',
                'auth.success.login',
                'auth.errors.failed',
            ];
            
            foreach (['zh', 'en'] as $locale) {
                App::setLocale($locale);
                
                foreach ($requiredKeys as $key) {
                    $translation = __($key);
                    expect($translation)->not->toBe($key);
                    expect($translation)->toBeString();
                }
            }
        });

        it('has all required gist translations', function () {
            $requiredKeys = [
                'gist.titles.index',
                'gist.titles.create',
                'gist.fields.title',
                'gist.fields.content',
                'gist.actions.create',
                'gist.actions.edit',
                'gist.success.created',
                'gist.errors.not_found',
            ];
            
            foreach (['zh', 'en'] as $locale) {
                App::setLocale($locale);
                
                foreach ($requiredKeys as $key) {
                    $translation = __($key);
                    expect($translation)->not->toBe($key);
                    expect($translation)->toBeString();
                }
            }
        });
    });

    describe('Middleware Integration', function () {
        it('applies locale middleware to web routes', function () {
            $response = $this->get('/?lang=en');
            
            expect(App::getLocale())->toBe('en');
        });

        it('preserves locale across requests', function () {
            // 第一个请求设置语言
            $this->get('/?lang=en');
            expect(App::getLocale())->toBe('en');
            
            // 第二个请求应该保持语言
            $this->get('/');
            expect(App::getLocale())->toBe('en');
        });
    });
});
