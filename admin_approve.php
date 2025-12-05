<?php
// admin_approve.php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function redirect_admin() {
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_admin();
}

// Must be admin
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
if (!$isAdmin) {
    $_SESSION['admin_msg'] = 'You must be an admin to perform this action.';
    redirect_admin();
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
$action    = $_POST['action'] ?? ''; // 'accept' or 'cancel'

if ($bookingId <= 0 || !in_array($action, ['accept', 'cancel'], true)) {
    $_SESSION['admin_msg'] = 'Invalid request parameters.';
    redirect_admin();
}

$mysqli = get_db();

try {
    $mysqli->begin_transaction();

    // Lock the row
    $sel = $mysqli->prepare("SELECT status FROM bookings WHERE id = ? FOR UPDATE");
    if (!$sel) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    $sel->bind_param('i', $bookingId);
    $sel->execute();
    $sel->bind_result($curStatus);
    if (!$sel->fetch()) {
        $sel->close();
        $mysqli->rollback();
        $_SESSION['admin_msg'] = 'Booking not found.';
        redirect_admin();
    }
    $sel->close();

    if ($curStatus !== 'Pending') {
        $mysqli->rollback();
        $_SESSION['admin_msg'] = 'Booking already ' . htmlspecialchars($curStatus) . ' and cannot be changed.';
        redirect_admin();
    }

    $newStatus = ($action === 'accept') ? 'Accepted' : 'Cancelled';
    $upd = $mysqli->prepare("UPDATE bookings SET status = ? WHERE id = ? AND status = 'Pending'");
    if (!$upd) {
        throw new Exception("Update prepare failed: " . $mysqli->error);
    }
    $upd->bind_param('si', $newStatus, $bookingId);
    $upd->execute();

    if ($upd->affected_rows > 0) {
        $upd->close();
        $mysqli->commit();
        $_SESSION['admin_msg'] = 'Booking updated to ' . $newStatus . '.';
        redirect_admin();
    } else {
        $upd->close();
        $mysqli->rollback();
        $_SESSION['admin_msg'] = 'Failed to update booking (maybe modified already).';
        redirect_admin();
    }
} catch (Exception $e) {
    error_log("admin_approve.php error: " . $e->getMessage());
    if ($mysqli->in_transaction) $mysqli->rollback();
    $_SESSION['admin_msg'] = 'Server error while updating booking.';
    redirect_admin();
}
?>
