<?php
require 'database.php';  // Make sure database.php connects $conn to MySQL

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST values safely
    $delivery_id  = $_POST['delivery_id']  ?? null;
    $message      = trim($_POST['message'] ?? '');
    $sender       = $_POST['sender']       ?? '';
    $sender_name  = $_POST['sender_name']  ?? '';

    // Validate input
    if (empty($delivery_id) || empty($message) || empty($sender) || empty($sender_name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit();
    }

    // Check current delivery status
    $status_check = $conn->prepare("SELECT delivery_status FROM deliveries WHERE id = ?");
    $status_check->bind_param("i", $delivery_id);
    $status_check->execute();
    $result = $status_check->get_result();
    $status_row = $result->fetch_assoc();
    $status_check->close();

    if (!$status_row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Delivery not found.']);
        exit();
    }

    $status = $status_row['delivery_status'];

    // Only allow sending if delivery is "In Transit"
    if ($status === 'In Transit') {
        $stmt = $conn->prepare("
            INSERT INTO chat_messages (delivery_id, sender, sender_name, message, timestamp)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isss", $delivery_id, $sender, $sender_name, $message);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database insert failed.']);
        }

        $stmt->close();
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Chat is only allowed while delivery is In Transit.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}


?>
