<?php
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8080/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
]);
$html = curl_exec($ch);

preg_match('/<input[^>]+name="([^"]*csrf[^"]*)"[^>]+value="([^"]+)"/i', $html, $m);
if (!$m) {
    preg_match('/name="csrf-token" content="([^"]+)"/i', $html, $m2);
    $csrfName = 'csrf_test_name';
    $csrfVal = $m2[1] ?? '';
} else {
    $csrfName = $m[1];
    $csrfVal = $m[2];
}

curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8080/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        $csrfName => $csrfVal,
        'email' => 'admin2@test.com',
        'password' => '123'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$res = curl_exec($ch);

curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8080/document/qzbQ3lINhU4',
    CURLOPT_POST => false,
    CURLOPT_HTTPGET => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$docHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/doc.html', $docHtml);
echo "Saved doc.html, length: " . strlen($docHtml) . "\n";
