<?php
// retailer/dashboard.php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// ✅ AUTH CHECK: only retailer allowed
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'retailer') {
    header(
        'Location: ' . rtrim(BASE_URL, '/') .
        '/auth/login.html?role=retailer&next=' .
        urlencode('/WheelBase/retailer/dashboard.php')
    );
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Retailer Dashboard – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />

  <script>
    window.USER_ROLE = "retailer";
    window.USERNAME = <?= json_encode($_SESSION['username']); ?>;
  </script>
</head>

<body>
<header>
  <div class="container navbar">
    <div class="logo">
      <a href="/WheelBase/index.php">Wheel<span>Base</span></a>
    </div>
    <nav class="nav-links">
      <a href="/WheelBase/retailer/dashboard.php" class="active">Dashboard</a>
      <a href="/WheelBase/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="admin-layout">

  <!-- ================= SIDEBAR ================= -->
  <aside class="admin-sidebar">
    <h2>Retailer Panel</h2>
    <div class="admin-menu">
      <button class="active" onclick="showSection('addCar', this)">Add / Remove Car</button>
    </div>
  </aside>

  <!-- ================= CONTENT ================= -->
  <section class="admin-content">

    <!-- ===== ADD CAR ===== -->
    <div id="addCar" class="section active">
      <h2>Add Car</h2>
      <p class="small-text">Fill all details including car image.</p>

      <?php if (!empty($_SESSION['car_msg'])): ?>
        <div class="alert <?= $_SESSION['car_msg_type']; ?>">
          <?= htmlspecialchars($_SESSION['car_msg']); ?>
        </div>
        <?php unset($_SESSION['car_msg'], $_SESSION['car_msg_type']); ?>
      <?php endif; ?>

      <form method="post"
            action="/WheelBase/retailer_add_car.php"
            enctype="multipart/form-data">

        <div class="form-group">
          <label>Car Registration Number</label>
          <input type="text" name="reg_no" required />
        </div>

        <div class="form-group">
          <label>Car Name</label>
          <input type="text" name="car_name" required />
        </div>

        <div class="form-group">
          <label>Brand Name</label>
          <input type="text" name="brand_name" required />
        </div>

        <div class="form-group">
          <label>Seating Capacity</label>
          <input type="number" name="capacity" min="1" required />
        </div>

        <div class="form-group">
          <label>Rental Price (per hour)</label>
          <input type="number" name="price_per_hour" min="0" required />
        </div>

        <div class="form-group">
          <label>Car Image</label>
          <input type="file" name="car_image" accept="image/*" required />
        </div>

        <button class="btn btn-primary" type="submit">Add Car</button>
      </form>

      <hr style="margin:30px 0">

      <!-- ===== REMOVE CAR ===== -->
      <h2>Remove Car</h2>
      <p class="small-text">Enter registration number.</p>

      <form method="post" action="/WheelBase/retailer_remove_car.php">
        <div class="form-group">
          <label>Car Registration Number</label>
          <input type="text" name="reg_no" required />
        </div>
        <button class="btn btn-danger" type="submit">Remove Car</button>
      </form>

    </div>
  </section>
</main>

<script>
function showSection(id, btn) {
  document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
  document.getElementById(id).style.display = 'block';
  document.querySelectorAll('.admin-menu button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>

<script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>
