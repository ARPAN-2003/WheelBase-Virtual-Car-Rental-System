<?php
// customer/my-bookings.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Require customer login
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login.html?role=customer');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$mysqli = get_db();

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
    c.car_name,
    c.reg_no AS car_reg_no
  FROM bookings b
  JOIN cars c ON b.car_reg_no = c.reg_no
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC
";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die('DB error: ' . $mysqli->error);
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

function format_booking_code($id, $createdAt) {
    $year = date('Y', strtotime($createdAt));
    return 'WB-' . $year . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Bookings – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo"><a href="/WheelBase/index.php">Wheel<span>Base</span></a></div>
      <nav class="nav-links">
        <a href="/WheelBase/index.php">Home</a>
        <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
        <a href="/WheelBase/customer/browse-cars.php">Browse Cars</a>
        <a href="/WheelBase/customer/my-bookings.php" class="active">My Bookings</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section>
      <h1>My Bookings</h1>
      <p class="small-text">
        Status is initially <strong>"Pending"</strong> and later <strong>"Accepted"</strong> or
        <strong>"Cancelled"</strong> by Admin.
      </p>

      <?php if (!empty($_SESSION['book_success'])): ?>
        <div class="alert success">
          <?php
            echo htmlspecialchars($_SESSION['book_success']);
            unset($_SESSION['book_success']);
          ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['book_error'])): ?>
        <div class="alert">
          <?php
            echo htmlspecialchars($_SESSION['book_error']);
            unset($_SESSION['book_error']);
          ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Car</th>
              <th>Trip Location</th>
              <th>Pickup</th>
              <th>Drop-off</th>
              <th>Rate/hour (₹)</th>
              <th>Total (₹)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($result->num_rows === 0): ?>
            <tr>
              <td colspan="8" style="text-align:center;">No bookings yet.</td>
            </tr>
          <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
                $bookingCode = format_booking_code($row['id'], $row['created_at']);
                $pickupDate  = date('d-m-Y H:i', strtotime($row['pickup_datetime']));
                $dropDate    = date('d-m-Y H:i', strtotime($row['drop_datetime']));
                $tripText    = $row['city'] . ' — ' . $row['location'];
                $status      = $row['status'];

                // choose CSS class based on status (for colored pill)
                $statusClass = 'status-pill';
                if ($status === 'Pending')   $statusClass .= ' status-pending';
                if ($status === 'Accepted')  $statusClass .= ' status-accepted';
                if ($status === 'Cancelled') $statusClass .= ' status-cancelled';
              ?>
              <tr>
                <td><?php echo htmlspecialchars($bookingCode); ?></td>
                <td>
                  <?php echo htmlspecialchars($row['car_name']); ?><br/>
                  <span class="small-text">(<?php echo htmlspecialchars($row['car_reg_no']); ?>)</span>
                </td>
                <td><?php echo htmlspecialchars($tripText); ?></td>
                <td><?php echo htmlspecialchars($pickupDate); ?></td>
                <td><?php echo htmlspecialchars($dropDate); ?></td>
                <td><?php echo number_format((float)$row['rate_per_hour'], 2); ?></td>
                <td><?php echo number_format((float)$row['total_amount'], 2); ?></td>
                <td><span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
