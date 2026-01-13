<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL);
    exit;
}

$reg = $_POST['reg_no'] ?? '';
if (!$reg) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$db = get_db();

$stmt = $db->prepare("
    UPDATE cars
    SET status = IF(status='available','unavailable','available')
    WHERE reg_no = ?
");
$stmt->bind_param('s', $reg);
$stmt->execute();

header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;
