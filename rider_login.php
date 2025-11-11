<?php
session_start();
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $usernameOrId = $_POST['username']; // can be username or official_id
  $password = $_POST['password'];

  // Try to match username OR official_id
  $stmt = $conn->prepare("SELECT * FROM riders WHERE username = ? OR official_id = ?");
  $stmt->bind_param("ss", $usernameOrId, $usernameOrId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $rider = $result->fetch_assoc();

    // Check password or allow ID-based login if password not required
    if (!empty($rider['password']) && password_verify($password, $rider['password'])) {
      $_SESSION['rider'] = $rider['username'];
      $_SESSION['rider_name'] = $rider['full_name'];
      header("Location: rider_dashboard.php");
      exit();
    } elseif ($password === $rider['official_id']) { 
      // Fallback: if rider enters their official ID as password
      $_SESSION['rider'] = $rider['username'];
      $_SESSION['rider_name'] = $rider['full_name'];
      header("Location: rider_dashboard.php");
      exit();
    } else {
      echo "<script>alert('Incorrect password or ID.');</script>";
    }
  } else {
    echo "<script>alert('Rider not found.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rider Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
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

    .container {
      background: rgba(255, 255, 255, 0.10);
      padding: 40px 35px;
      width: 380px;
      border-radius: 15px;
      backdrop-filter: blur(15px);
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
      text-align: center;
    }

    h2 {
      color: white;
      font-size: 24px;
      margin-bottom: 20px;
    }

    input {
      width: 80%;
      padding: 12px;
      margin: 10px 0;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      background: rgba(255, 255, 255, 0.9);
    }

    button {
      width: 88%;
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

    a {
      color: #90ee90;
      text-decoration: none;
      font-weight: 500;
      display: inline-block;
      margin-top: 12px;
    }

    a:hover { color: #d0e4ff; }

    p { color: #fff; font-size: 14px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Rider Login</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Enter Username or Official ID" required>
      <input type="password" name="password" placeholder="Enter Password or Official ID" required>
      <button type="submit">Login</button>
    </form>
    <p><a href="index.php">⬅ Back to Home</a></p>
    <p>Not registered? <a href="rider_register.php">Register here</a></p>
  </div>
</body>
</html>
