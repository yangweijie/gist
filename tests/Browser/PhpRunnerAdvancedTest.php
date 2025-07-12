<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Gist;
use Tests\Browser\Pages\PhpRunnerPage;

beforeEach(function () {
    // 清理浏览器状态
    $this->browse(function (Browser $browser) {
        $browser->driver->manage()->deleteAllCookies();
    });
});

test('can run complex php algorithms', function () {
    $this->browse(function (Browser $browser) {
        $fibonacciCode = '<?php
function fibonacci($n) {
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2);
}

for ($i = 0; $i < 10; $i++) {
    echo "F($i) = " . fibonacci($i) . "\n";
}
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $fibonacciCode, 'F(9) = 34')
                ->assertOutputContains($browser, 'F(0) = 0')
                ->assertOutputContains($browser, 'F(1) = 1')
                ->screenshot('php-runner-fibonacci');
    });
});

test('can handle php classes and objects', function () {
    $this->browse(function (Browser $browser) {
        $classCode = '<?php
class Calculator {
    public function add($a, $b) {
        return $a + $b;
    }
    
    public function multiply($a, $b) {
        return $a * $b;
    }
}

$calc = new Calculator();
echo "Addition: " . $calc->add(5, 3) . "\n";
echo "Multiplication: " . $calc->multiply(4, 7) . "\n";
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $classCode, 'Addition: 8')
                ->assertOutputContains($browser, 'Multiplication: 28')
                ->screenshot('php-runner-classes');
    });
});

test('can work with php arrays and functions', function () {
    $this->browse(function (Browser $browser) {
        $arrayCode = '<?php
$fruits = ["apple", "banana", "orange", "grape"];

echo "Original array:\n";
print_r($fruits);

$upperFruits = array_map("strtoupper", $fruits);
echo "\nUppercase fruits:\n";
print_r($upperFruits);

$filtered = array_filter($fruits, function($fruit) {
    return strlen($fruit) > 5;
});
echo "\nLong fruit names:\n";
print_r($filtered);
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $arrayCode, 'APPLE')
                ->assertOutputContains($browser, 'banana')
                ->assertOutputContains($browser, 'orange')
                ->screenshot('php-runner-arrays');
    });
});

test('handles php errors gracefully', function () {
    $this->browse(function (Browser $browser) {
        $errorCode = '<?php
function divide($a, $b) {
    if ($b == 0) {
        throw new Exception("Division by zero!");
    }
    return $a / $b;
}

try {
    echo "10 / 2 = " . divide(10, 2) . "\n";
    echo "10 / 0 = " . divide(10, 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $errorCode, 'Error: Division by zero!')
                ->assertOutputContains($browser, '10 / 2 = 5')
                ->screenshot('php-runner-error-handling');
    });
});

test('can work with php json and data manipulation', function () {
    $this->browse(function (Browser $browser) {
        $jsonCode = '<?php
$data = [
    "users" => [
        ["name" => "Alice", "age" => 25, "city" => "New York"],
        ["name" => "Bob", "age" => 30, "city" => "London"],
        ["name" => "Charlie", "age" => 35, "city" => "Tokyo"]
    ]
];

$json = json_encode($data, JSON_PRETTY_PRINT);
echo "JSON Data:\n" . $json . "\n\n";

$decoded = json_decode($json, true);
echo "Users over 28:\n";
foreach ($decoded["users"] as $user) {
    if ($user["age"] > 28) {
        echo $user["name"] . " (" . $user["age"] . ") from " . $user["city"] . "\n";
    }
}
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $jsonCode, 'Bob (30) from London')
                ->assertOutputContains($browser, 'Charlie (35) from Tokyo')
                ->assertOutputContains($browser, '"name": "Alice"')
                ->screenshot('php-runner-json');
    });
});

test('performance test with large data sets', function () {
    $this->browse(function (Browser $browser) {
        $performanceCode = '<?php
$start = microtime(true);

// Generate large array
$numbers = range(1, 10000);

// Perform operations
$sum = array_sum($numbers);
$squares = array_map(function($n) { return $n * $n; }, array_slice($numbers, 0, 100));
$filtered = array_filter($numbers, function($n) { return $n % 2 == 0; });

$end = microtime(true);
$executionTime = ($end - $start) * 1000;

echo "Sum of 1-10000: " . $sum . "\n";
echo "First 10 squares: " . implode(", ", array_slice($squares, 0, 10)) . "\n";
echo "Even numbers count: " . count($filtered) . "\n";
echo "Execution time: " . round($executionTime, 2) . " ms\n";
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, $performanceCode, 'Sum of 1-10000: 50005000', 15)
                ->assertOutputContains($browser, 'Even numbers count: 5000')
                ->assertOutputContains($browser, 'Execution time:')
                ->screenshot('php-runner-performance');
    });
});

test('can validate complex php syntax', function () {
    $this->browse(function (Browser $browser) {
        $validComplexCode = '<?php
interface Shape {
    public function getArea();
}

class Rectangle implements Shape {
    private $width, $height;
    
    public function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getArea() {
        return $this->width * $this->height;
    }
}

$rect = new Rectangle(5, 10);
echo "Rectangle area: " . $rect->getArea();
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->validateCodeScenario($browser, $validComplexCode, true)
                ->screenshot('php-runner-complex-validation');
    });
});

test('detects invalid php syntax in complex code', function () {
    $this->browse(function (Browser $browser) {
        $invalidCode = '<?php
class TestClass {
    public function testMethod() {
        echo "Missing closing brace"
    // Missing closing brace for method and class
?>';

        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->validateCodeScenario($browser, $invalidCode, false)
                ->screenshot('php-runner-invalid-complex');
    });
});

test('can share code execution results', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, '<?php echo "Shareable result"; ?>', 'Shareable result')
                ->click('#share-btn')
                ->waitFor('.share-modal', 5)
                ->assertSee('分享代码执行结果')
                ->screenshot('php-runner-share-modal');
    });
});

test('can save code as gist from php runner', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, '<?php echo "Code to save as gist"; ?>')
                ->click('#save-gist-btn')
                ->waitFor('.save-gist-modal', 5)
                ->type('#gist-title', 'Test PHP Code')
                ->type('#gist-description', 'Generated from PHP runner')
                ->click('#save-gist-confirm')
                ->waitForText('Gist 创建成功', 10)
                ->screenshot('php-runner-save-gist');
    });
});

test('execution history is maintained for logged in users', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, '<?php echo "First execution"; ?>', 'First execution')
                ->executeCodeScenario($browser, '<?php echo "Second execution"; ?>', 'Second execution')
                ->click('#history-btn')
                ->waitFor('.history-panel', 5)
                ->assertSee('First execution')
                ->assertSee('Second execution')
                ->screenshot('php-runner-history');
    });
});

test('can export execution results', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->executeCodeScenario($browser, '<?php echo "Export test"; ?>', 'Export test')
                ->click('#export-btn')
                ->waitFor('.export-options', 5)
                ->click('#export-txt')
                ->pause(2000) // Wait for download
                ->screenshot('php-runner-export');
    });
});

test('supports code formatting and beautification', function () {
    $this->browse(function (Browser $browser) {
        $uglyCode = '<?php $x=1;if($x>0){echo"positive";}else{echo"negative";}?>';
        
        $browser->visit(new PhpRunnerPage)
                ->waitForPhpReady($browser)
                ->typeCode($browser, $uglyCode)
                ->click('#format-btn')
                ->pause(2000)
                ->assertCodeContains($browser, 'if ($x > 0) {')
                ->assertCodeContains($browser, '    echo "positive";')
                ->screenshot('php-runner-formatted');
    });
});
