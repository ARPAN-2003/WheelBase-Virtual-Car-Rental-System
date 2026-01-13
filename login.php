<?php
// login.php
// Handles login for admin (fixed credentials) and customer/retailer (DB-based)

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Safe redirect helper
 * - Allows only internal redirects
 */
function safe_redirect($url, $default) {
    if (!empty($url)) {

        // Absolute internal URL (starts with BASE_URL)
        if (strpos($url, BASE_URL) === 0) {
            header("Location: $url");
            exit;
        }

        // Relative internal path like "customer/browse-cars.php"
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $candidate = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
            header("Location: $candidate");
            exit;
        }
    }

    // Fallback redirect
    header("Location: $default");
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html');
    exit;
}

// Read inputs
$user_type = $_POST['user_type'] ?? '';
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$next      = $_POST['next'] ?? '';

// Basic validation
if ($user_type === '' || $username === '' || $password === '') {
    $_SESSION['login_error'] = 'Please fill all required fields.';
    header(
        'Location: ' . rtrim(BASE_URL, '/') . 
        '/auth/login.html?role=' . urlencode($user_type)
    );
    exit;
}

/* =====================================================
   ADMIN LOGIN (FIXED CREDENTIALS)
===================================================== */
if ($user_type === 'admin') {

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {

        $_SESSION['user_id']  = 0;
        $_SESSION['username'] = ADMIN_USERNAME;
        $_SESSION['role']     = 'admin';

        safe_redirect(
            $next,
            rtrim(BASE_URL, '/') . '/admin/dashboard.php'
        );

    } else {
        $_SESSION['login_error'] = 'Invalid admin credentials.';
        header(
            'Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html?role=admin'
        );
        exit;
    }
}

/* =====================================================
   CUSTOMER / RETAILER LOGIN (DATABASE)
===================================================== */
$mysqli = get_db();

$stmt = $mysqli->prepare(
    "SELECT id, password_hash, role FROM users WHERE username = ?"
);

if (!$stmt) {
    error_log("Login prepare failed: " . $mysqli->error);
    $_SESSION['login_error'] = 'Server error. Please try again.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html');
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($id, $hash, $db_role);

if ($stmt->fetch()) {

    // Password check
    if (!password_verify($password, $hash)) {
        $_SESSION['login_error'] = 'Incorrect password.';
        header(
            'Location: ' . rtrim(BASE_URL, '/') . 
            '/auth/login.html?role=' . urlencode($user_type)
        );
        exit;
    }

    // Role mismatch
    if ($db_role !== $user_type) {
        $_SESSION['login_error'] = 'Wrong role selected for this account.';
        header(
            'Location: ' . rtrim(BASE_URL, '/') . 
            '/auth/login.html?role=' . urlencode($user_type)
        );
        exit;
    }

    // Successful login
    $_SESSION['user_id']  = (int)$id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $db_role;

    if ($db_role === 'customer') {
        safe_redirect(
            $next,
            rtrim(BASE_URL, '/') . '/customer/dashboard.php'
        );
    }

    if ($db_role === 'retailer') {
        safe_redirect(
            $next,
            rtrim(BASE_URL, '/') . '/retailer/dashboard.php'
        );
    }

    // Final fallback
    safe_redirect(
        rtrim(BASE_URL, '/') . '/',
        rtrim(BASE_URL, '/') . '/'
    );

} else {
    $_SESSION['login_error'] = 'User does not exist.';
    header(
        'Location: ' . rtrim(BASE_URL, '/') . 
        '/auth/login.html?role=' . urlencode($user_type)
    );
    exit;
}

$stmt->close();
$mysqli->close();
