<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['role'] ?? '') !== 'customer') {
    header('Location: ' . BASE_URL);
    exit;
}

$db = get_db();

/* -------------------------
   1️⃣ Fetch car rate safely
-------------------------- */
$rateStmt = $db->prepare("
    SELECT price_per_hour
    FROM cars
    WHERE reg_no = ?
");
$rateStmt->bind_param("s", $_POST['car_reg_no']);
$rateStmt->execute();
$rateResult = $rateStmt->get_result();
$car = $rateResult->fetch_assoc();
$rateStmt->close();

if (!$car) {
    die("Invalid car selected.");
}

$ratePerHour = (float)$car['price_per_hour'];

/* -------------------------
   2️⃣ Insert booking (FIXED)
-------------------------- */
$stmt = $db->prepare("
INSERT INTO bookings 
(
  user_id,
  car_reg_no,
  city,
  location,
  pickup_datetime,
  drop_datetime,
  rate_per_hour,
  total_amount,
  status,
  created_at
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
");

$stmt->bind_param(
  "issssssd",
  $_SESSION['user_id'],
  $_POST['car_reg_no'],
  $_POST['city'],
  $_POST['location'],
  $_POST['pickup_datetime'],
  $_POST['drop_datetime'],
  $ratePerHour,
  $_POST['total_amount']   // ✅ net total from invoice
);

$stmt->execute();
$stmt->close();

/* -------------------------
   3️⃣ Redirect to bookings
-------------------------- */
header("Location: /WheelBase/customer/my-bookings.php");
exit;
