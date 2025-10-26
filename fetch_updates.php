<?php
include "db.php";
session_start();
if(!isset($_SESSION['business_id'])){
    http_response_code(403);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$business_id = $_SESSION['business_id'];

// Fetch upcoming appointments
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.business_id=? ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute([$business_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reminder logs
$stmt = $pdo->prepare("SELECT l.*, a.customer_name, a.appointment_date, a.appointment_time 
                       FROM reminder_logs l
                       LEFT JOIN appointments a ON l.appointment_id = a.id
                       WHERE l.business_id=? ORDER BY l.sent_at DESC");
$stmt->execute([$business_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'appointments' => $appointments,
    'logs' => $logs
]);
