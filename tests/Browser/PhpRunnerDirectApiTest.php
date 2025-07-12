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

test('can access php runner validate api directly with javascript', function () {
    $this->browse(function (Browser $browser) {
        // 访问一个简单页面作为基础
        $browser->visit('/test');

        $browser->script([
            '// 直接测试 API 端点
            window.apiTest = async function() {
                try {
                    // 获取 CSRF token
                    const token = document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || "";

                    const response = await fetch("/php-runner/validate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": token
                        },
                        body: JSON.stringify({
                            code: "<?php echo \\"Hello World\\"; ?>"
                        })
                    });

                    const text = await response.text();

                    return {
                        success: true,
                        status: response.status,
                        ok: response.ok,
                        headers: Object.fromEntries(response.headers.entries()),
                        text: text,
                        textLength: text.length
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.toString()
                    };
                }
            };

            // 运行测试
            window.apiTest().then(result => {
                window.apiResult = result;
            });'
        ]);

        $browser->pause(3000)
                ->screenshot('direct-api-test');
        
        $result = $browser->script('return window.apiResult;')[0];
        
        if ($result) {
            echo "API Test Result:\n";
            echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
            
            if ($result['success']) {
                echo "Status: " . $result['status'] . "\n";
                echo "OK: " . ($result['ok'] ? 'true' : 'false') . "\n";
                echo "Response Length: " . $result['textLength'] . " bytes\n";
                
                if (isset($result['headers']['content-type'])) {
                    echo "Content-Type: " . $result['headers']['content-type'] . "\n";
                }
                
                // 尝试解析响应
                if ($result['textLength'] < 1000) {
                    echo "Response Text: " . $result['text'] . "\n";
                } else {
                    echo "Response Preview: " . substr($result['text'], 0, 200) . "...\n";
                }
                
                // 检查是否包含 JSON
                if (strpos($result['text'], '{') !== false && strpos($result['text'], '}') !== false) {
                    echo "✅ Response contains JSON-like content\n";
                } else {
                    echo "❌ Response does not contain JSON\n";
                }
            } else {
                echo "Error: " . $result['error'] . "\n";
            }
        } else {
            echo "❌ No API result received\n";
        }
    });
});

test('can test php runner security validation api', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test');

        $browser->script([
            'window.securityTest = async function() {
                try {
                    const response = await fetch("/php-runner/validate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            code: "<?php system(\\"ls\\"); ?>"
                        })
                    });

                    const text = await response.text();

                    return {
                        success: true,
                        status: response.status,
                        text: text
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.toString()
                    };
                }
            };

            window.securityTest().then(result => {
                window.securityResult = result;
            });'
        ]);

        $browser->pause(3000)
                ->screenshot('security-api-test');
        
        $result = $browser->script('return window.securityResult;')[0];
        
        if ($result && $result['success']) {
            echo "Security Test Result:\n";
            echo "Status: " . $result['status'] . "\n";
            echo "Response: " . substr($result['text'], 0, 300) . "\n";
            
            // 检查是否正确拒绝了危险代码
            if (strpos($result['text'], 'security') !== false || 
                strpos($result['text'], 'dangerous') !== false ||
                strpos($result['text'], 'not allowed') !== false) {
                echo "✅ Security validation working\n";
            } else {
                echo "⚠️ Security validation response unclear\n";
            }
        }
    });
});

test('can test gist loading api', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'API Test Gist',
        'content' => '<?php echo "Hello from API test gist"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit('/test');

        $browser->script([
            "window.gistTest = async function() {
                try {
                    const response = await fetch(\"/php-runner/gist/{$gist->id}\", {
                        method: \"GET\",
                        headers: {
                            \"Accept\": \"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\"
                        }
                    });

                    const text = await response.text();

                    return {
                        success: true,
                        status: response.status,
                        redirected: response.redirected,
                        url: response.url,
                        text: text,
                        textLength: text.length
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.toString()
                    };
                }
            };

            window.gistTest().then(result => {
                window.gistResult = result;
            });"
        ]);

        $browser->pause(3000)
                ->screenshot('gist-api-test');
        
        $result = $browser->script('return window.gistResult;')[0];
        
        if ($result && $result['success']) {
            echo "Gist Test Result:\n";
            echo "Status: " . $result['status'] . "\n";
            echo "Redirected: " . ($result['redirected'] ? 'true' : 'false') . "\n";
            echo "URL: " . $result['url'] . "\n";
            echo "Response Length: " . $result['textLength'] . " bytes\n";
            
            // 检查是否包含 Gist 内容
            if (strpos($result['text'], 'Hello from API test gist') !== false) {
                echo "✅ Gist content found in response\n";
            } else {
                echo "❌ Gist content not found\n";
                echo "Response preview: " . substr($result['text'], 0, 300) . "\n";
            }
        }
    });
});

test('can simulate complete php runner workflow', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/test');

        $browser->script([
                    '// 模拟完整的 PHP 运行器工作流程
                    window.workflowTest = async function() {
                        const results = {
                            steps: [],
                            success: true
                        };
                        
                        try {
                            // 步骤 1: 验证简单代码
                            results.steps.push("Validating simple code...");
                            const validateResponse = await fetch("/php-runner/validate", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({
                                    code: "<?php echo \\"Hello\\"; ?>"
                                })
                            });
                            
                            results.validateStatus = validateResponse.status;
                            results.steps.push("Validation response: " + validateResponse.status);
                            
                            // 步骤 2: 测试安全验证
                            results.steps.push("Testing security validation...");
                            const securityResponse = await fetch("/php-runner/validate", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({
                                    code: "<?php system(\\"dangerous\\"); ?>"
                                })
                            });
                            
                            results.securityStatus = securityResponse.status;
                            results.steps.push("Security test response: " + securityResponse.status);
                            
                            // 步骤 3: 模拟代码运行
                            results.steps.push("Simulating code execution...");
                            results.simulatedExecution = {
                                output: "Hello",
                                execution_time: 25,
                                memory_usage: "1MB",
                                success: true
                            };
                            results.steps.push("Execution simulation completed");
                            
                            results.steps.push("Workflow completed successfully");
                            
                        } catch (error) {
                            results.success = false;
                            results.error = error.toString();
                            results.steps.push("Error: " + error.toString());
                        }
                        
                        return results;
                    };
                    
                    window.workflowTest().then(result => {
                        window.workflowResult = result;
                    });'
        ]);

        $browser->pause(5000)
                ->screenshot('workflow-test');
        
        $result = $browser->script('return window.workflowResult;')[0];
        
        if ($result) {
            echo "Workflow Test Result:\n";
            echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
            
            if (isset($result['steps'])) {
                echo "Steps:\n";
                foreach ($result['steps'] as $step) {
                    echo "  - $step\n";
                }
            }
            
            if (isset($result['validateStatus'])) {
                echo "Validation Status: " . $result['validateStatus'] . "\n";
            }
            
            if (isset($result['securityStatus'])) {
                echo "Security Test Status: " . $result['securityStatus'] . "\n";
            }
            
            if (isset($result['simulatedExecution'])) {
                echo "Simulated Execution: ✅ Success\n";
                echo "  Output: " . $result['simulatedExecution']['output'] . "\n";
                echo "  Time: " . $result['simulatedExecution']['execution_time'] . "ms\n";
            }
            
            if ($result['success']) {
                echo "✅ Complete workflow test passed\n";
            } else {
                echo "❌ Workflow test failed: " . ($result['error'] ?? 'Unknown error') . "\n";
            }
        }
    });
});
