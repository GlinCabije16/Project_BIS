<?php
require 'database.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    die("Invalid request.");
}

$request_id = intval($_GET['request_id']);
$username = $_SESSION['username'];

// Update request status to Cancelled
$stmt = $conn->prepare("UPDATE document_requests SET status='Cancelled' WHERE id=? AND username=?");
$stmt->bind_param("is", $request_id, $username);
$stmt->execute();
$stmt->close();

// Optionally, delete related payments or deliveries if needed
// $conn->query("DELETE FROM payments WHERE request_id=$request_id");
// $conn->query("DELETE FROM deliveries WHERE request_id=$request_id");

header("Location: dashboard.php?cancelled=1");
exit();
