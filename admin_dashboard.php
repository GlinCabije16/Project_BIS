<?php
session_start();

// Prevent caching so back button won’t reopen dashboard after logout
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");

if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

require 'database.php';

// Fetch admin notifications
$notif_sql = "SELECT * FROM notifications WHERE recipient_type='admin' AND is_read=0 ORDER BY created_at DESC LIMIT 5";
$notif_result = $conn->query($notif_sql);

// 🔹 COUNT TOTAL REQUESTS
$request_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM document_requests");
if ($result && $row = $result->fetch_assoc()) $request_count = $row['total'];

// 🔹 COUNT TOTAL COMPLAINTS
$complaint_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM complaints");
if ($result && $row = $result->fetch_assoc()) $complaint_count = $row['total'];

// 🔹 COUNT TOTAL FEEDBACK
$feedback_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM feedback");
if ($result && $row = $result->fetch_assoc()) $feedback_count = $row['total'];

// 🔹 COUNT TOTAL CONTACT MESSAGES
$contact_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM contacts");
if ($result && $row = $result->fetch_assoc()) $contact_count = $row['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  
<meta charset="UTF-8">
<title>Barangay Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    height: 100vh;
    overflow: hidden;
    display: flex;
    background: linear-gradient(135deg, rgba(45, 126, 255, 0.85), rgba(106,17,203,0.85)),
                url('images/logo.png') center/cover no-repeat fixed;
    background-size: 400% 400%;
    animation: gradientShift 10s ease infinite;
  }

  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* --- SIDEBAR --- */
  .sidebar {
    width: 250px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    border-right: 2px solid rgba(255,255,255,0.2);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 25px;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.3) transparent;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
  }

  .sidebar::-webkit-scrollbar { width: 6px; }
  .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 6px; }

  .sidebar h3 { font-size: 20px; margin-bottom: 25px; letter-spacing: 1px; }

  .sidebar a {
    display: block;
    width: 85%;
    color: white;
    text-decoration: none;
    padding: 12px 18px;
    margin: 6px 0;
    border-radius: 10px;
    transition: all 0.3s ease;
  }

  .sidebar a:hover, .sidebar a.active {
    background: rgba(255,255,255,0.3);
    transform: translateX(6px);
  }

  .logout-btn {
    background: #ff4b5c;
    align-items:start;
    border: none;
    color: white;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    font-size: 15px;
    margin-top: auto;
    margin-bottom: 30px;
    transition: 0.3s;
  }

  .logout-btn:hover { background:black; transform: scale(1.05); }

  /* --- MAIN CONTENT --- */
  .main-content {
    margin-left: 250px;
    padding: 40px;
    height: 100vh;
    width: calc(100% - 250px);
    overflow-y: auto;
    color: #fff;
  }

  header {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 18px 30px;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  header h1 { font-size: 24px; font-weight: 600; }

  /* --- DASHBOARD CARDS --- */
  .container {
    margin-top: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 25px;
  }

  .card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 25px;
    color: #fff;
    text-align: center;
    transition: all 0.4s ease;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    position: relative;
  }

  .card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 30px rgba(0,0,0,0.25);
  }

  .card h2 { font-size: 22px; margin-bottom: 10px; position: relative; }

  /* 🔹 Count Badge */
  .badge {
    background: #00ffdd;
    color: #000;
    font-size: 13px;
    font-weight: bold;
    padding: 4px 10px;
    border-radius: 20px;
    position: absolute;
    top: -8px;
    right: 10px;
    box-shadow: 0 0 10px #00ffdd;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { box-shadow: 0 0 0px #00ffdd; }
    50% { box-shadow: 0 0 12px #00ffdd; }
    100% { box-shadow: 0 0 0px #00ffdd; }
  }

  .card p { font-size: 15px; color: #e0e0e0; margin-bottom: 15px; }
  .card a { color: #00ffdd; font-weight: 600; text-decoration: none; transition: 0.3s; }
  .card a:hover { color: #fff; text-decoration: underline; }

  /* --- NOTIFICATION BOX --- */
  .notification-box {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    margin-top: 40px;
    padding: 20px;
    color: #fff;
    animation: slideIn 0.8s ease;
  }

  .notification-box h3 { font-size: 20px; margin-bottom: 10px; }
  .notification-box ul { list-style: none; padding: 0; }
  .notification-box li { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); font-size: 14px; }
  .notification-box small { display: block; opacity: 0.7; }
  .notification-box p { color: #ccc; }

  @keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 900px) {
    .sidebar { width: 70px; }
    .main-content { margin-left: 70px; width: calc(100% - 70px); }
    .sidebar h3, .sidebar a span { display: none; }
  }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h3>Barangay Admin</h3>
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
    <button class="logout-btn" type="submit">Logout</button>
  </form>
</div>  

<!-- Main content -->
<div class="main-content">
  <header>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin']); ?> 👋</h1>
  </header>

  <div class="container">
    <div class="card">
      <h2>📄 User Requests <span class="badge"><?= $request_count ?></span></h2>
      <p>Review user document requests.</p>
      <a href="review_requests.php">Go →</a>
    </div>

    <div class="card">
      <h2>💬 Complaints <span class="badge"><?= $complaint_count ?></span></h2>
      <p>Handle and resolve issues.</p>
      <a href="view_complaints.php">Go →</a>
    </div>

    <div class="card">
      <h2>⭐ Feedback <span class="badge"><?= $feedback_count ?></span></h2>
      <p>View and learn from feedback.</p>
      <a href="view_feedback.php">Go →</a>
    </div>

    <div class="card">
      <h2>📞 Contacts <span class="badge"><?= $contact_count ?></span></h2>
      <p>Respond to inquiries.</p>
      <a href="admin_contacts.php">Go →</a>
    </div>

    <div class="card"><h2>📢 Announcements</h2><p>Post and edit announcements.</p><a href="manage_announcements.php">Go →</a></div>
    <div class="card"><h2>💳 Transactions History</h2><p>Track all barangay payments.</p><a href="view_transactions.php">Go →</a></div>
    <div class="card"><h2>👥 Manage Residents</h2><p>Manage resident information.</p><a href="manage_residents.php">Go →</a></div>
    <div class="card"><h2>🔐 Manage Users</h2><p>Manage account access.</p><a href="manage_users.php">Go →</a></div>
    <div class="card"><h2>📦 Deliveries</h2><p>Monitor and assign deliveries.</p><a href="admin_manage_deliveries.php">Go →</a></div>
    <div class="card"><h2>🚴 Manage Riders</h2><p>Oversee delivery riders.</p><a href="admin_manage_riders.php">Go →</a></div>
  </div>

  <div class="notification-box">
    <h3>🔔 Recent Notifications</h3>
  
    <?php if ($notif_result && $notif_result->num_rows > 0): ?>
        <ul>...</ul>
      <ul>
        <?php while($notif = $notif_result->fetch_assoc()): ?>
          <li>
            <?= htmlspecialchars($notif['message']) ?>
            <small>🕓 <?= $notif['created_at'] ?></small>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <p>No new notifications 🎉</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

<script>
function updateDashboard() {
  fetch('fetch_counts.php')
    .then(res => res.json())
    .then(data => {
      // Update badges
      document.querySelector('.card:nth-child(1) .badge').textContent = data.requests;
      document.querySelector('.card:nth-child(2) .badge').textContent = data.complaints;
      document.querySelector('.card:nth-child(3) .badge').textContent = data.feedback;
      document.querySelector('.card:nth-child(4) .badge').textContent = data.contacts;

      // Update notifications box
      const notifBox = document.querySelector('.notification-box ul');
      if (notifBox) {
        let currentNotifs = Array.from(notifBox.querySelectorAll('li')).map(li => li.textContent.trim());
        let newNotifs = '';

        data.notifications.forEach(n => {
          let msg = n.message.trim();
          if (!currentNotifs.includes(msg)) {
            newNotifs += `<li>${msg}<small>🕓 ${n.created_at}</small></li>`;
          }
        });

        if (newNotifs) {
          notifBox.insertAdjacentHTML('afterbegin', newNotifs);
        }
      }
    })
    .catch(err => console.error('Error updating dashboard:', err));
}

// Run every 5 seconds
setInterval(updateDashboard, 5000);
</script>

