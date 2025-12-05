<?php
// book.php - handle booking form submission for customers
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function redirect_and_exit($url) {
    header('Location: ' . $url);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_and_exit(rtrim(BASE_URL, '/') . '/customer/browse-cars.php');
}

// Must be logged in as customer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    $_SESSION['book_error'] = 'Please login as a customer to make a booking.';
    $loginUrl = rtrim(BASE_URL, '/') . '/auth/login.html?role=customer&next=' .
                urlencode(rtrim(BASE_URL, '/') . '/customer/browse-cars.php');
    redirect_and_exit($loginUrl);
}

$userId      = (int) $_SESSION['user_id'];
$carRegNo    = strtoupper(trim($_POST['car_reg_no'] ?? ''));
$city        = trim($_POST['city'] ?? '');
$location    = trim($_POST['location'] ?? '');
$pickupRaw   = trim($_POST['pickup_datetime'] ?? '');
$dropRaw     = trim($_POST['drop_datetime'] ?? '');

$errors = [];
if ($carRegNo === '') $errors[] = 'Car registration number is missing.';
if ($city === '')     $errors[] = 'City is required.';
if ($location === '') $errors[] = 'Location is required.';
if ($pickupRaw === '' || $dropRaw === '') $errors[] = 'Pickup and Drop-off date & time are required.';

if (!empty($errors)) {
    $_SESSION['book_error'] = implode(' ', $errors);
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}

// Parse date-times
$pickupDt = date_create($pickupRaw);
$dropDt   = date_create($dropRaw);

if (!$pickupDt || !$dropDt) {
    $_SESSION['book_error'] = 'Invalid date/time format.';
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}

if ($pickupDt >= $dropDt) {
    $_SESSION['book_error'] = 'Pickup must be before drop-off.';
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}

// Calculate hours
$seconds = $dropDt->getTimestamp() - $pickupDt->getTimestamp();
$hours   = max(1, (int) ceil($seconds / 3600.0));

$mysqli = get_db();

// Get rate per hour from cars table
$stmt = $mysqli->prepare("SELECT price_per_hour, car_name FROM cars WHERE reg_no = ?");
if (!$stmt) {
    error_log("book.php prepare error: " . $mysqli->error);
    $_SESSION['book_error'] = 'Server error (prepare).';
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}
$stmt->bind_param('s', $carRegNo);
$stmt->execute();
$stmt->bind_result($pricePerHour, $carName);
if (!$stmt->fetch()) {
    $stmt->close();
    $_SESSION['book_error'] = 'Selected car not found. Please choose a valid car.';
    redirect_and_exit(rtrim(BASE_URL, '/') . '/customer/browse-cars.php');
}
$stmt->close();

$pricePerHour = (float) $pricePerHour;
$totalAmount  = round($pricePerHour * $hours, 2);

$pickupSql = $pickupDt->format('Y-m-d H:i:s');
$dropSql   = $dropDt->format('Y-m-d H:i:s');

// Insert booking with status = 'Pending'
$insert = $mysqli->prepare("
    INSERT INTO bookings
      (user_id, car_reg_no, city, location, pickup_datetime, drop_datetime,
       rate_per_hour, total_amount, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
");
if (!$insert) {
    error_log("book.php insert prepare error: " . $mysqli->error);
    $_SESSION['book_error'] = 'Server error (insert prepare).';
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}

$insert->bind_param(
    'isssssdd',
    $userId, $carRegNo, $city, $location, $pickupSql, $dropSql,
    $pricePerHour, $totalAmount
);

if ($insert->execute()) {
    $bookingId = $insert->insert_id;
    $insert->close();
    $_SESSION['book_success'] = 'Booking created successfully and is now Pending.';
    redirect_and_exit(rtrim(BASE_URL, '/') . '/customer/my-bookings.php');
} else {
    error_log("book.php execute error: " . $insert->error);
    $insert->close();
    $_SESSION['book_error'] = 'Could not create booking.';
    $back = rtrim(BASE_URL, '/') . '/customer/book.php?car=' . urlencode($carRegNo);
    redirect_and_exit($back);
}
?>