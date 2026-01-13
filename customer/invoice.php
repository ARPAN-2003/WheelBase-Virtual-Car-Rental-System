<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['role'] ?? '') !== 'customer') {
    header('Location: ' . BASE_URL . 'auth/login.html?role=customer');
    exit;
}

$db = get_db();

/* ---------- INPUT ---------- */
$regNo     = $_POST['car_reg_no'];
$city      = $_POST['city'];
$location  = $_POST['location'];
$pickup    = $_POST['pickup_datetime'];
$drop      = $_POST['drop_datetime'];

/* ---------- FETCH CAR ---------- */
$stmt = $db->prepare("SELECT * FROM cars WHERE reg_no = ?");
$stmt->bind_param("s", $regNo);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$car) die("Invalid car");

/* ---------- CALCULATION ---------- */
$pickupTS = strtotime($pickup);
$dropTS   = strtotime($drop);
$hours    = ceil(($dropTS - $pickupTS) / 3600);

$baseAmount = $hours * $car['price_per_hour'];
$cgst = $baseAmount * 0.09;
$sgst = $baseAmount * 0.09;
$netTotal = $baseAmount + $cgst + $sgst;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice – WheelBase</title>
<link rel="stylesheet" href="/WheelBase/assets/css/style.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<header>
  <div class="container navbar">
    <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
  </div>
</header>

<main class="container dashboard-main">

<h1>Invoice</h1>

<div class="card" style="max-width:700px;margin:auto;padding:20px;">

<p><b>Car:</b> <?= htmlspecialchars($car['car_name']) ?> (<?= $car['reg_no'] ?>)</p>
<p><b>Brand:</b> <?= htmlspecialchars($car['brand_name']) ?></p>
<p><b>City:</b> <?= htmlspecialchars($city) ?></p>
<p><b>Location:</b> <?= htmlspecialchars($location) ?></p>
<p><b>Pickup:</b> <?= date('d-m-Y H:i', $pickupTS) ?></p>
<p><b>Drop:</b> <?= date('d-m-Y H:i', $dropTS) ?></p>

<hr>

<p><b>Rental (<?= $hours ?> hrs):</b> ₹<?= number_format($baseAmount,2) ?></p>
<p>CGST (9%): ₹<?= number_format($cgst,2) ?></p>
<p>SGST (9%): ₹<?= number_format($sgst,2) ?></p>

<h3>Net Total: ₹<?= number_format($netTotal,2) ?></h3>

<form method="post" action="/WheelBase/customer/pay.php">
<input type="hidden" name="car_reg_no" value="<?= $regNo ?>">
<input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">
<input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">
<input type="hidden" name="pickup_datetime" value="<?= $pickup ?>">
<input type="hidden" name="drop_datetime" value="<?= $drop ?>">
<input type="hidden" name="total_amount" value="<?= $netTotal ?>">

<button class="btn btn-primary">Pay Now</button>
</form>

</div>
</main>
</body>
</html>
