<?php
require 'database.php';

$delivery_id = $_GET['delivery_id'];
$stmt = $conn->prepare("SELECT * FROM chat_messages WHERE delivery_id = ? ORDER BY timestamp ASC");
$stmt->bind_param("i", $delivery_id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>
