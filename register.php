<?php
// register.php
// Handles registration for customer and retailer
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Redirect back to register page with errors
 */
function redirect_with_error($message, $role = 'customer') {
    $_SESSION['register_error'] = $message;
    $loc = rtrim(BASE_URL, '/') . '/auth/register.html?role=' . urlencode($role);
    header('Location: ' . $loc);
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/auth/register.html');
    exit;
}

$role     = $_POST['user_type'] ?? 'customer';
$name     = trim($_POST['name'] ?? '');
$aadhaar  = trim($_POST['aadhaar'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone    = trim($_POST['phone'] ?? '');

// Normalize role
if (!in_array($role, ['customer', 'retailer'])) {
    $role = 'customer';
}

/* =====================================================
   SERVER-SIDE VALIDATION
===================================================== */
if ($name === '' || !preg_match('/^[A-Za-z ]+$/', $name)) {
    redirect_with_error('Full Name must contain only letters and spaces.', $role);
}

if (!preg_match('/^\d{12}$/', $aadhaar)) {
    redirect_with_error('Aadhaar number must be exactly 12 digits.', $role);
}

if ($username === '') {
    redirect_with_error('Username is required.', $role);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('Please enter a valid email address.', $role);
}

if (!preg_match('/^\d{10}$/', $phone)) {
    redirect_with_error('Phone number must be exactly 10 digits.', $role);
}

if (!preg_match('/(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}/', $password)) {
    redirect_with_error(
        'Password must be at least 8 characters and include 1 uppercase letter, 1 number, and 1 special character.',
        $role
    );
}

/* =====================================================
   DATABASE CHECKS
===================================================== */
$mysqli = get_db();

// Check username uniqueness
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    redirect_with_error('Server error. Please try again.', $role);
}
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    redirect_with_error('Username already exists. Please choose another.', $role);
}
$stmt->close();

/* =====================================================
   INSERT USER
===================================================== */
$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $mysqli->prepare(
    "INSERT INTO users (name, aadhaar, username, email, password_hash, role, phone)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$insert) {
    redirect_with_error('Server error while creating account.', $role);
}

$insert->bind_param(
    'sssssss',
    $name,
    $aadhaar,
    $username,
    $email,
    $hash,
    $role,
    $phone
);

if (!$insert->execute()) {
    $insert->close();
    redirect_with_error('Could not create account. Please try again.', $role);
}

$newUserId = $insert->insert_id;
$insert->close();

/* =====================================================
   AUTO LOGIN AFTER REGISTRATION
===================================================== */
$_SESSION['user_id'] = (int)$newUserId;
$_SESSION['username'] = $username;
$_SESSION['role'] = $role;

/* =====================================================
   REDIRECT TO DASHBOARD
===================================================== */
if ($role === 'customer') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/customer/dashboard.php');
} else {
    header('Location: ' . rtrim(BASE_URL, '/') . '/retailer/dashboard.php');
}
exit;
