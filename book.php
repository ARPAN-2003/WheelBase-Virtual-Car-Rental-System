<?php
// book.php (ROOT) — handle booking submission
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function redirect_exit($url) {
    header("Location: $url");
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_exit(BASE_URL . '/browse-cars.php');
}

// Must be logged in as customer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    redirect_exit(BASE_URL . 'auth/login.html?role=customer');
}

$userId   = (int)$_SESSION['user_id'];
$carRegNo = trim($_POST['car_reg_no'] ?? '');
$city     = trim($_POST['city'] ?? '');
$location = trim($_POST['location'] ?? '');
$pickup   = trim($_POST['pickup_datetime'] ?? '');
$drop     = trim($_POST['drop_datetime'] ?? '');

if (!$carRegNo || !$city || !$location || !$pickup || !$drop) {
    $_SESSION['book_error'] = 'All fields are required.';
    redirect_exit(BASE_URL . '/browse-cars.php');
}

$pickupDT = date_create($pickup);
$dropDT   = date_create($drop);

if (!$pickupDT || !$dropDT || $pickupDT >= $dropDT) {
    $_SESSION['book_error'] = 'Invalid pickup/drop time.';
    redirect_exit(BASE_URL . 'customer/book.php?reg_no=' . urlencode($carRegNo));
}

// Duration (hours)
$hours = max(1, ceil(($dropDT->getTimestamp() - $pickupDT->getTimestamp()) / 3600));

$db = get_db();

// Fetch car
$stmt = $db->prepare("SELECT price_per_hour FROM cars WHERE reg_no = ?");
$stmt->bind_param("s", $carRegNo);
$stmt->execute();
$stmt->bind_result($rate);

if (!$stmt->fetch()) {
    $stmt->close();
    $_SESSION['book_error'] = 'Selected car not found.';
    redirect_exit(BASE_URL . '/browse-cars.php');
}
$stmt->close();

$total = round($rate * $hours, 2);

// Insert booking (STRICTLY Pending)
$stmt = $db->prepare("
    INSERT INTO bookings
    (user_id, car_reg_no, city, location, pickup_datetime, drop_datetime,
     rate_per_hour, total_amount, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
");

$stmt->bind_param(
    "isssssdd",
    $userId,
    $carRegNo,
    $city,
    $location,
    $pickupDT->format('Y-m-d H:i:s'),
    $dropDT->format('Y-m-d H:i:s'),
    $rate,
    $total
);

$stmt->execute();
$stmt->close();

$_SESSION['book_success'] = 'Booking created successfully. Status: Pending.';
redirect_exit(BASE_URL . 'customer/my-bookings.php');
