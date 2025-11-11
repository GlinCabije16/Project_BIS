<?php
require 'database.php';

header('Content-Type: application/json');

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit;
}

$id = intval($data['id']);

// Fetch plain password
$stmt = $conn->prepare("SELECT plain_password FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

$row = $result->fetch_assoc();
echo json_encode([
    'success' => true,
    'plain_password' => $row['plain_password']
]);
