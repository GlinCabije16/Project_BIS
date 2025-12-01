<?php
session_start();
require 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Activate/Deactivate user
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    // Use current status param but sanitize fallback
    $current = isset($_GET['status']) ? $_GET['status'] : '';
    $status = ($current === 'Active') ? 'Deactive' : 'Active';

    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// Handle OTP request
if (isset($_GET['otp'])) {
    $id = intval($_GET['otp']);
    $otp = rand(100000, 999999); // generate 6-digit OTP
    $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
    $plain_otp = (string)$otp;

    // Update both hashed password and plain_password
    $stmt = $conn->prepare("UPDATE users SET password=?, plain_password=? WHERE id=?");
    $stmt->bind_param("ssi", $hashed_otp, $plain_otp, $id);
    $stmt->execute();

    // fetch user to show message
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    echo "<script>alert('One-Time Password for {$user['username']} ({$user['email']}): $otp'); window.location.href='manage_users.php';</script>";
    exit();
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Admin | Manage Users</title>
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

/* Table Styling */
.table-container {
  background: white;
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.table th {
  background: #e9efff;
  color: #1e3a8a;
}

.table td {
  vertical-align: middle;
}

.btn-info {
  background: linear-gradient(135deg, #36d1dc, #5b86e5);
  border: none;
}

.btn-warning {
  background: linear-gradient(135deg, #f7971e, #ffd200);
  border: none;
}

.btn-success {
  background: linear-gradient(135deg, #00b09b, #96c93d);
  border: none;
}

.btn:hover {
  opacity: 0.9;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Barangay Admin</h2>
   <a href="admin_dashboard.php" class="active">🏠 <span>Dashboard</span></a>
  <a href="manage_announcements.php">📢 <span>Announcements</span></a>
  <a href="review_requests.php">📄 <span>User Requests</span></a>
  <a href="view_transactions.php">💳 <span>Transactions</span></a>
  <a href="manage_residents.php">👥 <span>Residents</span></a>
  <a href="view_complaints.php">💬 <span>Complaints</span></a>
  <a href="view_feedback.php">⭐ <span>Feedback</span></a>
  <a href="admin_contacts.php">📞 <span>Contacts</span></a>
  <a href="manage_users.php">🔐 <span>Users</span></a>
  <a href="admin_manage_deliveries.php">📦 <span>Deliveries</span></a>
  <a href="admin_manage_riders.php">🚴 <span>Manage Riders</span></a>
  <form action="logout.php" method="post">
    <button type="submit" class="logout-btn">🚪 Logout</button>
  </form>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2>👥 Manage Users</h2>
  <div class="table-container">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Status</th>
          <th>Plain Password</th>
          <th class="text-center">Actions</th>


        </tr>
      </thead>
      <tbody>
        <?php while ($row = $users->fetch_assoc()): ?>
        <tr>
          <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <span class="badge <?= $row['status']=='Active' ? 'bg-success' : 'bg-secondary' ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>

          <!-- Plain password column with masked text + Show button -->
          <td>
            <span id="pw-<?= (int)$row['id'] ?>">••••••••</span>
            <button class="btn btn-sm btn-outline-primary ms-2" onclick="revealPassword(<?= (int)$row['id'] ?>, this)">Show</button>
          </td>

          <td class="text-center">
            <a href="manage_users.php?toggle=<?= (int)$row['id'] ?>&status=<?= urlencode($row['status']) ?>" 
               class="btn btn-sm <?= $row['status']=='Active'?'btn-warning':'btn-success' ?>">
               <?= $row['status']=='Active'?'Deactivate':'Activate' ?>
            </a>
            <a href="manage_users.php?otp=<?= (int)$row['id'] ?>" class="btn btn-sm btn-info">Generate OTP</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function revealPassword(userId, btn) {
  btn.disabled = true;
  const span = document.getElementById('pw-' + userId);

  // toggle hide/show
  if (span.dataset.shown === '1') {
    span.textContent = '••••••••';
    span.dataset.shown = '0';
    btn.textContent = 'Show';
    btn.disabled = false;
    return;
  }

  btn.textContent = 'Loading...';

  try {
    const res = await fetch('reveal_password.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ id: userId })
    });

    const data = await res.json();
    if (data.success) {
      span.textContent = data.plain_password || '(empty)';
      span.dataset.shown = '1';
      btn.textContent = 'Hide';
    } else {
      alert(data.message || 'Could not retrieve password.');
      btn.textContent = 'Show';
    }
  } catch (err) {
    console.error(err);
    alert('Request failed. See console.');
    btn.textContent = 'Show';
  } finally {
    btn.disabled = false;
  }
}
</script>

</body>
</html>
