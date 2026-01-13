<?php
// customer/browse-cars.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// fetch cars from database
$db = get_db();
$result = $db->query("SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Browse Cars – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />

  <script>
    window.USER_ROLE = <?php echo isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;
    window.USERNAME  = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
  </script>
</head>

<body>
<header>
  <div class="container navbar">
    <div class="logo">
      <a href="/WheelBase/index.php">Wheel<span>Base</span></a>
    </div>
    <nav class="nav-links">
      <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
      <a href="/WheelBase/customer/browse-cars.php" class="active">Browse Cars</a>
      <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
      <a href="/WheelBase/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container dashboard-main">

  <div class="dashboard-header">
    <h1>Browse Cars</h1>
    <p class="small-text">Available cars from our system.</p>
  </div>

  <!-- Search summary -->
  <div id="searchSummary" style="margin-bottom:18px; display:none;">
    <div class="card" style="padding:12px;">
      <div><strong>Search:</strong> <span id="sumCity"></span> — <span id="sumLocation"></span></div>
      <div class="small-text">Pickup: <span id="sumPickup"></span> • Drop: <span id="sumDrop"></span></div>
    </div>
  </div>

  <!-- Cars grid -->
  <section class="card-grid" id="carsGrid">

    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($car = $result->fetch_assoc()): ?>
        <div class="card">

          <?php if (!empty($car['image_path'])): ?>
            <img src="/WheelBase/<?= htmlspecialchars($car['image_path']) ?>" alt="Car image">
          <?php else: ?>
            <img src="/WheelBase/img/default-car.png" alt="Car image">
          <?php endif; ?>

          <div class="card-title">
            <?= htmlspecialchars($car['reg_no']) ?> – <?= htmlspecialchars($car['car_name']) ?>
          </div>

          <div class="card-text">
            Brand: <?= htmlspecialchars($car['brand_name']) ?> •
            <?= (int)$car['capacity'] ?> seats •
            ₹<?= number_format($car['price_per_hour'], 2) ?> / hour
          </div>

          <br>

          <a
            href="/WheelBase/customer/book.php?reg_no=<?= urlencode($car['reg_no']) ?>"
            class="btn btn-primary book-btn"
            data-car="<?= htmlspecialchars($car['reg_no']) ?>"
          >
            Book now
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No cars available at the moment.</p>
    <?php endif; ?>

  </section>

  <!-- Modal for retailer/admin -->
  <div id="roleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:9999;">
    <div style="background:#fff; padding:22px; border-radius:8px; max-width:420px; width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.2); text-align:center;">
      <h3 style="margin-top:0;">Cannot Book</h3>
      <p id="roleModalMsg">You must login as a customer to book a car.</p>
      <div style="margin-top:18px; display:flex; gap:8px; justify-content:center;">
        <button id="roleModalClose" class="btn btn-outline">Close</button>
        <a id="roleModalLogin" class="btn btn-primary" href="/WheelBase/auth/login.html?role=customer">Login as Customer</a>
      </div>
    </div>
  </div>

</main>

<script>
(function () {

  // --------- Search summary ---------
  const params = new URLSearchParams(location.search);
  const city = params.get('city');
  const locationName = params.get('location');
  const pickup = params.get('pickup');
  const drop = params.get('drop');

  if (city && locationName && pickup && drop) {
    document.getElementById('sumCity').textContent = city;
    document.getElementById('sumLocation').textContent = locationName;
    document.getElementById('sumPickup').textContent = new Date(pickup).toLocaleString();
    document.getElementById('sumDrop').textContent = new Date(drop).toLocaleString();
    document.getElementById('searchSummary').style.display = 'block';
  }

  // --------- Booking logic ---------
  document.body.addEventListener('click', function (e) {
    const btn = e.target.closest('.book-btn');
    if (!btn) return;

    const role = window.USER_ROLE;
    const nextUrl = btn.getAttribute('href');

    if (!role) {
      e.preventDefault();
      window.location.href =
        '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(nextUrl);
      return;
    }

    if (role === 'retailer' || role === 'admin') {
      e.preventDefault();
      document.getElementById('roleModal').style.display = 'flex';
      document.getElementById('roleModalLogin').href =
        '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(nextUrl);
    }
  });

  document.getElementById('roleModalClose').onclick = function () {
    document.getElementById('roleModal').style.display = 'none';
  };

})();
</script>

<script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>
