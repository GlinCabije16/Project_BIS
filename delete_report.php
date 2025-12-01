<?php
session_start();
require 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if 'id' is passed
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete the report
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Report deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete the report.";
    }
    $stmt->close();
}

header("Location: view_complaints.php");
exit();
?>
