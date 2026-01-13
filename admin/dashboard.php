<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . 'auth/login.html?role=admin');
    exit;
}

$db = get_db();

/* ================= BOOKINGS ================= */
$bookings = $db->query("
    SELECT 
        b.id, b.city, b.location,
        b.pickup_datetime, b.drop_datetime,
        b.total_amount, b.status, b.created_at,
        u.username,
        c.car_name, c.reg_no
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_reg_no = c.reg_no
    ORDER BY b.created_at DESC
");

/* ================= CARS ================= */
$cars = $db->query("
    SELECT reg_no, car_name, brand_name, price_per_hour, owner_username, status
    FROM cars
    ORDER BY created_at DESC
");

/* ================= USERS ================= */
$users = $db->query("
    SELECT name, username, email, role, created_at
    FROM users
    ORDER BY created_at DESC
");

/* ================= REVENUE SUMMARY ================= */
$summary = ['today'=>0,'week'=>0,'month'=>0,'year'=>0,'total'=>0];

$revRows = $db->query("
    SELECT total_amount, created_at
    FROM bookings
    WHERE status='Accepted'
");

$today = date('Y-m-d');
$month = date('Y-m');
$year  = date('Y');

while ($r = $revRows->fetch_assoc()) {
    $amt = (float)$r['total_amount'];
    $d = date('Y-m-d', strtotime($r['created_at']));
    $m = date('Y-m', strtotime($r['created_at']));
    $y = date('Y', strtotime($r['created_at']));

    $summary['total'] += $amt;
    if ($d === $today) $summary['today'] += $amt;
    if (strtotime($d) >= strtotime('-7 days')) $summary['week'] += $amt;
    if ($m === $month) $summary['month'] += $amt;
    if ($y === $year)  $summary['year'] += $amt;
}

/* ================= PER-CAR REVENUE ================= */
$perCar = $db->query("
    SELECT 
        c.car_name, c.reg_no,
        COUNT(b.id) AS bookings,
        IFNULL(SUM(b.total_amount),0) AS revenue
    FROM cars c
    LEFT JOIN bookings b 
      ON c.reg_no = b.car_reg_no AND b.status='Accepted'
    GROUP BY c.reg_no
    ORDER BY revenue DESC
");

/* ================= PER-RETAILER REVENUE ================= */
$perRetailer = $db->query("
    SELECT 
        c.owner_username,
        COUNT(DISTINCT c.reg_no) AS cars_owned,
        IFNULL(SUM(b.total_amount),0) AS revenue
    FROM cars c
    LEFT JOIN bookings b 
      ON c.reg_no = b.car_reg_no AND b.status='Accepted'
    GROUP BY c.owner_username
    ORDER BY revenue DESC
");

function bookingCode($id,$dt){
    return 'WB-' . date('Y',strtotime($dt)) . '-' . str_pad($id,3,'0',STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard – WheelBase</title>
<link rel="stylesheet" href="/WheelBase/assets/css/style.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<header>
  <div class="container navbar">
    <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
    <nav class="nav-links">
      <a href="/WheelBase/index.php">Home</a>
      <a href="/WheelBase/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="admin-layout">

<!-- ================= SIDEBAR ================= -->
<aside class="admin-sidebar">
  <h2>Admin Panel</h2>
  <div class="admin-menu">
    <button class="active" onclick="showSection('approve',this)">Approve Bookings</button>
    <button onclick="showSection('cars',this)">Manage Cars</button>
    <button onclick="showSection('users',this)">Maintain System Users</button>
    <button onclick="showSection('revenue',this)">Check Revenue</button>
  </div>
</aside>

<!-- ================= CONTENT ================= -->
<section class="admin-content">

<!-- ===== APPROVE BOOKINGS ===== -->
<div id="approve" class="section active">
<h2>Approve Bookings</h2>

<table class="table">
<thead>
<tr>
<th>ID</th><th>User</th><th>Car</th><th>Trip</th>
<th>Pickup</th><th>Drop</th><th>Total</th><th>Status</th><th>Action</th>
</tr>
</thead>
<tbody>

<?php if ($bookings->num_rows === 0): ?>
<tr><td colspan="9" style="text-align:center;">No bookings yet</td></tr>
<?php endif; ?>

<?php while ($b = $bookings->fetch_assoc()): ?>
<tr>
<td><?= bookingCode($b['id'],$b['created_at']) ?></td>
<td><?= htmlspecialchars($b['username']) ?></td>
<td><?= htmlspecialchars($b['car_name']) ?><br><small><?= $b['reg_no'] ?></small></td>
<td><?= htmlspecialchars($b['city'].' — '.$b['location']) ?></td>
<td><?= date('d-m-Y H:i', strtotime($b['pickup_datetime'])) ?></td>
<td><?= date('d-m-Y H:i', strtotime($b['drop_datetime'])) ?></td>
<td>₹<?= number_format($b['total_amount'],2) ?></td>
<td><?= htmlspecialchars($b['status']) ?></td>
<td>
<?php if ($b['status']==='Pending'): ?>
<form method="post" action="/WheelBase/admin_approve.php">
<input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
<button name="action" value="accept" class="btn btn-primary">Accept</button>
<button name="action" value="cancel" class="btn btn-danger">Cancel</button>
</form>
<?php else: ?>
<button class="btn btn-outline" disabled><?= $b['status'] ?></button>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<!-- ===== CARS ===== -->
<div id="cars" class="section">
<h2>Cars in System</h2>

<table class="table">
<tr>
<th>Reg No</th><th>Name</th><th>Brand</th>
<th>Rate/hr</th><th>Owner</th><th>Status</th>
</tr>

<?php while ($c = $cars->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($c['reg_no']) ?></td>
<td><?= htmlspecialchars($c['car_name']) ?></td>
<td><?= htmlspecialchars($c['brand_name']) ?></td>
<td>₹<?= number_format($c['price_per_hour'],2) ?></td>
<td><?= htmlspecialchars($c['owner_username']) ?></td>
<td>
<form method="post" action="/WheelBase/admin/admin_toggle_car.php">
<input type="hidden" name="reg_no" value="<?= $c['reg_no'] ?>">
<button class="btn <?= $c['status']==='available'?'btn-primary':'btn-danger' ?>">
<?= ucfirst($c['status']) ?>
</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>

<!-- ===== USERS ===== -->
<div id="users" class="section">
<h2>System Users</h2>

<table class="table">
<tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th></tr>
<?php while ($u = $users->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= ucfirst($u['role']) ?></td>
<td><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- ===== REVENUE ===== -->
<div id="revenue" class="section">
<h2>Revenue Dashboard</h2>

<div class="card-grid">
  <div class="card"><h3>Today</h3><p class="big-text">₹<?= number_format($summary['today'],2) ?></p></div>
  <div class="card"><h3>Last 7 Days</h3><p class="big-text">₹<?= number_format($summary['week'],2) ?></p></div>
  <div class="card"><h3>This Month</h3><p class="big-text">₹<?= number_format($summary['month'],2) ?></p></div>
  <div class="card"><h3>This Year</h3><p class="big-text">₹<?= number_format($summary['year'],2) ?></p></div>
  <div class="card"><h3>Total Revenue</h3><p class="big-text">₹<?= number_format($summary['total'],2) ?></p></div>
</div>

<h3>Revenue per Car</h3>
<table class="table">
<tr><th>Car</th><th>Bookings</th><th>Revenue</th></tr>
<?php while ($c = $perCar->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($c['car_name'].' ('.$c['reg_no'].')') ?></td>
<td><?= $c['bookings'] ?></td>
<td>₹<?= number_format($c['revenue'],2) ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Revenue per Retailer</h3>
<table class="table">
<tr><th>Retailer</th><th>Cars Owned</th><th>Revenue</th></tr>
<?php while ($r = $perRetailer->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($r['owner_username']) ?></td>
<td><?= $r['cars_owned'] ?></td>
<td>₹<?= number_format($r['revenue'],2) ?></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="/WheelBase/admin/revenue_pdf.php" class="btn btn-primary">Export Revenue PDF</a>
</div>

</section>
</main>

<script>
function showSection(id, btn){
  document.querySelectorAll('.section').forEach(s=>s.style.display='none');
  document.getElementById(id).style.display='block';
  document.querySelectorAll('.admin-menu button').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
}
</script>

</body>
</html>
