<?php
    // register.php
    // Handles registration for customer and retailer.
    // Expects POST: user_type, name, aadhaar, username, email, password, phone (optional)
    session_start();

    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';

    // helper
    function redirect_with_errors($errors, $role = 'customer') {
        $_SESSION['reg_errors'] = $errors;
        $loc = rtrim(BASE_URL, '/') . '/auth/register.html?role=' . urlencode($role);
        header('Location: ' . $loc);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . rtrim(BASE_URL, '/') . '/auth/register.html');
        exit;
    }

    $role = $_POST['user_type'] ?? 'customer';
    $name = trim($_POST['name'] ?? '');
    $aadhaar = trim($_POST['aadhaar'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    $errors = [];
    if (!$name) $errors[] = 'Name is required';
    if (!preg_match('/^\d{12}$/', $aadhaar)) $errors[] = 'Aadhaar must be 12 digits';
    if (!$username) $errors[] = 'Username is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if (!in_array($role, ['customer','retailer'])) $role = 'customer';

    if (!empty($errors)) redirect_with_errors($errors, $role);

    // check uniqueness of username
    $mysqli = get_db();
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt) {
        redirect_with_errors(['Server error (prepare)'], $role);
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        redirect_with_errors(['Username already exists'], $role);
    }
    $stmt->close();

    // everything OK, insert
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $mysqli->prepare("INSERT INTO users (name, aadhaar, username, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$insert) {
        redirect_with_errors(['Server error (insert prepare)'], $role);
    }
    $insert->bind_param('sssssss', $name, $aadhaar, $username, $email, $hash, $role, $phone);
    if (!$insert->execute()) {
        $insert->close();
        redirect_with_errors(['Server error: could not create account'], $role);
    }
    $new_id = $insert->insert_id;
    $insert->close();

    // auto-login after register
    $_SESSION['user_id'] = (int)$new_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;

    // redirect to dashboard
    if ($role === 'customer') header('Location: ' . rtrim(BASE_URL, '/') . '/customer/dashboard.php');
    else header('Location: ' . rtrim(BASE_URL, '/') . '/retailer/dashboard.php');

    exit;
?>