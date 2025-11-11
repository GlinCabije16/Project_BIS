<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$current_username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['new_username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $current_username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $username_changes = (int)$user['username_changes'];
    $password_changes = (int)$user['password_changes'];

    $messages = [];

    // Username update
    if (!empty($new_username) && $new_username !== $current_username) {
        if ($username_changes < 2) {
            $stmt = $conn->prepare("UPDATE users SET username=?, username_changes=username_changes+1 WHERE username=?");
            $stmt->bind_param("ss", $new_username, $current_username);
            $stmt->execute();
            $_SESSION['username'] = $new_username;
            $current_username = $new_username;
            $messages[] = "Username updated successfully!";
        } else {
            $messages[] = "⚠️ You have reached the maximum username changes (2).";
        }
    }

    // Password update
    if (!empty($new_password) && $new_password === $confirm_password) {
        if ($password_changes < 2) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=?, plain_password=?, password_changes=password_changes+1 WHERE username=?");
            $stmt->bind_param("sss", $hashed, $new_password, $current_username);
            $stmt->execute();
            $messages[] = "Password updated successfully!";
        } else {
            $messages[] = "⚠️ You have reached the maximum password changes (2).";
        }
    } elseif (!empty($new_password) || !empty($confirm_password)) {
        $messages[] = "❌ Passwords do not match.";
    }

    echo "<script>alert('" . implode("\\n", $messages) . "'); window.location.href='dashboard.php';</script>";
    exit();
}

// rider_profile_update.php
session_start();
require 'database.php';

$rider_id = $_SESSION['rider_id'];
$fullname = $_POST['fullname'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$profile_pic = $_FILES['profile_pic'] ?? null;

$pic_filename = null;
if($profile_pic && $profile_pic['tmp_name']){
    $ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
    $pic_filename = 'rider_'.$rider_id.'_'.time().'.'.$ext;
    move_uploaded_file($profile_pic['tmp_name'], 'uploads/'.$pic_filename);
}

$stmt = $conn->prepare("INSERT INTO rider_profile_updates 
    (rider_id, new_fullname, new_username, new_password, new_profile_pic) 
    VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $rider_id, $fullname, $username, $password, $pic_filename);
$stmt->execute();
$stmt->close();

$_SESSION['toast'] = "Profile update submitted for admin approval!";
header("Location: rider_dashboard.php");
exit();

?>
