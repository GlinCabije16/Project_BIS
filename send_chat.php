<?php
require 'database.php';

$delivery_id = $_POST['delivery_id'];
$message = $_POST['message'];
$sender = $_POST['sender'];
$sender_name = $_POST['sender_name'];

$status_check = $conn->prepare("SELECT delivery_status FROM deliveries WHERE id=?");
$status_check->bind_param("i", $delivery_id);
$status_check->execute();
$status = $status_check->get_result()->fetch_assoc()['delivery_status'];

if ($status === 'In Transit') {
    $stmt = $conn->prepare("INSERT INTO chat_messages (delivery_id, sender, sender_name, message, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $delivery_id, $sender, $sender_name, $message);
    $stmt->execute();
    $stmt->close();
}

?>
