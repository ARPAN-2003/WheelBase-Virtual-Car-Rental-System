<?php
    // customer/dashboard.php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Dashboard – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script>
    // expose session role to client JS
    window.USER_ROLE = <?php echo isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;
    window.USERNAME = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
  </script>
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
      <nav class="nav-links" id="mainNav">
        <a href="/WheelBase/index.php">Home</a>
        <a href="/WheelBase/customer/dashboard.php" class="active">Dashboard</a>
        <a href="/WheelBase/browse-cars.php">Browse Cars</a>
        <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container dashboard-main">
    <div class="dashboard-header">
      <h1>Welcome<?php echo isset($_SESSION['username']) ?(', '.htmlspecialchars($_SESSION['username'])) : ', Guest'; ?></h1>
      <span class="badge">Role: <?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Guest'; ?></span>
    </div>

    <section>
      <div class="tab-row">
        <div class="card">
          <div class="card-title">Start a new trip</div>
          <div class="card-text">Browse available cars and book your next ride.</div>
          <br />
          <a href="/WheelBase/browse-cars.php" class="btn btn-primary">Browse Cars</a>
        </div>

        <div class="card">
          <div class="card-title">My bookings</div>
          <div class="card-text">View current and past bookings, including status.</div>
          <br />
          <a href="/WheelBase/customer/my-bookings.php" class="btn btn-primary">View Bookings</a>
        </div>

        <div class="card">
          <div class="card-title">Edit profile</div>
          <div class="card-text">Update your name, Aadhaar, email and password.</div>
          <br />
          <a href="/WheelBase/customer/edit-profile.php" class="btn btn-primary">Edit profile</a>
        </div>
      </div>
    </section>
  </main>

  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>