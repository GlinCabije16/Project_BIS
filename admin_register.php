<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $official_id = trim($_POST['official_id']);

    // ✅ 1. Validate official_id
    $check = $conn->prepare("SELECT * FROM officials WHERE official_id = ?");
    $check->bind_param("s", $official_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid Barangay Official ID.');</script>";
    } else {
        // ✅ 2. Check if official_id already registered
        $checkAdmin = $conn->prepare("SELECT * FROM admins WHERE official_id = ?");
        $checkAdmin->bind_param("s", $official_id);
        $checkAdmin->execute();
        $exists = $checkAdmin->get_result();

        if ($exists->num_rows > 0) {
            echo "<script>alert('This official is already registered as admin.');</script>";
        } else {
            // ✅ 3. Check if username already exists
            $checkUser = $conn->prepare("SELECT * FROM admins WHERE username = ?");
            $checkUser->bind_param("s", $username);
            $checkUser->execute();
            $userExists = $checkUser->get_result();

            if ($userExists->num_rows > 0) {
                echo "<script>alert('Username already exists. Please choose another.');</script>";
            } else {
                // ✅ 4. Register new admin
                $stmt = $conn->prepare("INSERT INTO admins (username, password, official_id) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $password, $official_id);

                if ($stmt->execute()) {
                    echo "<script>alert('Admin registered successfully! You can now login.'); window.location='admin_login.php';</script>";
                } else {
                    echo "<script>alert('Error during registration. Please try again.');</script>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
  <meta charset="UTF-8">
  <title>Admin Registration</title>
  <style>
    body {
       background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
                  url('images/logo.png') center/cover no-repeat fixed;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      background-attachment: fixed;
      background-size: cover;
      margin: 0;
    }
    .container {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      width: 400px;
      padding: 40px 35px;
      box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.3);
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    h2 {
      color: white;
      font-size: 26px;
      margin-bottom: 20px;
      letter-spacing: 1px;
    }
    input {
      width: 380px;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: none;
      outline: none;
      font-size: 15px;
      background: rgba(255, 255, 255, 0.9);
    }
    button {
      background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85));
      color: white;
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 10px;
      transition: transform 0.2s, box-shadow 0.3s;
    }
    button:hover {
      transform: translateY(-3px);
      box-shadow: 0px 4px 15px rgba(37,117,252,0.4);
    }
    p {
      color: white;
      margin-top: 15px;
      font-size: 14px;
    }
    a {
      color: skyblue;
      text-decoration: none;
      font-weight: 500;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Admin Registration</h2>
    <form method="POST">
      <input type="text" name="official_id" placeholder="Barangay Official ID" required>
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="admin_login.php">Login here</a></p>
  </div>
</body>
</html>
