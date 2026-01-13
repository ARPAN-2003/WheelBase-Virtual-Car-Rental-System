<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';

// Admin only
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('Unauthorized access');
}

$db = get_db();

/* =======================
   REVENUE SUMMARY
======================= */
$summary = [
    'today'=>0,'week'=>0,'month'=>0,'year'=>0,'total'=>0
];

$res = $db->query("
    SELECT total_amount, created_at
    FROM bookings
    WHERE status='Accepted'
");

$today = date('Y-m-d');
$month = date('Y-m');
$year  = date('Y');

while ($r = $res->fetch_assoc()) {
    $amt = (float)$r['total_amount'];
    $d   = date('Y-m-d', strtotime($r['created_at']));
    $m   = date('Y-m', strtotime($r['created_at']));
    $y   = date('Y', strtotime($r['created_at']));

    $summary['total'] += $amt;
    if ($d === $today) $summary['today'] += $amt;
    if (strtotime($d) >= strtotime('-7 days')) $summary['week'] += $amt;
    if ($m === $month) $summary['month'] += $amt;
    if ($y === $year)  $summary['year'] += $amt;
}

/* =======================
   PER-CAR REVENUE
======================= */
$perCar = $db->query("
    SELECT 
        c.car_name, c.reg_no,
        COUNT(b.id) AS bookings,
        SUM(b.total_amount) AS revenue
    FROM bookings b
    JOIN cars c ON b.car_reg_no = c.reg_no
    WHERE b.status='Accepted'
    GROUP BY c.reg_no
    ORDER BY revenue DESC
");

/* =======================
   PER-RETAILER REVENUE
======================= */
$perRetailer = $db->query("
    SELECT 
        c.owner_username,
        COUNT(DISTINCT c.reg_no) AS cars_owned,
        IFNULL(SUM(b.total_amount),0) AS revenue
    FROM cars c
    LEFT JOIN bookings b 
      ON c.reg_no = b.car_reg_no AND b.status='Accepted'
    GROUP BY c.owner_username
    ORDER BY revenue DESC
");

/* =======================
   PDF GENERATION
======================= */
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('WheelBase');
$pdf->SetAuthor('WheelBase Admin');
$pdf->SetTitle('Revenue Report');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

/* =======================
   HEADER
======================= */
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, 'WheelBase – Revenue Report', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('d M Y, h:i A'), 0, 1, 'C');

$pdf->Ln(5);

/* =======================
   SUMMARY
======================= */
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Revenue Summary', 0, 1);

$pdf->SetFont('helvetica', '', 11);
foreach ($summary as $k => $v) {
    $label = ucfirst($k);
    $pdf->Cell(80, 8, $label, 1);
    $pdf->Cell(0, 8, '₹ ' . number_format($v, 2), 1, 1);
}

$pdf->Ln(6);

/* =======================
   PER CAR
======================= */
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Per-Car Revenue', 0, 1);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(70, 8, 'Car', 1);
$pdf->Cell(40, 8, 'Bookings', 1);
$pdf->Cell(0, 8, 'Revenue (₹)', 1, 1);

$pdf->SetFont('helvetica', '', 11);
while ($c = $perCar->fetch_assoc()) {
    $pdf->Cell(70, 8, $c['car_name'].' ('.$c['reg_no'].')', 1);
    $pdf->Cell(40, 8, $c['bookings'], 1);
    $pdf->Cell(0, 8, number_format($c['revenue'], 2), 1, 1);
}

$pdf->Ln(6);

/* =======================
   PER RETAILER
======================= */
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Per-Retailer Revenue', 0, 1);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(70, 8, 'Retailer', 1);
$pdf->Cell(40, 8, 'Cars Owned', 1);
$pdf->Cell(0, 8, 'Revenue (₹)', 1, 1);

$pdf->SetFont('helvetica', '', 11);
while ($r = $perRetailer->fetch_assoc()) {
    $pdf->Cell(70, 8, $r['owner_username'], 1);
    $pdf->Cell(40, 8, $r['cars_owned'], 1);
    $pdf->Cell(0, 8, number_format($r['revenue'], 2), 1, 1);
}

/* =======================
   OUTPUT
======================= */
$pdf->Output('WheelBase_Revenue_Report.pdf', 'D');
exit;
