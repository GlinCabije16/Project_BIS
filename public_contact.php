<?php
require 'database.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $success = "Message sent successfully! Admin will reply soon.";
        } else {
            $success = "Failed to send message. Please try again.";
        }
    } else {
        $success = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Contact Admin | Barangay System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg,#2575fc,#1111);
  color: #fff;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px 0;
}
.container {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  padding: 30px;
  border-radius: 15px;
  max-width: 500px;
  width: 100%;
}
h1 { text-align:center; margin-bottom: 20px; }
input, textarea { width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:none; }
textarea { resize:none; height:120px; }
button {
  width:100%; padding:12px; border:none; border-radius:8px;
  background:#fff; color:#2575fc; font-weight:600; transition:0.3s;
}
button:hover { background:#2575fc; color:#fff; transform:scale(1.03); }
.success { text-align:center; margin-bottom:15px; color:#00ffdd; font-weight:600; }
nav {
  width:100%; position:absolute; top:0; left:0; right:0;
  padding:15px 40px; display:flex; justify-content:flex-end; gap:20px;
}
nav a { color:#fff; text-decoration:none; font-weight:500; }
nav a:hover { color:#2575fc; }
</style>
</head>
<body>

<nav>
  <a href="index.php">Home</a>
  <a href="about.php">About</a>
  <a href="public_contact.php">Contact</a>
</nav>

<div class="container">
  <h1>Contact Barangay Admin</h1>
  <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

  <form method="POST">
    <input type="text" name="name" placeholder="Your Name" required>
    <input type="email" name="email" placeholder="Your Email" required>
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="message" placeholder="Type your message..." required></textarea>
    <button type="submit">Send Message</button>
  </form>
</div>

</body>
</html>
