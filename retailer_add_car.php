<?php
// retailer_add_car.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Allow only logged-in retailers
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'retailer') {
    header('Location: ' . BASE_URL . 'auth/login.html?role=retailer');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'retailer/dashboard.php');
    exit;
}

$reg_no   = trim($_POST['reg_no'] ?? '');
$car_name = trim($_POST['car_name'] ?? '');
$brand    = trim($_POST['brand_name'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);
$price    = (float)($_POST['price_per_hour'] ?? 0);

$owner_username = $_SESSION['username'];
$added_by       = $_SESSION['user_id'];

if (!$reg_no || !$car_name || !$brand || $capacity <= 0 || $price <= 0) {
    die("Invalid input");
}

/* ---------- IMAGE UPLOAD ---------- */
$image_path = null;

if (!empty($_FILES['car_image']['name'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = time() . '_' . basename($_FILES['car_image']['name']);
    $target = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target)) {
        $image_path = 'uploads/' . $filename;
    }
}

/* ---------- INSERT INTO DB ---------- */
$db = get_db();

$stmt = $db->prepare("
    INSERT INTO cars 
    (reg_no, car_name, brand_name, capacity, price_per_hour, image_path, added_by, owner_username)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssidsis",
    $reg_no,
    $car_name,
    $brand,
    $capacity,
    $price,
    $image_path,
    $added_by,
    $owner_username
);

if (!$stmt->execute()) {
    die("Error adding car: " . $stmt->error);
}

$stmt->close();
$db->close();

header('Location: ' . BASE_URL . 'retailer/dashboard.php');
exit;
