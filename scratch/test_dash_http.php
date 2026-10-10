<?php
function doRequest($url, $method = 'GET', $data = null, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: close']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data ?? []));
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

// 1. Test Admin Login and Dashboard
echo "=== Testing Admin Dashboard via HTTP ===\n";
$cookieAdmin = tempnam(sys_get_temp_dir(), 'ck_admin_');
$loginPage = doRequest('http://localhost:8080/login', 'GET', null, $cookieAdmin);
preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $loginPage['body'], $m);
$csrf = $m[1] ?? '';

$loginRes = doRequest('http://localhost:8080/login', 'POST', [
    'email'          => 'admin@test.com',
    'password'       => '123',
    'csrf_test_name' => $csrf
], $cookieAdmin);

$dashRes = doRequest('http://localhost:8080/dashboard', 'GET', null, $cookieAdmin);
echo "Admin Dashboard HTTP Code: " . $dashRes['code'] . "\n";
echo "Has 'CSC Rating Distribution': " . (str_contains($dashRes['body'], 'CSC Rating Distribution') ? 'YES' : 'NO') . "\n";
echo "Has 'Top Performing Personnel': " . (str_contains($dashRes['body'], 'Top Performing Personnel') ? 'YES' : 'NO') . "\n";
echo "Has 'Target Calibration': " . (str_contains($dashRes['body'], 'Target Calibration') ? 'YES' : 'NO') . "\n";
echo "Has 'STAGE 2 MONITORING': " . (str_contains($dashRes['body'], 'STAGE 2 MONITORING') ? 'YES' : 'NO') . "\n";

// 2. Test Dean / Supervisor Login and Dashboard
echo "\n=== Testing Supervisor Dashboard via HTTP ===\n";
$cookieDean = tempnam(sys_get_temp_dir(), 'ck_dean_');
$loginPage = doRequest('http://localhost:8080/login', 'GET', null, $cookieDean);
preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $loginPage['body'], $m);
$csrf = $m[1] ?? '';

$loginRes = doRequest('http://localhost:8080/login', 'POST', [
    'email'          => 'dean@test.com',
    'password'       => '123',
    'csrf_test_name' => $csrf
], $cookieDean);

$dashRes = doRequest('http://localhost:8080/dashboard', 'GET', null, $cookieDean);
echo "Supervisor Dashboard HTTP Code: " . $dashRes['code'] . "\n";
echo "Has 'CSC Rating Distribution': " . (str_contains($dashRes['body'], 'CSC Rating Distribution') ? 'YES' : 'NO') . "\n";
echo "Has 'Top Performing Personnel': " . (str_contains($dashRes['body'], 'Top Performing Personnel') ? 'YES' : 'NO') . "\n";

@unlink($cookieAdmin);
@unlink($cookieDean);
