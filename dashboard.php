<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch deliveries with claimed rider
$query = "SELECT d.* FROM deliveries d WHERE d.username=? AND d.rider_name IS NOT NULL AND d.delivery_status<>'Pending' ORDER BY d.delivery_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$deliveries = $stmt->get_result();

// Fetch user document requests
$query = "SELECT * FROM document_requests WHERE username = ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Fetch latest announcements (limit to 5)
$announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcements = $conn->query($announcements_query);

// ✅ Auto-delete chat messages after delivery is marked "Delivered"
$conn->query("DELETE FROM chat_messages WHERE delivery_id IN (SELECT id FROM deliveries WHERE delivery_status='Delivered')");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>User Dashboard</title>
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
  color: white !important;
  font-weight: 600;
}
.welcome {
  display: flex; align-items: center;
  justify-content: space-between; flex-wrap: wrap;
}
.badge { font-size: 0.9em; }
.payment-info {
  background: #e0f7ec;
  border-left: 4px solid #10b981;
  padding: 10px 15px;
  border-radius: 8px;
  margin-top: 5px;
  font-size: 0.9em;
}
.payment-info strong { color: #065f46; }
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
  <a href="dashboard.php" class="active">💻 Dashboard</a>
  <a href="user_request.php">📄 Document Request</a>
  <a href="history.php">📜 History</a>
  <a href="reports.php">📊 Reports</a>
  <a href="feedback.php">💬 Feedback</a>
  <a href="announcement.php">📢 Announcements</a>
  <a href="contact.php">📞 Contact</a>
  <a href="logout.php" class="logout"> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="card p-4 mb-4">
    <div class="welcome">
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 🎉</h1>
      <p class="text-secondary">Barangay Document & Services Portal</p>
    </div>
  </div>

  <!-- Announcements Section -->
  <div class="card p-4 mb-4">
    <h2>📢 Latest Announcements</h2>
    <hr>
    <?php
    if ($announcements && $announcements->num_rows > 0) {
        while($a = $announcements->fetch_assoc()){
         echo "<h5>".htmlspecialchars($a['title'])."</h5>";
         echo "<p>".htmlspecialchars($a['content'])."</p>";
         echo "<small class='text-muted'>Posted on: " . date('F j, Y h:i A', strtotime($a['created_at'])) . "</small><hr>";
        }
    } else {
        echo "<p class='text-muted'>No new announcements yet. Please check back later.</p>";
    }
    ?>
  </div>

  <!-- Deliveries Chat Section -->
  <?php while($d = $deliveries->fetch_assoc()): ?>
    <?php if($d['delivery_status'] === 'In Transit'): ?>
      <div class="card p-3 mb-3">
        <h4>💬 Chat with Rider: <?= htmlspecialchars($d['rider_name']) ?></h4>
        <div id="user-chat-box-<?= $d['id'] ?>" style="height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; border-radius:8px;"></div>
        <form class="user-chat-form" data-delivery-id="<?= $d['id'] ?>">
          <input type="text" name="message" placeholder="Type a message..." style="width:70%; padding:6px; border-radius:6px; border:1px solid #ccc;">
          <button type="submit" style="padding:6px 10px; border:none; background:#2563eb; color:white; border-radius:6px;">Send</button>
          <input type="hidden" name="sender_name" value="<?= htmlspecialchars($username) ?>">
          <input type="hidden" name="sender" value="user">
        </form>
      </div>
    <?php endif; ?>
  <?php endwhile; ?>

  <!-- Document Requests Table -->
  <div class="card p-4">
    <h2>📄 My Document Requests</h2>
    <hr>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Document</th>
          <th>Amount</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
          // Get latest delivery status
          $delivery_status = '';
          $stmt2 = $conn->prepare("SELECT delivery_status FROM deliveries WHERE request_id=? ORDER BY id DESC LIMIT 1");
          $stmt2->bind_param("i", $row['id']);
          $stmt2->execute();
          $res2 = $stmt2->get_result();
          if ($res2 && $res2->num_rows > 0) {
              $delivery = $res2->fetch_assoc();
              $delivery_status = $delivery['delivery_status'];
          }
          $stmt2->close();

          // Determine final status
          $final_status = $row['status'];
          if ($delivery_status === 'Delivered') {
              $final_status = 'Completed';
          } elseif ($delivery_status === 'In Transit') {
              $final_status = 'In Transit';
          }

          $isPaid = in_array(strtolower($final_status), ['paid', 'completed']);
          ?>
          <tr>
            <td><?= htmlspecialchars($row['document_type']) ?></td>
            <td>₱<?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars(date('F j, Y', strtotime($row['request_date']))) ?></td>
            <td>
              <?php
              switch ($final_status) {
                  case 'Pending': echo '<span class="badge bg-secondary">Pending</span>'; break;
                  case 'Approved': echo '<span class="badge bg-success">Approved ✅</span>'; break;
                  case 'Paid': echo '<span class="badge bg-primary">Paid 💳</span>'; break;
                  case 'Completed': echo '<span class="badge bg-info">Delivered ✅</span>'; break;
                  case 'In Transit': echo '<span class="badge bg-warning text-dark">In Transit 🚚</span>'; break;
                  case 'Cancelled': echo '<span class="badge bg-danger">Cancelled</span>'; break;
                  default: echo '<span class="badge bg-light text-dark">Unknown</span>';
              }
              ?>
            </td>
            <td>
              <?php
              if ($row['status'] == 'Approved') {
                  echo '<a href="payment.php?request_id=' . $row['id'] . '" class="btn btn-primary btn-sm">Proceed to Payment</a>';
              } elseif ($row['status'] == 'Pending') {
                  echo '<small class="text-muted">Waiting for admin</small>';
              } elseif ($row['status'] == 'Paid') {
                  echo '<small class="text-success">Payment Done</small>';
              } elseif ($row['status'] == 'Completed') {
                  echo '<small class="text-info">Done</small>';
              }
              ?>
            </td>
          </tr>

          <?php if ($isPaid): ?>
          <tr>
            <td colspan="5">
              <div class="payment-info">
                <strong>✅ Payment Successful</strong><br>
                Method: <?= htmlspecialchars($row['payment_method'] ?? '—') ?><br>
                Reference Number: <?= htmlspecialchars($row['reference_number'] ?? '—') ?><br>
                Payment Date: <?= $row['payment_date'] ? htmlspecialchars(date('F d, Y', strtotime($row['payment_date']))) : '—' ?><br>
                Amount Paid: ₱<?= number_format($row['amount'], 2) ?><br>
                Status: <?= htmlspecialchars($final_status) ?>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Update Profile Section -->
<div class="card p-4 mb-4">
  <h2>👤 Update Profile</h2>
  <hr>
  <form action="update_profile.php" method="POST" style="max-width:400px;">
    <div class="mb-3">
      <label class="form-label">New Username</label>
      <input type="text" name="new_username" class="form-control" placeholder="Enter new username">
      <small class="text-muted">You can only change your username twice.</small>
    </div>

    <div class="mb-3">
      <label class="form-label">New Password</label>
      <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
      <small class="text-muted">You can only change your password twice.</small>
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
  </form>
</div>

<script>
setInterval(() => {
    fetch('check_approval.php')
    .then(res => res.json())
    .then(data => {
        if (data.approved_request_id) {
            window.location.href = 'payment.php?request_id=' + data.approved_request_id;
        }
    });
}, 5000);

// ✅ Chat logic (for each delivery chat box)
document.querySelectorAll('.user-chat-form').forEach(form => {
    const deliveryId = form.getAttribute('data-delivery-id');
    const chatBox = document.getElementById('user-chat-box-' + deliveryId);

    function fetchMessages() {
        fetch('fetch_chat.php?delivery_id=' + deliveryId)
        .then(res => res.json())
        .then(data => {
            chatBox.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.innerHTML = `<strong>${msg.sender_name}:</strong> ${msg.message} <small style="color:#888;">[${msg.timestamp}]</small>`;
                chatBox.appendChild(div);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    setInterval(fetchMessages, 2000);

    form.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('delivery_id', deliveryId);

        fetch('send_chat.php', { method: 'POST', body: formData })
        .then(() => {
            form.message.value = '';
            fetchMessages();
        });
    });
}); 
</script>

</div>
</body>
</html>
