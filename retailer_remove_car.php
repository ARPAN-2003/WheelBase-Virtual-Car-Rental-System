<?php
// retailer_remove_car.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'retailer') {
    header('Location: ' . BASE_URL . 'auth/login.html?role=retailer');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'retailer/dashboard.php');
    exit;
}

$reg_no = trim($_POST['reg_no'] ?? '');
$owner_username = $_SESSION['username'];

if (!$reg_no) {
    die("Car registration number required");
}

$db = get_db();

$stmt = $db->prepare("
    DELETE FROM cars 
    WHERE reg_no = ? AND owner_username = ?
");

$stmt->bind_param("ss", $reg_no, $owner_username);
$stmt->execute();

$stmt->close();
$db->close();

header('Location: ' . BASE_URL . 'retailer/dashboard.php');
exit;
