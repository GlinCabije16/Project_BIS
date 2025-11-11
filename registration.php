<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Registration System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, rgba(37,117,252,0.9), rgba(106,17,203,0.9)),
                  url('images/logo.png') center/cover no-repeat fixed;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      padding: 40px 0;
    }

    .form-side {
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(15px);
      border-radius: 16px;
      padding: 40px;
      width: 85%;
      max-width: 850px;
      box-shadow: 0 0 30px rgba(0,0,0,0.3);
      animation: fadeIn 1s ease-in-out;
    }

    .form-title h1 {
      text-align: center;
      font-size: 28px;
      color: #fff;
      text-shadow: 0 0 8px rgba(0,0,0,0.4);
      margin-bottom: 25px;
      letter-spacing: 1px;
      font-weight: 600;
    }

    label {
      color: #fff;
      font-weight: 500;
    }

    .form-control, .form-select, textarea {
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-size: 15px;
      color: #333;
    }

    .btn-custom {
      width: 100%;
      background: linear-gradient(90deg, #2575fc, #6a11cb);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .btn-custom:hover {
      background: linear-gradient(90deg, #1e5ed3, #581caa);
      transform: scale(1.02);
    }

    .signup-link {
      color: #ffd700;
      text-decoration: none;
      transition: 0.3s;
    }

    .signup-link:hover {
      color: #fff;
      text-decoration: underline;
    }

    .logo-top-left img {
      position: absolute;
      top: 20px;
      left: 20px;
      width: 300px;
      border-radius: 50%;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
 <div class="logo-top-left">
    <img src="images/logo.png" alt="Barangay Logo">
  </div>

  <div class="form-side">
    <div class="form-title">
      <h1>Barangay Registration System Form</h1>
    </div>

    <?php
  include 'database.php';
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $first_name = $_POST['first_name'];
      $last_name = $_POST['last_name'];
      $gender = $_POST['gender'];
      $civil_status = $_POST['civil_status'];
      $citizenship = $_POST['citizenship'];
      $contact_number = $_POST['contact_number'];
      $email = $_POST['email'];
      $address = $_POST['address'];
      $username = $_POST['username'];
      $password = $_POST['password'];
      $confirm_password = $_POST['confirm_password'];

      if ($password !== $confirm_password) {
          die("<script>alert('Passwords do not match!');</script>");
      }

      // 🔐 Encrypt + store plain text version
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $plain_password = $password; // 👈 Store plain text for admin view

      // ✅ Make sure you added a 'plain_password' column in your users table
      $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, gender, civil_status, citizenship, contact_number, email, address, username, password, plain_password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
      );

      $stmt->bind_param(
        "sssssssssss", 
        $first_name, 
        $last_name, 
        $gender, 
        $civil_status, 
        $citizenship, 
        $contact_number, 
        $email, 
        $address, 
        $username, 
        $hashed_password, 
        $plain_password
      );

      if ($stmt->execute()) {
          echo "<script>alert('Registration Successful!');</script>";  
      } else {
          echo "Error: " . $stmt->error;
      }

      $stmt->close();
      $conn->close();
  }
?>


    <form action="registration.php" method="POST">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name" class="form-control" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="gender">Gender</label>
          <select id="gender" name="gender" class="form-select" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label for="civil_status">Civil Status</label>
          <select id="civil_status" name="civil_status" class="form-select" required>
            <option value="">Select Status</option>
            <option>Single</option>
            <option>Married</option>
            <option>Widowed</option>
            <option>Separated</option>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label for="citizenship">Citizenship</label>
          <input type="text" id="citizenship" name="citizenship" class="form-control" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="contact_number">Contact Number</label>
          <input type="tel" id="contact_number" name="contact_number" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="address">Address</label>
        <textarea id="address" name="address" class="form-control" rows="2" required></textarea>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
          <div class="form-check show-password mt-1">
            <input type="checkbox" class="form-check-input" id="togglePassword">
            <label class="form-check-label" for="togglePassword">Show</label>
          </div>
          <div class="password-strength" id="strengthMessage"></div>
        </div>

        <div class="col-md-6 mb-3">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
      </div>

      <button type="submit" class="btn btn-custom">Register</button>
    </form>

    <p class="mt-3 text-center">
      Already have an account? <a href="login.php" class="signup-link">Login here</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");
    const strengthMessage = document.getElementById("strengthMessage");

    togglePassword.addEventListener("change", function() {
      password.type = this.checked ? "text" : "password";
    });

    password.addEventListener("input", function() {
      const val = password.value;
      if (val.length < 6) {
        strengthMessage.textContent = "Weak (min 6 characters)";
        strengthMessage.style.color = "red";
      } else if (val.match(/[A-Z]/) && val.match(/[0-9]/)) {
        strengthMessage.textContent = "Strong";
        strengthMessage.style.color = "green";
      } else {
        strengthMessage.textContent = "Medium (add numbers & uppercase)";
        strengthMessage.style.color = "orange";
      }
    });
  </script>
</body>
</html>
