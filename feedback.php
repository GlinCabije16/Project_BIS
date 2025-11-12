<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$username = $_SESSION['username'];

if(isset($_POST['submit'])) {
    $message = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO feedback (username, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $message);
    $stmt->execute();
    $success = "Your feedback has been sent!";
}

// Fetch user feedback with admin replies
$result = $conn->prepare("SELECT * FROM feedback WHERE username=? ORDER BY feedback_date DESC");
$result->bind_param("s", $username);
$result->execute();
$feedbacks = $result->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>💬 Feedback</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body {
    background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.main-content {
    max-width: 800px;
    margin: 50px auto;
    padding: 10px;
}
.card {
    margin-bottom: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    padding: 30px;
    background: linear-gradient(145deg, #ffffff, #f1f3f6);
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}
.card h2 {
    margin-bottom: 20px;
    font-weight: 700;
    color: #343a40;
}
textarea.form-control {
    border-radius: 12px;
    min-height: 120px;
    resize: none;
}
.btn-primary {
    border-radius: 12px;
    padding: 10px 25px;
    font-weight: 600;
    background: linear-gradient(90deg, #6f42c1, #7950f2);
    border: none;
    transition: background 0.3s;
}
.btn-primary:hover {
    background: linear-gradient(90deg, #7950f2, #6f42c1);
}
.btn-secondary {
    border-radius: 12px;
    padding: 10px 25px;
    color: white;
    background-color: green ;
    


  }
.feedback-message {
    background: #f8f9fa;
    border-left: 4px solid #6f42c1;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.admin-reply {
    background: #e2e3e5;
    border-left: 4px solid #198754;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.text-success {
    font-weight: 600;
}
</style>
</head>
<body>

<div class="main-content">
  <!-- Feedback Form Card -->
  <div class="card">
    <h2>Send Feedback</h2>
    <?php if(isset($success)) echo "<p class='text-success'>$success</p>"; ?>
    <form method="POST">
      <textarea name="message" class="form-control" required placeholder="Write your feedback here..."></textarea><br>
      <button type="submit" name="submit" class="btn btn-primary">Send Feedback</button>
    </form>
    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
  </div>

  <!-- Feedback & Replies Card -->
  <div class="card">
    <h2>My Feedback & Replies</h2>
    <?php
    if($feedbacks->num_rows > 0){
        while($row = $feedbacks->fetch_assoc()){
            echo "<div class='feedback-message'><strong>You:</strong> ".htmlspecialchars($row['message'])."</div>";
            if($row['admin_reply']){
                echo "<div class='admin-reply'><strong>Admin:</strong> ".htmlspecialchars($row['admin_reply'])."</div>";
            } else {
                echo "<p><em>No reply yet.</em></p>";
            }
        }
    } else {
        echo "<p class='text-muted'>No feedback submitted yet.</p>";
    }
    ?>
  </div>
</div>

</body>
</html>
