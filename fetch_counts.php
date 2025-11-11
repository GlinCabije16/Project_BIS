<?php
require 'database.php';

// Counts
$data = [];

// Total requests
$r = $conn->query("SELECT COUNT(*) AS total FROM document_requests");
$data['requests'] = $r->fetch_assoc()['total'] ?? 0;

// Total complaints
$r = $conn->query("SELECT COUNT(*) AS total FROM complaints");
$data['complaints'] = $r->fetch_assoc()['total'] ?? 0;

// Total feedback
$r = $conn->query("SELECT COUNT(*) AS total FROM feedback");
$data['feedback'] = $r->fetch_assoc()['total'] ?? 0;

// Total contacts
$r = $conn->query("SELECT COUNT(*) AS total FROM contacts");
$data['contacts'] = $r->fetch_assoc()['total'] ?? 0;

// New notifications
$notif_sql = "SELECT * FROM notifications WHERE recipient_type='admin' AND is_read=0 ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($notif_sql);
$data['notifications'] = [];

if ($result && $result->num_rows > 0) {
  while ($n = $result->fetch_assoc()) {
    $data['notifications'][] = [
      'message' => $n['message'],
      'created_at' => $n['created_at']
    ];
  }
}

header('Content-Type: application/json');
echo json_encode($data);
