<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['role'] ?? '') !== 'customer') {
    header('Location: ' . BASE_URL . 'auth/login.html?role=customer');
    exit;
}

$db = get_db();
$userId = (int)$_SESSION['user_id'];

/* ✅ EXPLICIT COLUMN LIST + ALIASES (CRITICAL FIX) */
$stmt = $db->prepare("
  SELECT
    b.id            AS booking_id,
    b.city          AS city,
    b.location      AS location,
    b.pickup_datetime,
    b.drop_datetime,
    b.total_amount,
    b.status        AS booking_status,
    b.created_at    AS booking_created,
    c.car_name      AS car_name
  FROM bookings b
  JOIN cars c ON b.car_reg_no = c.reg_no
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

function bookingCode($id, $date) {
    if (!$id || !$date) return 'WB-NA';
    return 'WB-' . date('Y', strtotime($date)) . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings – WheelBase</title>
<link rel="stylesheet" href="/WheelBase/assets/css/style.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<header>
  <div class="container navbar">
    <div class="logo">
      <a href="/WheelBase/index.php">Wheel<span>Base</span></a>
    </div>
    <nav class="nav-links">
      <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
      <a href="/WheelBase/browse-cars.php">Browse Cars</a>
      <a href="/WheelBase/customer/my-bookings.php" class="active">My Bookings</a>
      <a href="/WheelBase/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container dashboard-main">

<h1>My Bookings</h1>

<table class="table">
<thead>
<tr>
  <th>ID</th>
  <th>Car</th>
  <th>Trip</th>
  <th>Pickup</th>
  <th>Drop</th>
  <th>Total</th>
  <th>Status</th>
</tr>
</thead>

<tbody>
<?php if ($result->num_rows === 0): ?>
<tr>
  <td colspan="7" style="text-align:center;">No bookings yet</td>
</tr>
<?php endif; ?>

<?php while ($b = $result->fetch_assoc()): ?>

<tr>
  <td><?= bookingCode($b['booking_id'], $b['booking_created']) ?></td>

  <td><?= htmlspecialchars($b['car_name']) ?></td>

  <td><?= htmlspecialchars($b['city'] . ' — ' . $b['location']) ?></td>

  <td><?= date('d-m-Y H:i', strtotime($b['pickup_datetime'])) ?></td>

  <td><?= date('d-m-Y H:i', strtotime($b['drop_datetime'])) ?></td>

  <td>₹<?= number_format((float)$b['total_amount'], 2) ?></td>

  <td>
    <span class="status-pill status-<?= strtolower($b['booking_status']) ?>">
      <?= htmlspecialchars($b['booking_status']) ?>
    </span>
  </td>
</tr>

<?php endwhile; ?>
</tbody>
</table>

</main>

<script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>
