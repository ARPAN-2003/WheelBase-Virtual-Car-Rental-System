<?php
    // retailer/dashboard.php
    session_start();
    // (Optionally) redirect non-logged-in users to login:
    // if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'retailer') {
    //   header('Location: /WheelBase/auth/login.html?role=retailer&next=/WheelBase/retailer/dashboard.php');
    //   exit;
    // }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Retailer Dashboard – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script>
    // expose session to client JS for logic (modal, blocking booking etc)
    window.USER_ROLE = <?php echo isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;
    window.USERNAME = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
  </script>
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
      <nav class="nav-links">
        <a href="/WheelBase/retailer/dashboard.php" class="active">Dashboard</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="admin-layout">
    <aside class="admin-sidebar">
      <h2>Retailer Panel</h2>
      <div class="admin-menu">
        <button class="active" onclick="showAdminSection('r-add', this)">Add / Remove Car</button>
      </div>
    </aside>

    <section class="admin-content">
      <div id="r-add" class="section active">
        <h2>Add Car</h2>
        <p class="small-text">Enter details of the car you want to add.</p>

        <!-- Later: action should point to add_car.php (server) -->
        <form method="post" action="/WheelBase/add_car.php">
          <div class="form-group">
            <label for="regNo">Car Registration Number</label>
            <input id="regNo" name="reg_no" type="text" required />
          </div>

          <div class="form-group">
            <label for="carName">Car Name</label>
            <input id="carName" name="car_name" type="text" required />
          </div>

          <div class="form-group">
            <label for="brandName">Car Brand Name</label>
            <input id="brandName" name="brand_name" type="text" required />
          </div>

          <div class="form-group">
            <label for="capacity">Seating Capacity</label>
            <input id="capacity" name="capacity" type="number" min="1" required />
          </div>

          <div class="form-group">
            <label for="pricePerHour">Rental Price (per hour)</label>
            <input id="pricePerHour" name="price_per_hour" type="number" min="0" required />
          </div>

          <button class="btn btn-primary" type="submit">Add Car</button>
        </form>

        <br />

        <h2>Remove Car</h2>
        <p class="small-text">Enter registration number of the car you want to remove.</p>

        <!-- Later: action should point to remove_car.php -->
        <form method="post" action="/WheelBase/remove_car.php">
          <div class="form-group">
            <label for="regRemove">Car Registration Number</label>
            <input id="regRemove" name="reg_no" type="text" required />
          </div>
          <button class="btn btn-primary" type="submit">Remove Car</button>
        </form>
      </div>
    </section>
  </main>

  <script>
    function showAdminSection(id, btn) {
      document.querySelectorAll('.admin-content .section').forEach(s => s.style.display = 'none');
      document.getElementById(id).style.display = 'block';
      document.querySelectorAll('.admin-menu button').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
    }
    // keep first visible
    (function(){ showAdminSection('r-add', document.querySelector('.admin-menu button')); })();
  </script>

  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>