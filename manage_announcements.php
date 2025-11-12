<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Add new announcement
if (isset($_POST['title'], $_POST['content'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO announcements (title, content, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $date);
    $stmt->execute();
    header("Location: manage_announcements.php");
    exit();
}

// ✅ Delete announcement
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_announcements.php");
    exit();
}

// ✅ Fetch all announcements
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Admin | Announcements</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, rgba(37,117,252,0.9), rgba(106,17,203,0.9)),
                url('images/logo.png') center/cover no-repeat fixed;
    color: #fff;
    min-height: 100vh;
    padding-bottom: 60px;
  }

  header {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 18px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  header h1 {
    font-size: 22px;
    font-weight: 600;
  }

  nav a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    margin-left: 20px;
    transition: 0.3s;
  }

  nav a:hover {
    color: #00ffd0;
    text-shadow: 0 0 8px #00ffd0;
  }

  .container {
    max-width: 1000px;
    margin: 40px auto;
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    padding: 40px;
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  }

  h2 {
    margin-top: 0;
    color: #fff;
    border-left: 5px solid #00ffd0;
    padding-left: 10px;
  }

  form {
    background: rgba(255,255,255,0.15);
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }

  input, textarea {
    width: 100%;
    padding: 10px 14px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: 15px;
  }

  input::placeholder, textarea::placeholder {
    color: rgba(255,255,255,0.7);
  }

  button {
    background: linear-gradient(90deg, #2575fc, #6a11cb);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
  }

  button:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(37,117,252,0.6);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255,255,255,0.15);
    border-radius: 15px;
    overflow: hidden;
  }

  th, td {
    padding: 12px 15px;
    text-align: center;
  }

  th {
    background: rgba(0,0,0,0.3);
    color: #00ffd0;
    font-weight: 600;
  }

  tr:hover {
    background: rgba(255,255,255,0.1);
    transition: 0.3s;
  }

  .btn-delete {
    background: #ff4b5c;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
  }

  .btn-delete:hover {
    background: #e03145;
    transform: scale(1.05);
  }

  td a {
    color: #ffb3b3;
    text-decoration: none;
    font-weight: 600;
  }

  td a:hover {
    color: #fff;
  }

  @media (max-width: 768px) {
    .container {
      width: 90%;
      padding: 25px;
    }
    header {
      flex-direction: column;
      text-align: center;
    }
  }
</style>
</head>
<body>
<header>
  <h1>📢 Manage Announcements</h1>
  <nav>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</header>

<div class="container">
  <h2>📝 Create Announcement</h2>
  <form method="POST">
    <input type="text" name="title" placeholder="Announcement Title" required>
    <textarea name="content" rows="4" placeholder="Enter announcement details..." required></textarea>
    <button type="submit">+ Add Announcement</button>
  </form>

  <h2>📜 Existing Announcements</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Content</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['content']) ?></td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete this announcement?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">No announcements found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
