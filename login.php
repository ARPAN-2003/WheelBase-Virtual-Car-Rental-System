<?php
    // login.php
    // Processes login form (auth/login.html). Supports admin fixed credentials and normal users.
    // Expects POST: user_type, username, password, optional next (from login form).
    session_start();

    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';

    // helper: safe redirect only to internal paths beginning with BASE_URL
    function safe_redirect($url, $default) {
        // allow empty default
        if ($url) {
            // allow either absolute (starting with BASE_URL) or relative to BASE_URL
            if (strpos($url, BASE_URL) === 0 || strpos($url, '/') === 0 && strpos($url, BASE_URL) === 0) {
                header('Location: ' . $url);
                exit;
            }
            // if next is relative like "customer/browse-cars.php?..." allow it by prefixing BASE_URL
            if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0 && strpos($url, BASE_URL) !== 0) {
                $candidate = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
                header('Location: ' . $candidate);
                exit;
            }
        }
        // fallback
        header('Location: ' . $default);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // If GET to /login.php, forward to login page
        header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html');
        exit;
    }

    $user_type = $_POST['user_type'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $next = $_POST['next'] ?? $_GET['next'] ?? '';

    // basic validation
    if (!$user_type || !$username || !$password) {
        $_SESSION['login_error'] = 'Please fill all required fields.';
        // preserve next if present
        $loc = rtrim(BASE_URL, '/') . '/auth/login.html';
        if ($next) $loc .= '?next=' . urlencode($next) . '&role=' . urlencode($user_type);
        header('Location: ' . $loc);
        exit;
    }

    // ADMIN fixed credentials (config)
    if ($user_type === 'admin') {
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            // success
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = ADMIN_USERNAME;
            $_SESSION['role'] = 'admin';
            // if next is safe and internal, redirect there, otherwise go to admin dashboard
            if ($next && strpos($next, BASE_URL) === 0) safe_redirect($next, rtrim(BASE_URL, '/') . '/admin/dashboard.php');
            safe_redirect(rtrim(BASE_URL, '/') . '/admin/dashboard.php', rtrim(BASE_URL, '/') . '/admin/dashboard.php');
        } else {
            $_SESSION['login_error'] = 'Invalid admin credentials';
            // redirect back to login with role preselected
            $loc = rtrim(BASE_URL, '/') . '/auth/login.html?role=admin';
            if ($next) $loc .= '&next=' . urlencode($next);
            header('Location: ' . $loc);
            exit;
        }
    }

    // FOR CUSTOMER / RETAILER: check DB
    $mysqli = get_db();
    $stmt = $mysqli->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Prepare failed in login.php: " . $mysqli->error);
        $_SESSION['login_error'] = 'Server error';
        header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html');
        exit;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $db_role);
    if ($stmt->fetch()) {
        // user found
        if (password_verify($password, $hash) && $db_role === $user_type) {
            // success
            $_SESSION['user_id'] = (int)$id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $db_role;

            // safe redirect to next if it starts with BASE_URL OR is relative (we prefix)
            if ($next) {
                // If next is absolute (starts with BASE_URL)
                if (strpos($next, BASE_URL) === 0) safe_redirect($next, rtrim(BASE_URL, '/') . '/');
                // If next is relative (customer/... without leading /), prefix BASE_URL
                if (strpos($next, '/') !== 0) {
                    $candidate = rtrim(BASE_URL, '/') . '/' . ltrim($next, '/');
                    safe_redirect($candidate, rtrim(BASE_URL, '/') . '/');
                }
                // If next starts with '/' but not BASE_URL -> block and go to dashboard
            }

            // default redirects based on role
            if ($db_role === 'customer') safe_redirect(rtrim(BASE_URL, '/') . '/customer/dashboard.php', rtrim(BASE_URL, '/') . '/');
            if ($db_role === 'retailer') safe_redirect(rtrim(BASE_URL, '/') . '/retailer/dashboard.php', rtrim(BASE_URL, '/') . '/');
            // fallback
            safe_redirect(rtrim(BASE_URL, '/') . '/', rtrim(BASE_URL, '/') . '/');
        } else {
            $_SESSION['login_error'] = 'Invalid credentials or wrong role selected.';
            $loc = rtrim(BASE_URL, '/') . '/auth/login.html?role=' . urlencode($user_type);
            if ($next) $loc .= '&next=' . urlencode($next);
            header('Location: ' . $loc);
            exit;
        }
    } else {
        $_SESSION['login_error'] = 'User not found';
        $loc = rtrim(BASE_URL, '/') . '/auth/login.html';
        if ($user_type) $loc .= '?role=' . urlencode($user_type);
        if ($next) $loc .= ($user_type ? '&' : '?') . 'next=' . urlencode($next);
        header('Location: ' . $loc);
        exit;
    }
    $stmt->close();
    $mysqli->close();
?>