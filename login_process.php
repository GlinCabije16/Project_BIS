<?php
session_start();
require_once "database.php"; // connect DB

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Secure query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ if password stored hashed in DB
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php"); // redirect to dashboard
            exit;
        }
        // ❌ if you are still storing plain text (not recommended), use:
        // if ($password === $user['password']) { ... }

        else {
            header("Location: login.php?error=Invalid password");
            exit;
        }
    } else {
        header("Location: login.php?error=User not found");
        exit;
    }
}
?>
