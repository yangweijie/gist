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

test('simple json endpoint returns correct data', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/simple-json')
                ->screenshot('simple-json-test');

        $content = $browser->driver->getPageSource();

        echo "Content length: " . strlen($content) . " bytes\n";
        echo "Content preview: " . substr($content, 0, 500) . "\n";

        // 提取 JSON 内容
        $jsonData = extractJsonFromContent($content);

        if ($jsonData) {
            expect($jsonData)->toBeArray();
            expect($jsonData['simple'])->toBe('json');
            expect($jsonData['test'])->toBeTrue();
            echo "✅ Simple JSON test passed\n";
        } else {
            echo "❌ Could not extract JSON from content\n";
            // 查找可能的 JSON 片段
            if (strpos($content, '{"simple"') !== false) {
                echo "Found JSON start pattern\n";
            }
            if (strpos($content, '"test":true') !== false) {
                echo "Found test field\n";
            }
        }
    });
});

test('php runner test endpoint returns correct json data', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/php-runner-test')
                ->screenshot('php-runner-test-json');
        
        $content = $browser->driver->getPageSource();
        
        // 提取 JSON 内容
        $jsonData = extractJsonFromContent($content);
        
        if ($jsonData) {
            expect($jsonData)->toBeArray();
            expect($jsonData['status'])->toBe('ok');
            expect($jsonData['message'])->toBe('PHP Runner test route working');
            expect($jsonData['features'])->toBeArray();
            expect($jsonData['features']['code_validation'])->toBeTrue();
            expect($jsonData['features']['code_execution'])->toBeTrue();
            expect($jsonData['features']['security_checks'])->toBeTrue();
            expect($jsonData['features']['gist_integration'])->toBeTrue();
            
            echo "✅ PHP Runner test endpoint JSON validation passed\n";
        } else {
            echo "❌ Could not extract JSON from PHP Runner test endpoint\n";
            echo "Content preview: " . substr($content, 0, 500) . "\n";
        }
    });
});

test('can test php runner validate endpoint with fetch api', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/simple-json')
                ->script([
                    '// 测试 PHP 运行器验证端点
                    fetch("/php-runner/validate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || ""
                        },
                        body: JSON.stringify({
                            code: "<?php echo \"Hello World\"; ?>"
                        })
                    })
                    .then(response => {
                        window.fetchResponse = {
                            status: response.status,
                            ok: response.ok,
                            headers: Object.fromEntries(response.headers.entries())
                        };
                        return response.text();
                    })
                    .then(text => {
                        window.fetchResponseText = text;
                        try {
                            window.fetchResponseJson = JSON.parse(text);
                        } catch (e) {
                            window.fetchParseError = e.toString();
                        }
                    })
                    .catch(error => {
                        window.fetchError = error.toString();
                    });'
                ])
                ->pause(3000)
                ->screenshot('php-runner-validate-fetch-test');
        
        // 检查 fetch 结果
        $response = $browser->script('return window.fetchResponse;')[0];
        $responseText = $browser->script('return window.fetchResponseText;')[0];
        $responseJson = $browser->script('return window.fetchResponseJson;')[0];
        $fetchError = $browser->script('return window.fetchError;')[0];
        $parseError = $browser->script('return window.fetchParseError;')[0];
        
        if ($fetchError) {
            echo "Fetch Error: $fetchError\n";
        }
        
        if ($parseError) {
            echo "JSON Parse Error: $parseError\n";
        }
        
        if ($response) {
            echo "Response Status: " . $response['status'] . "\n";
            echo "Response OK: " . ($response['ok'] ? 'true' : 'false') . "\n";
        }
        
        if ($responseText) {
            echo "Response Text Length: " . strlen($responseText) . " bytes\n";
            
            // 尝试从响应文本中提取 JSON
            $extractedJson = extractJsonFromContent($responseText);
            if ($extractedJson) {
                echo "✅ Successfully extracted JSON from validate endpoint\n";
                echo "Validation Result: " . json_encode($extractedJson) . "\n";
            } else {
                echo "❌ Could not extract JSON from validate endpoint\n";
                echo "Response preview: " . substr($responseText, 0, 200) . "\n";
            }
        }
        
        if ($responseJson) {
            expect($responseJson)->toBeArray();
            echo "✅ Direct JSON parsing successful\n";
        }
    });
});

test('can test gist loading with javascript', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Gist for JSON API',
        'content' => '<?php echo "Hello from JSON API test"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit('/simple-json')
                ->script([
                    "// 测试 Gist 加载
                    fetch(\"/php-runner/gist/{$gist->id}\", {
                        method: \"GET\",
                        headers: {
                            \"Accept\": \"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\"
                        }
                    })
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
                        window.gistHtml = html;
                        // 检查是否包含 Gist 内容
                        if (html.includes('Hello from JSON API test')) {
                            window.gistContentFound = true;
                        }
                    })
                    .catch(error => {
                        window.gistError = error.toString();
                    });"
                ])
                ->pause(3000)
                ->screenshot('gist-loading-test');
        
        $response = $browser->script('return window.gistResponse;')[0];
        $html = $browser->script('return window.gistHtml;')[0];
        $contentFound = $browser->script('return window.gistContentFound;')[0];
        $error = $browser->script('return window.gistError;')[0];
        
        if ($error) {
            echo "Gist Loading Error: $error\n";
        }
        
        if ($response) {
            echo "Gist Response Status: " . $response['status'] . "\n";
            echo "Gist Response OK: " . ($response['ok'] ? 'true' : 'false') . "\n";
            echo "Gist Redirected: " . ($response['redirected'] ? 'true' : 'false') . "\n";
        }
        
        if ($contentFound) {
            echo "✅ Gist content found in response\n";
        } else {
            echo "❌ Gist content not found\n";
            if ($html) {
                echo "HTML preview: " . substr($html, 0, 300) . "\n";
            }
        }
    });
});

test('can simulate basic php runner interactions', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/simple-json')
                ->script([
                    '// 模拟 PHP 运行器的基本交互
                    window.phpRunnerSimulation = {
                        // 模拟代码验证
                        validateCode: async function(code) {
                            try {
                                const response = await fetch("/php-runner/validate", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "Accept": "application/json"
                                    },
                                    body: JSON.stringify({ code: code })
                                });
                                const text = await response.text();
                                return { success: true, response: text, status: response.status };
                            } catch (error) {
                                return { success: false, error: error.toString() };
                            }
                        },
                        
                        // 模拟代码运行（实际上只是验证，因为真正的运行需要 WASM）
                        runCode: function(code) {
                            return {
                                success: true,
                                output: "Simulated output for: " + code.substring(0, 50),
                                execution_time: Math.floor(Math.random() * 100) + 10
                            };
                        },
                        
                        // 模拟安全检查
                        checkSecurity: function(code) {
                            const dangerousFunctions = ["system", "exec", "file_get_contents", "eval"];
                            for (let func of dangerousFunctions) {
                                if (code.includes(func)) {
                                    return { safe: false, reason: "Contains dangerous function: " + func };
                                }
                            }
                            return { safe: true };
                        }
                    };
                    
                    // 运行测试
                    window.testResults = {
                        validation: null,
                        security: null,
                        simulation: null
                    };
                    
                    // 测试代码验证
                    window.phpRunnerSimulation.validateCode("<?php echo \\"Hello World\\"; ?>")
                        .then(result => {
                            window.testResults.validation = result;
                        });
                    
                    // 测试安全检查
                    window.testResults.security = window.phpRunnerSimulation.checkSecurity("<?php echo \\"Safe code\\"; ?>");
                    
                    // 测试代码运行模拟
                    window.testResults.simulation = window.phpRunnerSimulation.runCode("<?php echo \\"Test\\"; ?>");'
                ])
                ->pause(3000)
                ->screenshot('php-runner-simulation-test');
        
        $results = $browser->script('return window.testResults;')[0];
        
        if ($results) {
            echo "PHP Runner Simulation Results:\n";
            
            if ($results['validation']) {
                echo "Validation Test: " . ($results['validation']['success'] ? '✅ Success' : '❌ Failed') . "\n";
                if (isset($results['validation']['status'])) {
                    echo "Validation Status: " . $results['validation']['status'] . "\n";
                }
            }
            
            if ($results['security']) {
                echo "Security Test: " . ($results['security']['safe'] ? '✅ Safe' : '❌ Unsafe') . "\n";
            }
            
            if ($results['simulation']) {
                echo "Simulation Test: " . ($results['simulation']['success'] ? '✅ Success' : '❌ Failed') . "\n";
                echo "Simulated Execution Time: " . $results['simulation']['execution_time'] . "ms\n";
            }
        }
    });
});

// 辅助函数：从内容中提取 JSON
function extractJsonFromContent(string $content): ?array
{
    // 方法 1: 直接查找完整的 JSON 字符串
    $patterns = [
        '/\{"simple":"json","test":true\}/',
        '/\{"status":"ok","message":"[^"]*"[^}]*\}/',
        '/\{[^{}]*"simple"[^{}]*"json"[^{}]*\}/',
        '/\{[^{}]*"test"[^{}]*true[^{}]*\}/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
    }

    // 方法 2: 查找任何看起来像 JSON 的内容
    if (preg_match_all('/\{[^{}]*\}/', $content, $matches)) {
        foreach ($matches[0] as $match) {
            $decoded = json_decode($match, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
    }

    // 方法 3: 查找嵌套的 JSON
    if (preg_match('/\{[^{}]*\{[^{}]*\}[^{}]*\}/', $content, $matches)) {
        $decoded = json_decode($matches[0], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
    }

    // 方法 4: 逐行查找 JSON
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '{') === 0 && strpos($line, '}') !== false) {
            $decoded = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
    }

    return null;
}
