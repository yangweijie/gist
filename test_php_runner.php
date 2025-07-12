<?php

// 简单的测试脚本来检查 PHP 运行器页面
$url = 'http://localhost:8000/php-runner';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}

if ($httpCode === 200) {
    echo "✅ PHP Runner page loaded successfully!\n";
    echo "Response length: " . strlen($response) . " bytes\n";
} else {
    echo "❌ PHP Runner page failed to load\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}
