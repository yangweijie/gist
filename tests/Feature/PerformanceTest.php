<?php

use App\Models\User;
use App\Models\Gist;
use App\Models\Comment;
use App\Models\Tag;
use App\Filament\Widgets\StatsOverview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

describe('Performance Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create([
            'is_active' => true,
            'github_id' => '12345',
        ]);
    });

    describe('Dashboard Performance', function () {
        it('loads dashboard within acceptable time', function () {
            $start = microtime(true);
            
            $response = $this->actingAs($this->admin)->get('/admin');
            
            $end = microtime(true);
            $loadTime = $end - $start;
            
            $response->assertOk();
            // 仪表板应该在 2 秒内加载完成
            expect($loadTime)->toBeLessThan(2.0);
        });

        it('stats widget uses caching effectively', function () {
            // 清除缓存
            Cache::forget('dashboard_stats');
            
            // 记录第一次查询数量
            DB::enableQueryLog();
            $widget = new StatsOverview();
            $reflection = new ReflectionClass($widget);
            $method = $reflection->getMethod('getStats');
            $method->setAccessible(true);
            $stats1 = $method->invoke($widget);
            $firstQueryCount = count(DB::getQueryLog());
            
            // 清除查询日志
            DB::flushQueryLog();
            
            // 第二次调用应该使用缓存，查询数量应该为 0
            $stats2 = $method->invoke($widget);
            $secondQueryCount = count(DB::getQueryLog());
            
            expect($firstQueryCount)->toBeGreaterThan(0);
            expect($secondQueryCount)->toBe(0);
            expect($stats1)->toEqual($stats2);
        });
    });

    describe('API Performance', function () {
        it('gist listing API responds quickly', function () {
            // 创建一些测试数据
            Gist::factory(10)->create(['user_id' => $this->admin->id]);

            $start = microtime(true);

            $response = $this->get('/htmx/gists/search');

            $end = microtime(true);
            $loadTime = $end - $start;

            $response->assertOk();
            // API 应该在 1 秒内响应
            expect($loadTime)->toBeLessThan(1.0);
        });

        it('php runner API responds quickly', function () {
            $start = microtime(true);

            $response = $this->post('/php-runner/validate', [
                'code' => '<?php echo "Hello World";'
            ]);

            $end = microtime(true);
            $loadTime = $end - $start;

            $response->assertOk();
            // PHP 运行器应该在 3 秒内响应
            expect($loadTime)->toBeLessThan(3.0);
        });
    });

    describe('Database Performance', function () {
        it('gist queries are optimized', function () {
            // 创建测试数据
            $user = User::factory()->create();
            $gists = Gist::factory(5)->create(['user_id' => $user->id]);
            
            DB::enableQueryLog();
            
            // 获取用户的 gists（应该使用 eager loading）
            $userWithGists = User::with('gists')->find($user->id);
            
            $queries = DB::getQueryLog();
            
            // 应该只有 2 个查询：1 个获取用户，1 个获取 gists
            expect(count($queries))->toBeLessThanOrEqual(2);
        });
    });

    describe('Memory Usage', function () {
        it('memory usage stays within limits', function () {
            $initialMemory = memory_get_usage();
            
            // 执行一些操作
            $response = $this->actingAs($this->admin)->get('/admin');
            $response->assertOk();
            
            $finalMemory = memory_get_usage();
            $memoryIncrease = $finalMemory - $initialMemory;
            
            // 内存增长应该少于 10MB
            expect($memoryIncrease)->toBeLessThan(10 * 1024 * 1024);
        });
    });
});
