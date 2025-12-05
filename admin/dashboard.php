<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Require admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html?role=admin');
    exit;
}

$mysqli = get_db();

// Fetch all bookings with user & car info
$sql = "
  SELECT 
    b.id,
    b.city,
    b.location,
    b.pickup_datetime,
    b.drop_datetime,
    b.rate_per_hour,
    b.total_amount,
    b.status,
    b.created_at,
    u.username AS customer_username,
    c.car_name,
    c.reg_no AS car_reg_no
  FROM bookings b
  JOIN users u ON b.user_id = u.id
  JOIN cars c ON b.car_reg_no = c.reg_no
  ORDER BY b.created_at DESC
";
$result = $mysqli->query($sql);
if (!$result) {
    die('DB error: ' . $mysqli->error);
}

function format_booking_code($id, $createdAt) {
    // e.g. WB-2025-001
    $year = date('Y', strtotime($createdAt));
    return 'WB-' . $year . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
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
    <aside class="admin-sidebar">
      <h2>Admin Panel</h2>
      <p class="small-text">Signed in as <strong>admin</strong></p>
      <div class="admin-menu">
        <button class="active" onclick="showAdminSection('approve', this)">Approve Booking</button>
        <button onclick="showAdminSection('cars', this)" disabled>Add / Remove Car</button>
        <button onclick="showAdminSection('users', this)" disabled>Check System Users</button>
        <button onclick="showAdminSection('revenue', this)" disabled>Check Revenue</button>
      </div>
    </aside>

    <section class="admin-content">
      <!-- Approve Booking section -->
      <div id="approve" class="section active">
        <h1>Approve Booking</h1>
        <p class="small-text">
          Admin can change booking status from Pending to Accepted or Cancelled. Once changed it cannot be modified again.
        </p>

        <?php if (!empty($_SESSION['admin_msg'])): ?>
          <div class="alert">
            <?php
              echo htmlspecialchars($_SESSION['admin_msg']);
              unset($_SESSION['admin_msg']);
            ?>
          </div>
        <?php endif; ?>

        <div class="card">
          <table class="table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Car</th>
                <th>Trip</th>
                <th>Pickup</th>
                <th>Drop-off</th>
                <th>Rate/hr (₹)</th>
                <th>Total (₹)</th>
                <th>Current Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
              <tr>
                <td colspan="10" style="text-align:center;">No bookings yet.</td>
              </tr>
            <?php else: ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                  $bookingCode = format_booking_code($row['id'], $row['created_at']);
                  $status      = $row['status']; // 'Pending', 'Accepted', 'Cancelled'
                  $isPending   = ($status === 'Pending');

                  $pickupDate = date('d-m-Y H:i', strtotime($row['pickup_datetime']));
                  $dropDate   = date('d-m-Y H:i', strtotime($row['drop_datetime']));
                  $tripText   = $row['city'] . ' — ' . $row['location'];
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($bookingCode); ?></td>
                  <td><?php echo htmlspecialchars($row['customer_username']); ?></td>
                  <td>
                    <?php echo htmlspecialchars($row['car_name']); ?><br/>
                    <span class="small-text">(<?php echo htmlspecialchars($row['car_reg_no']); ?>)</span>
                  </td>
                  <td><?php echo htmlspecialchars($tripText); ?></td>
                  <td><?php echo htmlspecialchars($pickupDate); ?></td>
                  <td><?php echo htmlspecialchars($dropDate); ?></td>
                  <td><?php echo number_format((float)$row['rate_per_hour'], 2); ?></td>
                  <td><?php echo number_format((float)$row['total_amount'], 2); ?></td>
                  <td><?php echo htmlspecialchars($status); ?></td>
                  <td>
                    <form method="post" action="/WheelBase/admin_approve.php">
                      <input type="hidden" name="booking_id" value="<?php echo (int)$row['id']; ?>">
                      <button type="submit" name="action" value="accept"
                        class="btn btn-primary"
                        <?php echo $isPending ? '' : 'disabled'; ?>>Accept</button>
                      <button type="submit" name="action" value="cancel"
                        class="btn btn-danger"
                        <?php echo $isPending ? '' : 'disabled'; ?>>Cancel</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Placeholder sections for future -->
      <div id="cars" class="section" style="display:none;">
        <h2>Add / Remove Car</h2>
        <p class="small-text">Static for now.</p>
      </div>

      <div id="users" class="section" style="display:none;">
        <h2>Check System Users</h2>
        <p class="small-text">Static for now.</p>
      </div>

      <div id="revenue" class="section" style="display:none;">
        <h2>Check Revenue</h2>
        <p class="small-text">Static for now.</p>
      </div>
    </section>
  </main>

  <script>
    function showAdminSection(id, btn) {
      document.querySelectorAll('.admin-content .section').forEach(s => {
        s.style.display = 'none';
      });
      var el = document.getElementById(id);
      if (el) el.style.display = 'block';
      document.querySelectorAll('.admin-menu button').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
    }
  </script>
</body>
</html>
