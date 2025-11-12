<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Reply to feedback
if(isset($_POST['reply_id'])){
    $reply = $_POST['admin_reply'];
    $id = intval($_POST['reply_id']);
    $stmt = $conn->prepare("UPDATE feedback SET admin_reply=?, replied_at=NOW() WHERE id=?");
    $stmt->bind_param("si", $reply, $id);
    $stmt->execute();
    header("Location: view_feedback.php");
    exit();
}

// Fetch all feedback
$result = $conn->query("SELECT * FROM feedback ORDER BY feedback_date DESC");



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Admin | Feedback</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
* { font-family: 'Poppins', sans-serif; }

body {
  margin: 0;
  display: flex;
  background: #f5f7fb;
}

/* Sidebar */
.sidebar {
  width: 250px;
  height: 100vh;
 background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
  color: white;
  padding: 30px 20px;
  position: fixed;
  left: 0;
  top: 0;
  display: flex;
  flex-direction: column;
}

.sidebar h2 {
  font-weight: 600;
  font-size: 22px;
  text-align: center;
  margin-bottom: 30px;
}

.sidebar a, .logout-btn {
  display: block;
  color: white;
  text-decoration: none;
  padding: 10px 15px;
  margin: 6px 0;
  border-radius: 8px;
  transition: 0.3s;
}

.sidebar a:hover, .logout-btn:hover {
  background: rgba(255,255,255,0.2);
}

.logout-btn {
  background: #ff4b5c;
  border: none;
  text-align: left;
  margin-top: auto;
  width: 100%;
}

/* Main Content */
.main-content {
  margin-left: 270px;
  padding: 40px;
  width: 100%;
}

h2 {
  color: #1e3a8a;
  font-weight: 600;
  margin-bottom: 25px;
}

/* Feedback Cards */
.card {
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 20px;
  background: white;
  margin-bottom: 20px;
  transition: 0.3s;
}
.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

textarea {
  width: 100%;
  border-radius: 8px;
  border: 1px solid #ccc;
  padding: 10px;
  resize: none;
}

.btn-success {
  background: linear-gradient(135deg, #00b09b, #96c93d);
  border: none;
  transition: 0.3s;
}
.btn-success:hover {
  background: linear-gradient(135deg, #009879, #82b92f);
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Barangay Admin</h2>
  <a href="admin_dashboard.php">🏠 Dashboard</a>
  <a href="manage_announcements.php">📢 Announcements</a>
  <a href="review_requests.php">📄 Requests</a>
  <a href="view_complaints.php">💬 Complaints</a>
  <a href="view_feedback.php">⭐ Feedback</a>
  <a href="manage_users.php">👥 Users</a>
  <a href="manage_residents.php">🏘️ Residents</a>
  <form action="logout.php" method="post">
    <button type="submit" class="logout-btn">🚪 Logout</button>
  </form>
</div>
  

<!-- Main Content -->
<div class="main-content">
  <h2>⭐ User Feedback</h2>

  <?php
  if($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
          echo "<div class='card'>";
          echo "<p><strong>👤 ".htmlspecialchars($row['username']).":</strong><br>".htmlspecialchars($row['message'])."</p>";
          
          if($row['admin_reply']){
              echo "<div class='mt-3 p-3 rounded' style='background:#f1f3ff;'>
                      <strong>🗨️ Your Reply:</strong><br>".htmlspecialchars($row['admin_reply'])."
                    </div>";
          } else {
              echo "<form method='POST' class='mt-3'>
                      <input type='hidden' name='reply_id' value='".$row['id']."'>
                      <textarea name='admin_reply' placeholder='Write a reply...' rows='3' required></textarea><br>
                      <button type='submit' class='btn btn-success mt-2'>Send Reply</button>
                    </form>";
          }

          echo "</div>";
      }
  } else {
      echo "<p class='text-muted'>No feedback submitted yet.</p>";
  }
  ?>
</div>

</body>
</html>
