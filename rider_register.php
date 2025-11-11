<?php
session_start();
require 'database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $official_id = $_POST['official_id'] ?? '';

    // Check if Official ID exists in approved_riders
    $stmt = $conn->prepare("SELECT * FROM approved_riders WHERE official_id = ?");
    $stmt->bind_param("s", $official_id);
    $stmt->execute();
    $approved = $stmt->get_result();

    if ($approved->num_rows === 0) {
        $message = "❌ Official ID not found. You are not an approved rider.";
    } else {
        // Check if official_id already exists in riders table
        $stmt = $conn->prepare("SELECT * FROM riders WHERE official_id = ?");
        $stmt->bind_param("s", $official_id);
        $stmt->execute();
        $exists = $stmt->get_result();

        if ($exists->num_rows > 0) {
            $message = "⚠️ Official ID already registered.";
        } else {
            // Insert new rider
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO riders (fullname, username, password, email, contact, official_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $username, $hash, $email, $contact, $official_id);

            if ($stmt->execute()) {
                $message = "✅ Registration successful! You can now log in.";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Registration</title>
<style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
                  url('images/logo.png') center/cover no-repeat fixed;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
    }


    .register-container {
        background: rgba(255, 255, 255, 0.15);
        padding: 40px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        width: 350px;
        text-align: center;
        color: #fff;
    }

    .register-container h2 {
        margin-bottom: 20px;
        color: #fff;
    }

    .register-container input {
        width: 95%;
        padding: 10px;
        margin: 8px 0;
        border: none;
        border-radius: 8px;
        outline: none;
    }

    .register-container button {
        background-color: #2575fc;
        color: white;
        border: none;
        padding: 12px;
        width: 100%;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }

    .register-container button:hover {
        background-color: #1e5fd6;
    }

    button {
      width: 100%;
      background: linear-gradient(90deg, #2575fc, #6a11cb);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(90deg, #1e5ed3, #581caa);
      transform: scale(1.02);
    }

    .message {
        margin-top: 15px;
        font-weight: bold;
    }

    a {
        color: #a8d0ff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="register-container">
    <h2>Rider Registration</h2>
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="contact" placeholder="Contact Number" required>
        <input type="text" name="official_id" placeholder="Official Rider ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <p>Already have an account? <a href="rider_login.php">Login</a></p>
</div>
</body>
</html>
