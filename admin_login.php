<?php
session_start();
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
      $_SESSION['admin'] = $admin['username'];
      header("Location: admin_dashboard.php");
      exit();
    } else {
      echo "<script>alert('Incorrect password.');</script>";
    }
  } else {
    echo "<script>alert('Admin not found.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Login Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
                  url('images/logo.png') center/cover no-repeat fixed;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
    }

    .page-title {
      color: #fff;
      font-size: 36px;
      font-weight: 700;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 1.5px;
      text-shadow: 0 3px 12px rgba(0, 0, 0, 0.5);
      margin-bottom: 30px;
      animation: fadeInDown 1s ease-in-out;
    }

    .container {
      background: rgba(255, 255, 255, 0.10);
      padding: 50px 40px;
      width: 400px;
      border-radius: 16px;
      backdrop-filter: blur(10px);
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.4);
      text-align: center;
      animation: fadeIn 1s ease-in-out;
    }

    h2 {
      color: #fff;
      font-size: 24px;
      margin-bottom: 25px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    input {
      width: 90%;
      padding: 12px;
      margin: 12px 0;
      border: none;
      border-radius: 8px;
      outline: none;
      font-size: 15px;
      color: #333;
      background: rgba(255, 255, 255, 0.9);
    }

    button {
      width: 380px;
      background: linear-gradient(90deg, #2575fc, #6a11cb);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
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
      transition: 0.3s;
    }

    a:hover { color: #d0e4ff; }

    p { color: #fff; font-size: 14px; }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-40px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <h1 class="page-title">Barangay Login Portal</h1>

  <div class="container">
    <h2>Admin Login</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Enter Username" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit">Login</button>
    </form>
    <p><a href="index.php">⬅ Back to Home</a></p>
    <p>Not registered? <a href="admin_register.php">Register here</a></p>
  </div>
</body>
</html>
