<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Barangay System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
</head>


<body>


<?php
session_start();
$conn = new mysqli('localhost','root','','login_register');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();

    if ($id && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $name;

        header("Location: ../barangay_db/request.php");
        exit();
    } else {
        echo "Invalid credentials";
    }
}
?>



  <!-- Page Title -->
  <div class="form-title">
    <h1>Barangay Login Portal</h1>
  </div>

  <!-- Login Form Box -->
  <div class="form-box d-flex flex-wrap align-items-center">
    
    <!-- Left Side Logo -->
    <div class="form-logo text-center">
      <img src="images/logo.png" alt="Barangay Logo">
    </div>

    <!-- Right Side Form -->
    <div class="form-content">
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <h2 class="text-center mb-4">Login</h2>

      <form action="login_process.php" method="POST">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
          <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="togglePassword">
            <label class="form-check-label" for="togglePassword">Show Password</label>
          </div>
        </div>

        <button type="submit" name="login" class="btn btn-custom">Login</button>

        <p class="mt-3 text-center">
          Don’t have an account? <a href="registration.php" class="signup-link">Sign up</a>
        </p>

        <!-- ✅ Added "Back to Home" button -->
        <p class="mt-2 text-center">
          <a href="index.php" class="text-primary fw-bold">⬅ Back to Home</a>
        </p>
      </form>
    </div>
  </div>

  <script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("change", function() {
      password.type = this.checked ? "text" : "password";
    });
  </script>

</body>
</html>
