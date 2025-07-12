<?php

namespace App\Http\Controllers;

use App\Models\Gist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PhpRunnerController extends Controller
{
    /**
     * 显示 PHP 运行器页面
     */
    public function index()
    {
        return view('php-runner.index');
    }

    /**
     * 显示带有 Gist 代码的运行器页面
     */
    public function runGist(Gist $gist)
    {
        // 检查权限
        if (!$gist->is_public && (!Auth::check() || $gist->user_id !== Auth::id())) {
            abort(403, '无权访问此 Gist');
        }

        // 只支持 PHP 代码
        if (strtolower($gist->language) !== 'php') {
            return redirect()->route('gists.show', $gist)
                ->with('error', '只支持运行 PHP 代码');
        }

        return view('php-runner.index', compact('gist'));
    }

    /**
     * 验证 PHP 代码（语法检查）
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $code = $request->input('code');

        // 基本的 PHP 语法检查
        $syntaxCheck = $this->checkPhpSyntax($code);

        if (!$syntaxCheck['valid']) {
            return response()->json([
                'success' => false,
                'error' => $syntaxCheck['error']
            ]);
        }

        // 安全检查
        $securityCheck = $this->checkCodeSecurity($code);

        if (!$securityCheck['safe']) {
            return response()->json([
                'success' => false,
                'error' => $securityCheck['error'],
                'type' => 'security'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '代码语法正确'
        ]);
    }

    /**
     * 记录代码运行日志
     */
    public function logExecution(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50000',
            'output' => 'string|max:100000',
            'error' => 'string|max:10000',
            'execution_time' => 'numeric|min:0',
            'memory_usage' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 记录执行日志
        Log::info('PHP Code Execution', [
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'code_length' => strlen($request->input('code')),
            'has_output' => !empty($request->input('output')),
            'has_error' => !empty($request->input('error')),
            'execution_time' => $request->input('execution_time'),
            'memory_usage' => $request->input('memory_usage'),
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '执行日志已记录'
        ]);
    }

    /**
     * 获取 PHP 代码示例
     */
    public function examples()
    {
        $examples = [
            [
                'title' => 'Hello World',
                'description' => '基础的 PHP 输出',
                'code' => '<?php
echo "Hello, World!";
echo "\n";
echo "当前时间: " . date("Y-m-d H:i:s");
?>'
            ],
            [
                'title' => '数组操作',
                'description' => 'PHP 数组的基本操作',
                'code' => '<?php
$fruits = ["苹果", "香蕉", "橙子"];
echo "水果列表:\n";
foreach ($fruits as $index => $fruit) {
    echo ($index + 1) . ". " . $fruit . "\n";
}

$fruits[] = "葡萄";
echo "\n添加葡萄后:\n";
print_r($fruits);
?>'
            ],
            [
                'title' => '函数定义',
                'description' => '定义和使用函数',
                'code' => '<?php
function fibonacci($n) {
    if ($n <= 1) {
        return $n;
    }
    return fibonacci($n - 1) + fibonacci($n - 2);
}

echo "斐波那契数列前10项:\n";
for ($i = 0; $i < 10; $i++) {
    echo fibonacci($i) . " ";
}
echo "\n";
?>'
            ],
            [
                'title' => '类和对象',
                'description' => 'PHP 面向对象编程',
                'code' => '<?php
class Person {
    private $name;
    private $age;

    public function __construct($name, $age) {
        $this->name = $name;
        $this->age = $age;
    }

    public function introduce() {
        return "我是 {$this->name}，今年 {$this->age} 岁。";
    }

    public function getAge() {
        return $this->age;
    }
}

$person = new Person("张三", 25);
echo $person->introduce() . "\n";
echo "年龄: " . $person->getAge() . "\n";
?>'
            ],
            [
                'title' => 'JSON 处理',
                'description' => 'JSON 编码和解码',
                'code' => '<?php
$data = [
    "name" => "张三",
    "age" => 30,
    "skills" => ["PHP", "JavaScript", "Python"],
    "active" => true
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "JSON 编码:\n";
echo $json . "\n\n";

$decoded = json_decode($json, true);
echo "解码后的数据:\n";
echo "姓名: " . $decoded["name"] . "\n";
echo "技能: " . implode(", ", $decoded["skills"]) . "\n";
?>'
            ]
        ];

        return response()->json($examples);
    }

    /**
     * 检查 PHP 代码语法
     */
    private function checkPhpSyntax($code)
    {
        // 确保代码以 <?php 开头
        if (!str_starts_with(trim($code), '<?php')) {
            $code = '<?php ' . $code;
        }

        // 使用 php -l 检查语法（在实际环境中）
        // 这里我们做一些基本检查
        $result = ['valid' => true, 'error' => null];

        // 检查是否有未闭合的括号
        $openBraces = substr_count($code, '{');
        $closeBraces = substr_count($code, '}');
        if ($openBraces !== $closeBraces) {
            $result['valid'] = false;
            $result['error'] = '括号不匹配：{ 和 } 的数量不相等';
            return $result;
        }

        $openParens = substr_count($code, '(');
        $closeParens = substr_count($code, ')');
        if ($openParens !== $closeParens) {
            $result['valid'] = false;
            $result['error'] = '圆括号不匹配：( 和 ) 的数量不相等';
            return $result;
        }

        // 检查是否有基本的语法错误
        if (preg_match('/\$[^a-zA-Z_]/', $code)) {
            $result['valid'] = false;
            $result['error'] = '变量名语法错误';
            return $result;
        }

        return $result;
    }

    /**
     * 检查代码安全性
     */
    private function checkCodeSecurity($code)
    {
        $result = ['safe' => true, 'error' => null];

        // 危险函数列表
        $dangerousFunctions = [
            'exec', 'system', 'shell_exec', 'passthru', 'eval',
            'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
            'curl_exec', 'curl_init', 'fsockopen', 'pfsockopen',
            'stream_socket_client', 'stream_socket_server',
            'mail', 'header', 'setcookie', 'session_start',
            'include', 'require', 'include_once', 'require_once',
            'unlink', 'rmdir', 'mkdir', 'chmod', 'chown',
            'phpinfo', 'show_source', 'highlight_file'
        ];

        foreach ($dangerousFunctions as $func) {
            if (preg_match('/\b' . preg_quote($func) . '\s*\(/i', $code)) {
                $result['safe'] = false;
                $result['error'] = "不允许使用函数: {$func}()";
                return $result;
            }
        }

        // 检查超全局变量
        $dangerousGlobals = ['$_SERVER', '$_ENV', '$_GET', '$_POST', '$_FILES', '$_COOKIE', '$_SESSION'];
        foreach ($dangerousGlobals as $global) {
            if (strpos($code, $global) !== false) {
                $result['safe'] = false;
                $result['error'] = "不允许使用超全局变量: {$global}";
                return $result;
            }
        }

        // 检查类实例化
        if (preg_match('/new\s+[A-Za-z_][A-Za-z0-9_]*\s*\(/i', $code)) {
            // 允许的类列表
            $allowedClasses = ['DateTime', 'DateTimeImmutable', 'Exception', 'stdClass'];
            $pattern = '/new\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/i';
            if (preg_match_all($pattern, $code, $matches)) {
                foreach ($matches[1] as $className) {
                    if (!in_array($className, $allowedClasses)) {
                        $result['safe'] = false;
                        $result['error'] = "不允许实例化类: {$className}";
                        return $result;
                    }
                }
            }
        }

        return $result;
    }
}
