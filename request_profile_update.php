<?php
session_start();
require 'database.php';

if (!isset($_SESSION['rider'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_username = $_SESSION['rider'];

// Fetch rider ID
$stmt = $conn->prepare("SELECT id FROM riders WHERE username = ?");
$stmt->bind_param("s", $rider_username);
$stmt->execute();
$rider = $stmt->get_result()->fetch_assoc();
$rider_id = $rider['id'] ?? null;
$stmt->close();

if (!$rider_id) {
    echo "Error: Rider not found.";
    exit();
}

// Get new info
$new_fullname = $_POST['fullname'] ?? '';
$new_username = $_POST['username'] ?? '';
$new_contact = $_POST['contact'] ?? '';
$new_password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Upload profile picture
$new_profile_pic = null;
if (!empty($_FILES['profile_pic']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $filename = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        $new_profile_pic = $target_file;
    }
}

// Insert pending update
$stmt = $conn->prepare("
    INSERT INTO rider_profile_updates 
    (rider_id, new_fullname, new_username, new_contact, new_password, new_profile_pic, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'Pending')
");
$stmt->bind_param("isssss", $rider_id, $new_fullname, $new_username, $new_contact, $new_password, $new_profile_pic);
$stmt->execute();
$stmt->close();

$_SESSION['success_message'] = "Your profile update has been sent for admin approval!";
header("Location: rider_dashboard.php");
exit();
?>
