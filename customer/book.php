<?php
// customer/book.php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

/* ---------- ROLE CHECK ---------- */
if (!isset($_SESSION['role'])) {
    $next = '/WheelBase/customer/book.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
    header("Location: /WheelBase/auth/login.html?role=customer&next=" . urlencode($next));
    exit;
}

$blocked = ($_SESSION['role'] !== 'customer');

/* ---------- READ PARAMS ---------- */
$regNo  = $_GET['reg_no'] ?? '';

/* ---------- FETCH CAR ---------- */
$db = get_db();
$stmt = $db->prepare("SELECT * FROM cars WHERE reg_no = ?");
$stmt->bind_param("s", $regNo);
$stmt->execute();
$res = $stmt->get_result();
$car = $res->fetch_assoc();
$stmt->close();

if (!$car) {
    die("<h2 style='padding:20px'>Invalid car selected.</h2>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Car – WheelBase</title>
<link rel="stylesheet" href="/WheelBase/assets/css/style.css">
<meta name="viewport" content="width=device-width,initial-scale=1">

<script>
/* SAME CITY–LOCATION MAP AS index.php */
const cityLocations = {
  "Kolkata": ["Salt Lake", "Garia", "Howrah", "Ballygunge", "Dumdum"],
  "New Delhi": ["Connaught Place", "Karol Bagh", "Chanakyapuri", "Saket", "Dwarka"],
  "Bengaluru": ["MG Road", "Koramangala", "Whitefield", "Indiranagar", "BTM Layout"],
  "Mumbai": ["Andheri", "Bandra", "Colaba", "Juhu", "BKC"],
  "Chennai": ["T Nagar", "Adyar", "Velachery", "Anna Nagar", "Chromepet"]
};
</script>
</head>

<body>

<header>
<div class="container navbar">
  <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
  <nav class="nav-links">
    <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
    <a href="/WheelBase/browse-cars.php">Browse Cars</a>
    <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
    <a href="/WheelBase/logout.php">Logout</a>
  </nav>
</div>
</header>

<main class="container dashboard-main">

<div class="dashboard-header">
  <h1>Book Car</h1>
  <p class="small-text">Confirm trip details. Booking status will be <b>Pending</b>.</p>
</div>

<div class="table-wrapper">

<form method="post" action="/WheelBase/customer/invoice.php">

  <div class="form-group">
    <label>Car</label>
    <input type="text" value="<?= htmlspecialchars($car['car_name']) ?> (<?= htmlspecialchars($car['reg_no']) ?>)" readonly>
  </div>

  <div class="form-group">
    <label>Brand</label>
    <input type="text" value="<?= htmlspecialchars($car['brand_name']) ?>" readonly>
  </div>

  <div class="form-group">
    <label>Rate / hour</label>
    <input type="text" value="₹<?= number_format($car['price_per_hour'],2) ?>" readonly>
  </div>

  <!-- CITY -->
  <div class="form-group">
    <label>City</label>
    <select name="city" id="citySelect" required>
      <option value="">Select City</option>
    </select>
  </div>

  <!-- LOCATION -->
  <div class="form-group">
    <label>Location</label>
    <select name="location" id="locationSelect" required>
      <option value="">Select Location</option>
    </select>
  </div>

  <div class="form-group">
    <label>Pickup Date & Time</label>
    <input type="datetime-local" name="pickup_datetime" required>
  </div>

  <div class="form-group">
    <label>Drop-off Date & Time</label>
    <input type="datetime-local" name="drop_datetime" required>
  </div>

  <input type="hidden" name="car_reg_no" value="<?= htmlspecialchars($car['reg_no']) ?>">

  <button class="btn btn-primary" type="submit" <?= $blocked ? 'disabled' : '' ?>>
    Confirm Booking
  </button>

</form>
</div>

<?php if ($blocked): ?>
<div style="position:fixed; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center;">
  <div style="background:#fff; padding:22px; border-radius:8px; text-align:center;">
    <h3>Cannot Book</h3>
    <p>You are logged in as <b><?= htmlspecialchars($_SESSION['role']) ?></b>.<br>Login as customer to book.</p>
    <a class="btn btn-primary" href="/WheelBase/auth/login.html?role=customer">Login as Customer</a>
  </div>
</div>
<?php endif; ?>

</main>

<script>
/* Populate city dropdown */
const citySelect = document.getElementById('citySelect');
const locationSelect = document.getElementById('locationSelect');

Object.keys(cityLocations).forEach(city => {
  citySelect.add(new Option(city, city));
});

/* Populate locations */
citySelect.addEventListener('change', function () {
  locationSelect.innerHTML = '<option value="">Select Location</option>';
  (cityLocations[this.value] || []).forEach(loc => {
    locationSelect.add(new Option(loc, loc));
  });
});
</script>

<script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>
