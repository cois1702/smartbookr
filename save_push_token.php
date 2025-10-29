<?php
// save_push_token.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['expo_token'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$user_id = intval($data['user_id']);
$expo_token = $data['expo_token'];

// Example: save token to database (MySQL)
$mysqli = new mysqli('localhost', 'db_user', 'db_pass', 'smartbookr_db');

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => $mysqli->connect_error]);
    exit;
}

// Insert or update token
$stmt = $mysqli->prepare("INSERT INTO user_push_tokens (user_id, expo_token) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE expo_token = VALUES(expo_token)");
$stmt->bind_param('is', $user_id, $expo_token);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Token saved']);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
