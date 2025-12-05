<?php
    // customer/edit-profile.php
    session_start();

    // Simple protection: only customers (or logged-in users) should see this
    if (!isset($_SESSION['user_id'])) {
      header('Location: /WheelBase/auth/login.html?next=/WheelBase/customer/edit-profile.php');
      exit;
    }

    // optionally you can fetch existing profile data from DB using $_SESSION['user_id']
    // For now we keep placeholders; later we will fetch via DB and prefill
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Edit Profile — WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script>window.USER_ROLE = <?php echo isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;</script>
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo"><a href="/WheelBase/index.html">Wheel<span>Base</span></a></div>
      <nav class="nav-links">
        <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
        <a href="/WheelBase/customer/browse-cars.php">Browse Cars</a>
        <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container dashboard-main">
    <div class="dashboard-header">
      <h1>Edit profile</h1>
    </div>

    <div class="table-wrapper">
      <!-- TODO: implement update_profile.php server-side. For now it's a placeholder. -->
      <form method="post" action="/WheelBase/update_profile.php">
        <div class="form-group">
          <label for="name">Full name</label>
          <input id="name" name="name" type="text" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" required />
        </div>

        <div class="form-group">
          <label for="aad">Aadhaar</label>
          <input id="aad" name="aadhaar" type="text" value="" required maxlength="12" />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="" required />
        </div>

        <div class="form-group">
          <label for="pw">New password (leave blank to keep current)</label>
          <input id="pw" name="password" type="password" />
        </div>

        <button class="btn btn-primary" type="submit">Save changes</button>
      </form>
    </div>
  </main>
  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>