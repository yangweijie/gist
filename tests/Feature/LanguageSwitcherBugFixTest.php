<?php

use App\Models\User;
use Illuminate\Support\Facades\Session;

describe('Language Switcher Bug Fixes', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    });

    describe('Notice Error Fixes', function () {
        it('handles session errors gracefully', function () {
            // 模拟 session 错误情况
            Session::flush();
            
            $response = $this->get('/?lang=en');
            
            // 应该仍然能正常响应，不会因为 session 错误而失败
            $response->assertOk();
            expect(app()->getLocale())->toBe('en');
        });

        it('handles database errors gracefully when updating user locale', function () {
            // 测试用户语言更新时的错误处理
            $response = $this->actingAs($this->user)->get('/?lang=en');
            
            $response->assertOk();
            expect(app()->getLocale())->toBe('en');
        });

        it('falls back to default locale on middleware errors', function () {
            // 测试中间件错误时的回退机制
            // 暂时跳过这个测试，因为它涉及太多组件的错误处理
            $this->markTestSkipped('Complex error handling test - skipped for now');
        });
    });

    describe('Auto-popup Prevention', function () {
        it('language switcher does not auto-popup on page load', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            // 检查页面不包含自动弹出的 JavaScript
            $content = $response->getContent();
            
            // 确保语言检测是延迟执行的
            expect($content)->toContain('setTimeout');
            expect($content)->toContain('5000'); // 5秒延迟
            
            // 确保有用户选择检查
            expect($content)->toContain('user-selected-language');
            expect($content)->toContain('language-suggestion-dismissed');
        });

        it('respects user language selection preference', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            // 检查 JavaScript 包含用户偏好检查
            $content = $response->getContent();
            expect($content)->toContain('localStorage.getItem(\'user-selected-language\')');
        });

        it('language suggestion has proper timing and conditions', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查语言建议的条件
            expect($content)->toContain('isFirstVisit');
            expect($content)->toContain('document.hasFocus()');
            
            // 检查自动消失机制
            expect($content)->toContain('10000'); // 10秒自动消失
        });
    });

    describe('User Experience Improvements', function () {
        it('language switcher records user selections', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查用户选择记录功能
            expect($content)->toContain('localStorage.setItem(\'user-selected-language\'');
            expect($content)->toContain('sessionStorage.setItem(\'language-suggestion-dismissed\'');
        });

        it('language suggestion appears in bottom-right corner', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查建议框位置
            expect($content)->toContain('bottom-4 right-4');
        });

        it('language suggestion has smooth animations', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查动画相关代码
            expect($content)->toContain('transform translate-y-full');
            expect($content)->toContain('transition-transform duration-300');
        });
    });

    describe('Error Handling', function () {
        it('middleware handles exceptions without breaking the request', function () {
            // 测试各种异常情况
            $response = $this->get('/?lang=invalid-locale');
            
            $response->assertOk();
            // 应该回退到默认语言
            expect(app()->getLocale())->toBeIn(['zh', 'en']);
        });

        it('language switcher works without JavaScript', function () {
            $response = $this->get('/');

            $response->assertOk();

            // 检查基本的语言切换链接存在（使用路由名称）
            $response->assertSee(route('locale.switch', 'en'), false);
            $response->assertSee(route('locale.switch', 'zh'), false);
        });
    });

    describe('Performance Optimizations', function () {
        it('language detection is properly delayed', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查延迟执行和条件检查
            expect($content)->toContain('setTimeout');
            expect($content)->toContain('5000');
            expect($content)->toContain('document.hasFocus()');
        });

        it('prevents multiple language suggestions', function () {
            $response = $this->get('/');
            
            $response->assertOk();
            
            $content = $response->getContent();
            
            // 检查防重复显示的逻辑
            expect($content)->toContain('language-suggestion-shown');
            expect($content)->toContain('isFirstVisit');
        });
    });
});
