<?php
// notify_all.php
require 'vendor/autoload.php'; // If using Expo SDK for PHP or your own cURL implementation

$mysqli = new mysqli('localhost', 'db_user', 'db_pass', 'smartbookr_db');

if ($mysqli->connect_error) {
    die('DB connection error');
}

// Get all Expo tokens
$result = $mysqli->query("SELECT expo_token FROM user_push_tokens");
$tokens = [];
while ($row = $result->fetch_assoc()) {
    $tokens[] = $row['expo_token'];
}

$mysqli->close();

// Send notification to all tokens
$messages = [];
foreach ($tokens as $token) {
    $messages[] = [
        'to' => $token,
        'sound' => 'default',
        'title' => 'New Booking!',
        'body' => 'A new booking has been made.',
    ];
}

$ch = curl_init('https://exp.host/--/api/v2/push/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));
$response = curl_exec($ch);
if(curl_errno($ch)){
    error_log('Error sending notifications: ' . curl_error($ch));
}
curl_close($ch);

echo json_encode(['success' => true, 'response' => $response]);
?>
