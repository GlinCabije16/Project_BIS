<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch all announcements (latest first)
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
  <meta charset="UTF-8">
  <title>📢 Announcements</title>
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
      background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
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
      margin-bottom: 25px;
      background: white;
    }
    .card h1, .card h2 {
      color: #1e3a8a;
      font-weight: 600;
    }
    .logout {
      color: #f87171 !important;
      font-weight: 600;
    }
    @media (max-width: 768px) {
      .sidebar { width: 100%; height: auto; position: relative; }
      .main-content { margin-left: 0; }
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
  <a href="announcement.php" class="active">📢 Announcements</a>
  <a href="contact.php">📞 Contact</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4 mb-4">
    <h1>📢 Announcements</h1>
    <p class="text-secondary">Latest updates from your barangay</p>
  </div>

  <div class="card p-4">
    <?php
    if ($announcements->num_rows > 0) {
        while($row = $announcements->fetch_assoc()) {
            echo "<h4>".htmlspecialchars($row['title'])."</h4>";
            echo "<p>".htmlspecialchars($row['content'])."</p>";
            echo "<small class='text-muted'>Posted on: ".date('Y-m-d H:i', strtotime($row['created_at']))."</small><hr>";
        }
    } else {
        echo "<p class='text-muted'>No announcements at the moment. Please check back later.</p>";
    }
    ?>
  </div>
</div>

</body>
</html>
