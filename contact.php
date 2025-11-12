<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// ✅ Fetch user email from the users table
$stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$email = $user['email'] ?? '';

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $email = trim($_POST['email']);

    if (!empty($subject) && !empty($message) && !empty($email)) {
        $insert = $conn->prepare("INSERT INTO contacts (username, email, subject, message, status) VALUES (?, ?, ?, ?, 'Pending')");
        $insert->bind_param("ssss", $username, $email, $subject, $message);
        $insert->execute();

        echo "<script>
            alert('Message sent successfully!');
            window.location.href='contact.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Contact | Barangay System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #f1f5f9;
}
.sidebar {
  position: fixed; top: 0; left: 0;
  width: 250px; height: 100%;
  background: linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
              url('images/logo.png') center center fixed;
  padding-top: 20px; color: #fff;
}
.sidebar h2 {
  text-align: center; font-weight: 600; margin-bottom: 40px;
}
.sidebar a {
  display: block; padding: 12px 20px; color: #e2e8f0;
  text-decoration: none; margin: 5px 10px; border-radius: 6px;
  font-weight: 500; transition: 0.2s;
}
.sidebar a:hover, .sidebar a.active {
  background: #60a5fa; color: white;
}
.main-content {
  margin-left: 250px; padding: 30px;
}
.card {
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  background: white;
  padding: 30px;
  margin-bottom: 25px;
}
.logout {
  color: #f87171 !important;
  font-weight: 600;
}
form input, form textarea {
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 10px;
  margin-top: 10px;
}
form textarea {
  resize: none;
  height: 120px;
}
button {
  background-color: #1e3a8a;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 8px;
  margin-top: 15px;
  width: 100%;
  transition: 0.3s;
}
button:hover {
  background-color: #2563eb;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>🏡 Barangay System</h2>
  <a href="dashboard.php">💻 Dashboard</a>
  <a href="user_request.php">📄 Document Request</a>
  <a href="history.php">📜 History</a>
  <a href="reports.php">📊 Reports</a>
  <a href="feedback.php">💬 Feedback</a>
  <a href="announcement.php">📢 Announcements</a>
  <a href="contact.php" class="active">📞 Contact</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card">
    <h2>📞 Contact Barangay Support</h2>
    <p>Have an issue or question? Send us a message below.</p>
    <form method="POST">
      <label>Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

      <label>Subject:</label>
      <input type="text" name="subject" placeholder="Enter subject" required>

      <label>Message:</label>
      <textarea name="message" placeholder="Type your message..." required></textarea>

      <button type="submit">Send Message</button>
    </form>
  </div>

  <div class="card">
    <h4>📍 Barangay Poblacion Boston, Davao Oriental</h4>
    <p><b>Hotline:</b> 0935-123-4567</p>
    <p><b>Email:</b> barangaypoblacion@gmail.com</p>
    <p><b>Office Hours:</b> Monday - Friday (8:00 AM - 5:00 PM)</p>
  </div>

  <!-- ✅ Moved Replies Section INSIDE main-content -->
  <div class="card p-4 mb-4">
    <h2>📞 Admin Replies</h2>
    <hr>
    <?php
    // Fetch contact messages for the logged-in user
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE username = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $contactResult = $stmt->get_result();

    if ($contactResult->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Message</th><th>Admin Reply</th><th>Status</th></tr></thead><tbody>';
        while ($contact = $contactResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($contact['message']) . '</td>';
            echo '<td>' . (!empty($contact['admin_reply']) ? htmlspecialchars($contact['admin_reply']) : '<em>No reply yet</em>') . '</td>';
            echo '<td>';
            if ($contact['status'] === 'Replied') {
                echo '<span class="badge bg-success">Replied ✅</span>';
            } else {
                echo '<span class="badge bg-warning text-dark">Pending</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p class='text-muted'>You haven't sent any contact messages yet.</p>";
    }
    ?>
  </div>
</div>

</body>
</html>
