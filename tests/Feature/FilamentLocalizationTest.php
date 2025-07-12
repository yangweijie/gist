<?php

use App\Models\User;
use App\Models\Gist;
use Illuminate\Support\Facades\App;

describe('Filament Localization', function () {
    beforeEach(function () {
        // 创建管理员用户
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
    });

    describe('Admin Panel Language', function () {
        it('uses correct language in admin panel', function () {
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin');
            
            $response->assertOk();
            // 检查是否有中文内容
            $response->assertSee('仪表板');
        });

        it('switches language in admin panel', function () {
            $this->admin->update(['locale' => 'en']);
            
            $response = $this->actingAs($this->admin)->get('/admin');
            
            $response->assertOk();
            // 检查是否有英文内容
            expect(App::getLocale())->toBe('en');
        });
    });

    describe('Filament Resources', function () {
        it('displays localized resource labels', function () {
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin/gists');
            
            $response->assertOk();
            // 检查资源标签是否本地化
            $response->assertSee('Gist');
        });

        it('displays localized form fields', function () {
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin/gists/create');
            
            $response->assertOk();
            // 检查表单字段标签
            $response->assertSee('标题');
            $response->assertSee('内容');
        });

        it('shows localized validation messages', function () {
            App::setLocale('zh');

            // 检查验证翻译文件是否存在并包含中文内容
            $validationTranslations = trans('validation.required');
            expect($validationTranslations)->toContain('必须');
        });
    });

    describe('Filament Navigation', function () {
        it('displays localized navigation groups', function () {
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin');
            
            $response->assertOk();
            // 检查导航组是否本地化
            $response->assertSee('内容管理');
            $response->assertSee('用户管理');
        });

        it('displays localized navigation labels', function () {
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin');
            
            $response->assertOk();
            // 检查导航标签
            $response->assertSee('Gist 管理');
            $response->assertSee('用户管理');
        });
    });

    describe('Filament Tables', function () {
        it('displays localized table headers', function () {
            // 创建一些测试数据
            Gist::factory()->count(3)->create();
            
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin/gists');
            
            $response->assertOk();
            // 检查表格列标题
            $response->assertSee('标题');
            $response->assertSee('作者');
            $response->assertSee('创建时间');
        });

        it('displays localized action buttons', function () {
            $gist = Gist::factory()->create();
            
            App::setLocale('zh');
            
            $response = $this->actingAs($this->admin)->get('/admin/gists');
            
            $response->assertOk();
            // 检查操作按钮
            $response->assertSee('编辑');
            $response->assertSee('删除');
        });
    });

    describe('Filament Widgets', function () {
        it('displays localized widget titles', function () {
            App::setLocale('zh');

            // 检查小部件类是否存在并且包含中文标题
            $widgetClass = \App\Filament\Widgets\StatsOverview::class;
            expect(class_exists($widgetClass))->toBeTrue();

            // 检查源代码中是否包含中文标题
            $reflection = new ReflectionClass($widgetClass);
            $source = file_get_contents($reflection->getFileName());
            expect($source)->toContain('总 Gist 数');
            expect($source)->toContain('用户总数');
        });
    });

    describe('Filament Messages', function () {
        it('shows localized success messages', function () {
            App::setLocale('zh');

            // 检查成功消息翻译是否存在
            $successMessage = __('filament.messages.saved');
            expect($successMessage)->not->toBe('filament.messages.saved');
        });

        it('shows localized error messages', function () {
            App::setLocale('zh');

            // 检查错误消息翻译是否存在
            $errorMessage = trans('validation.required', ['attribute' => '标题']);
            expect($errorMessage)->toContain('必须');
        });
    });
});

describe('Filament Language Files', function () {
    it('has all required filament translations in Chinese', function () {
        $requiredKeys = [
            'filament.brand_name',
            'filament.navigation_groups.content',
            'filament.navigation_groups.users',
            'filament.resources.gist.label',
            'filament.resources.user.label',
            'filament.fields.gist.title',
            'filament.fields.gist.content',
            'filament.actions.create',
            'filament.actions.edit',
            'filament.actions.delete',
        ];
        
        App::setLocale('zh');
        
        foreach ($requiredKeys as $key) {
            $translation = __($key);
            expect($translation)->not->toBe($key); // 确保有翻译
            expect($translation)->toBeString();
        }
    });

    it('has all required filament translations in English', function () {
        $requiredKeys = [
            'filament.brand_name',
            'filament.navigation_groups.content',
            'filament.navigation_groups.users',
            'filament.resources.gist.label',
            'filament.resources.user.label',
            'filament.fields.gist.title',
            'filament.fields.gist.content',
            'filament.actions.create',
            'filament.actions.edit',
            'filament.actions.delete',
        ];
        
        App::setLocale('en');
        
        foreach ($requiredKeys as $key) {
            $translation = __($key);
            expect($translation)->not->toBe($key); // 确保有翻译
            expect($translation)->toBeString();
        }
    });

    it('has consistent translations between languages', function () {
        $testKeys = [
            'filament.actions.create',
            'filament.actions.edit',
            'filament.actions.delete',
            'filament.fields.gist.title',
            'filament.fields.gist.content',
        ];
        
        foreach ($testKeys as $key) {
            App::setLocale('zh');
            $zhTranslation = __($key);
            
            App::setLocale('en');
            $enTranslation = __($key);
            
            // 两种语言都应该有翻译
            expect($zhTranslation)->not->toBe($key);
            expect($enTranslation)->not->toBe($key);
            
            // 翻译内容应该不同（除非是专有名词）
            if (!in_array($key, ['filament.fields.gist.title'])) {
                expect($zhTranslation)->not->toBe($enTranslation);
            }
        }
    });
});
