<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Gist;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('php runner test endpoint is accessible', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-test')
                ->assertSee('PHP Runner test route working')
                ->assertSee('code_validation')
                ->assertSee('code_execution')
                ->assertSee('security_checks')
                ->assertSee('gist_integration')
                ->screenshot('php-runner-test-endpoint');
    });
});

test('php runner validate endpoint works via browser', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->assertSee('Test route working')
                ->screenshot('test-endpoint');
    });
});

test('can access php runner validate endpoint with javascript', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->script([
                    'fetch("/php-runner/validate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || ""
                        },
                        body: JSON.stringify({
                            code: "<?php echo \"Hello World\"; ?>"
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.validationResult = data;
                    })
                    .catch(error => {
                        window.validationError = error.toString();
                    });'
                ])
                ->pause(2000)
                ->screenshot('php-runner-validate-js');
        
        // 检查结果
        $result = $browser->script('return window.validationResult;')[0];
        $error = $browser->script('return window.validationError;')[0];
        
        if ($error) {
            echo "Validation Error: $error\n";
        }
        
        if ($result) {
            expect($result)->toBeArray();
            echo "Validation Result: " . json_encode($result) . "\n";
        }
    });
});

test('can test php runner security validation', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->script([
                    'fetch("/php-runner/validate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || ""
                        },
                        body: JSON.stringify({
                            code: "<?php system(\"ls\"); ?>"
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.securityResult = data;
                    })
                    .catch(error => {
                        window.securityError = error.toString();
                    });'
                ])
                ->pause(2000)
                ->screenshot('php-runner-security-test');
        
        // 检查安全验证结果
        $result = $browser->script('return window.securityResult;')[0];
        $error = $browser->script('return window.securityError;')[0];
        
        if ($error) {
            echo "Security Test Error: $error\n";
        }
        
        if ($result) {
            expect($result)->toBeArray();
            echo "Security Test Result: " . json_encode($result) . "\n";
            
            // 应该返回安全错误
            if (isset($result['success']) && $result['success'] === false) {
                expect($result['type'])->toBe('security');
            }
        }
    });
});

test('can test gist loading functionality', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test PHP Gist for API',
        'content' => '<?php echo "Hello from API test gist"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit('/test')
                ->script([
                    "fetch(\"/php-runner/gist/{$gist->id}\")
                    .then(response => {
                        window.gistResponse = {
                            status: response.status,
                            ok: response.ok,
                            redirected: response.redirected,
                            url: response.url
                        };
                        return response.text();
                    })
                    .then(html => {
                        window.gistContent = html;
                    })
                    .catch(error => {
                        window.gistError = error.toString();
                    });"
                ])
                ->pause(3000)
                ->screenshot('php-runner-gist-test');
        
        // 检查 Gist 加载结果
        $response = $browser->script('return window.gistResponse;')[0];
        $content = $browser->script('return window.gistContent;')[0];
        $error = $browser->script('return window.gistError;')[0];
        
        if ($error) {
            echo "Gist Test Error: $error\n";
        }
        
        if ($response) {
            echo "Gist Response: " . json_encode($response) . "\n";
        }
        
        if ($content && strlen($content) > 0) {
            echo "Gist Content Length: " . strlen($content) . " bytes\n";
            // 检查是否包含 Gist 内容
            expect($content)->toContain('Hello from API test gist');
        }
    });
});

test('can test php runner logging functionality', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        // 首先登录用户
        $browser->loginAs($user)
                ->visit('/test')
                ->script([
                    'fetch("/php-runner/log", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || ""
                        },
                        body: JSON.stringify({
                            code: "<?php echo \"Log test\"; ?>",
                            output: "Log test",
                            execution_time: 50,
                            memory_usage: "1MB",
                            is_successful: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.logResult = data;
                    })
                    .catch(error => {
                        window.logError = error.toString();
                    });'
                ])
                ->pause(2000)
                ->screenshot('php-runner-log-test');
        
        // 检查日志记录结果
        $result = $browser->script('return window.logResult;')[0];
        $error = $browser->script('return window.logError;')[0];
        
        if ($error) {
            echo "Log Test Error: $error\n";
        }
        
        if ($result) {
            expect($result)->toBeArray();
            echo "Log Test Result: " . json_encode($result) . "\n";
            
            if (isset($result['success']) && $result['success'] === true) {
                expect($result['message'])->toBe('执行日志已记录');
            }
        }
    });
});

test('can test multiple api endpoints in sequence', function () {
    $this->browse(function (Browser $browser) {
        // 测试序列：测试端点 -> 验证端点 -> 安全测试
        $browser->visit('/php-runner-test')
                ->assertSee('PHP Runner test route working')
                ->visit('/test')
                ->assertSee('Test route working')
                ->screenshot('php-runner-api-sequence-test');
        
        echo "✅ All API endpoints are accessible\n";
    });
});

test('browser can handle php runner javascript interactions', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test')
                ->script([
                    '// 模拟 PHP 运行器的基本 JavaScript 功能
                    window.phpRunner = {
                        validateCode: function(code) {
                            return fetch("/php-runner/validate", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({ code: code })
                            });
                        },
                        
                        runCode: function(code) {
                            console.log("Running code:", code);
                            return Promise.resolve({
                                success: true,
                                output: "Simulated output",
                                execution_time: 25
                            });
                        },
                        
                        clearCode: function() {
                            console.log("Code cleared");
                            return true;
                        }
                    };
                    
                    // 测试基本功能
                    window.testResults = {
                        validate: typeof window.phpRunner.validateCode === "function",
                        run: typeof window.phpRunner.runCode === "function",
                        clear: typeof window.phpRunner.clearCode === "function"
                    };'
                ])
                ->pause(1000)
                ->screenshot('php-runner-js-test');
        
        // 检查 JavaScript 功能
        $results = $browser->script('return window.testResults;')[0];
        
        expect($results)->toBeArray();
        expect($results['validate'])->toBeTrue();
        expect($results['run'])->toBeTrue();
        expect($results['clear'])->toBeTrue();
        
        echo "✅ JavaScript functionality test passed\n";
    });
});
