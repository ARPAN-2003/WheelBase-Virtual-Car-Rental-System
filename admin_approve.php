<?php
// admin_approve.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function redirect_admin() {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_admin();
}

// Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['admin_msg'] = 'Unauthorized access.';
    redirect_admin();
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if ($bookingId <= 0 || !in_array($action, ['accept', 'cancel'], true)) {
    $_SESSION['admin_msg'] = 'Invalid request.';
    redirect_admin();
}

$db = get_db();

try {
    // Start transaction
    $db->begin_transaction();

    // Lock booking row
    $stmt = $db->prepare(
        "SELECT status FROM bookings WHERE id = ? FOR UPDATE"
    );
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->bind_result($currentStatus);

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->rollback();
        $_SESSION['admin_msg'] = 'Booking not found.';
        redirect_admin();
    }
    $stmt->close();

    // Only Pending bookings can be changed
    if ($currentStatus !== 'Pending') {
        $db->rollback();
        $_SESSION['admin_msg'] = "Booking already {$currentStatus}. No further changes allowed.";
        redirect_admin();
    }

    // Decide new status
    $newStatus = ($action === 'accept') ? 'Accepted' : 'Cancelled';

    // Update booking
    $update = $db->prepare(
        "UPDATE bookings 
         SET status = ? 
         WHERE id = ? AND status = 'Pending'"
    );
    $update->bind_param('si', $newStatus, $bookingId);
    $update->execute();

    if ($update->affected_rows !== 1) {
        $update->close();
        $db->rollback();
        $_SESSION['admin_msg'] = 'Booking update failed.';
        redirect_admin();
    }

    $update->close();
    $db->commit();

    $_SESSION['admin_msg'] = "Booking successfully {$newStatus}.";
    redirect_admin();

} catch (Exception $e) {
    error_log('Admin approve error: ' . $e->getMessage());
    $db->rollback();
    $_SESSION['admin_msg'] = 'Server error while updating booking.';
    redirect_admin();
}
