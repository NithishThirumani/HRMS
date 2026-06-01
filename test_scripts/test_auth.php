<?php
$base_url = 'http://localhost/emps';

function test_post($url, $data, $cookie_file = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookie_file) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $http_code, 'redirect' => $redirect_url, 'response' => $response];
}

function test_get($url, $cookie_file = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookie_file) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $http_code, 'redirect' => $redirect_url, 'response' => $response];
}

echo "Running Authentication Tests...\n";

// 1. Test Login Failure
$res = test_post("$base_url/login.php", ['email' => 'admin@bizwy.com', 'ps' => 'wrongpass']);
if (strpos($res['response'], 'Invalid Password') !== false || strpos($res['response'], 'Invalid Email') !== false) {
    echo "[PASS] Login failure handled correctly.\n";
} else {
    echo "[FAIL] Login failure handling.\n";
}

// 2. Test Super Admin Login Success
$admin_cookie = tempnam(sys_get_temp_dir(), 'cookie_admin');
$res = test_post("$base_url/login.php", ['email' => 'superadmin@bizwy.com', 'ps' => 'Super@123'], $admin_cookie);
if ($res['code'] == 302 && strpos($res['redirect'], 'super_admin_panel') !== false) {
    echo "[PASS] Super Admin Login successful.\n";
} else {
    echo "[FAIL] Super Admin Login failed. Code: {$res['code']}, Redirect: {$res['redirect']}\n";
}

// 3. Test Unauthorized Access (Access admin panel without session)
$res = test_get("$base_url/admin_panel/index.php");
if ($res['code'] == 302 && strpos($res['redirect'], 'login.php') !== false) {
    echo "[PASS] Unauthorized access to admin panel blocked.\n";
} else {
    echo "[FAIL] Unauthorized access to admin panel NOT blocked.\n";
}

// 4. Test Authorized Access (Access admin panel with admin session)
// Since super_admin is also allowed in admin_panel based on session.php role check
$res = test_get("$base_url/admin_panel/index.php", $admin_cookie);
if ($res['code'] == 200) {
    echo "[PASS] Authorized access to admin panel successful.\n";
} else {
    echo "[FAIL] Authorized access failed. Code: {$res['code']}\n";
}

unlink($admin_cookie);
echo "Done.\n";
?>
