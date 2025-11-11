<?php
session_start();
require 'database.php';

// Check session
if (!isset($_SESSION['rider']) || !isset($_SESSION['rider_name'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_username = $_SESSION['rider'];
$rider_name = $_SESSION['rider_name'];

// Fetch rider profile
$stmtR = $conn->prepare("SELECT * FROM riders WHERE username = ? LIMIT 1");
$stmtR->bind_param("s", $rider_username);
$stmtR->execute();
$rider = $stmtR->get_result()->fetch_assoc();
$stmtR->close();

// Fetch deliveries
$stmt = $conn->prepare("SELECT * FROM deliveries WHERE (rider_name = ? OR rider_name IS NULL OR rider_name='Unassigned') ORDER BY delivery_date DESC");
$stmt->bind_param("s", $rider_name);
$stmt->execute();
$deliveries = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rider Dashboard 🚴‍♂️</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Reset & Body */
body, html { margin:0; padding:0; font-family:'Poppins', sans-serif; background:#f0f2f5; height:100vh; }
* { box-sizing:border-box; }

/* Sidebar */
.sidebar {
  width:250px;background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
                  url('images/logo.png') center/cover no-repeat fixed;; color:white;
  position:fixed; top:0; left:0; bottom:0; display:flex; flex-direction:column;
  transition:0.3s;
}
.sidebar h2 { text-align:center; padding:20px 0; font-size:22px; }
.sidebar a {
  display:flex; align-items:center; padding:15px 25px; color:white; text-decoration:none;
  transition:0.3s; font-weight:500;
}

.sidebar a i { margin-right:12px; font-size:18px; }
.sidebar a:hover, .sidebar a.active { background:#1a54b2; }

/* Main content */
.main { margin-left:250px; padding:20px; transition:0.3s; }

/* Header */
header {
  display:flex; justify-content:space-between; align-items:center;
  background:white; padding:12px 20px; border-radius:8px;
  box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:20px;
}
header h1 { font-size:20px; color:#2575fc; }
header .profile {
  display:flex; align-items:center; cursor:pointer;
}
header .profile img { width:40px; height:40px; border-radius:50%; margin-right:10px; }
header .profile span { font-weight:500; }

/* Cards */
.card {
  background:white; border-radius:12px; padding:20px; margin-bottom:20px;
  box-shadow:0 5px 20px rgba(0,0,0,0.08); transition:0.3s; position:relative;
}
.card:hover { transform:translateY(-5px); }

/* Status */
.status {
  display:inline-block; padding:5px 12px; border-radius:8px; font-weight:600; font-size:13px;
}
.Pending { background:#fff3cd; color:#856404; }
.InTransit { background:#cfe8ff; color:#0b4ea2; }
.Delivered { background:#d4edda; color:#155724; }

/* Buttons */
button {
  padding:8px 12px; border:none; border-radius:8px; font-weight:600; cursor:pointer;
  transition:0.3s;
}
.ClaimBtn { background:#28a745; color:white; }
.ClaimBtn:hover { background:#218838; }
.ActionBtn { background:#2563eb; color:white; }
.ActionBtn:hover { background:#1e4bb8; }

/* Profile Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:30px; border-radius:12px; width:400px; max-width:90%; position:relative; }
.modal-content h3 { margin-top:0; color:#2575fc; }
.modal-content label { display:block; margin-top:10px; font-weight:500; }
.modal-content input { width:100%; padding:8px 10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; }
.modal-content button { margin-top:15px; width:100%; }
.modal-close { position:absolute; top:12px; right:15px; cursor:pointer; font-size:18px; }

/* Chatbox */
.chat-box { height:120px; overflow-y:auto; border:1px solid #ccc; padding:10px; border-radius:8px; background:#f9f9f9; margin-top:10px; }
.chat-input { display:flex; margin-top:5px; }
.chat-input input { flex:1; padding:6px; border-radius:6px; border:1px solid #ccc; }
.chat-input button { padding:6px 10px; margin-left:5px; border:none; background:#2563eb; color:white; border-radius:6px; cursor:pointer; }
.chat-input button:hover { background:#1e4bb8; }

/* Responsive */
@media(max-width:768px){
  .sidebar { width:60px; }
  .sidebar a span { display:none; }
  .main { margin-left:60px; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2><i class="fas fa-bicycle"></i> Rider</h2>
  <a href="#deliveries" class="active"><i class="fas fa-box"></i> <span>Deliveries</span></a>
  <a href="#" id="profileBtn"><i class="fas fa-user"></i> <span>Profile</span></a>
  <a href="rider_logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
</div>

<!-- Main Content -->
<div class="main">
<header>
  <h1>Welcome, <?= htmlspecialchars($rider_name) ?> 🚴‍♂️</h1>
  <div class="profile">
    <img src="<?= htmlspecialchars($rider['profile_pic'] ?? 'https://via.placeholder.com/40') ?>" alt="Profile Pic">
    <span><?= htmlspecialchars($rider_name) ?></span>
  </div>
</header>

<!-- Deliveries Section -->
<section id="deliveries">
  <?php if ($deliveries->num_rows == 0): ?>
    <p>No deliveries available.</p>
  <?php else: ?>
    <?php while($row = $deliveries->fetch_assoc()): ?>
      <div class="card">
        <h3>Request ID: <?= htmlspecialchars($row['request_id'] ?? $row['id']) ?></h3>
        <p><strong>Recipient:</strong> <?= htmlspecialchars($row['recipient_name']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?></p>
        <p><strong>Rider:</strong> <?= htmlspecialchars($row['rider_name'] ?? 'Unassigned') ?></p>
        <span class="status <?= str_replace(' ','',$row['delivery_status']) ?>"><?= htmlspecialchars($row['delivery_status']) ?></span>

        <?php if (($row['rider_name']=='Unassigned'||empty($row['rider_name'])) && $row['delivery_status']=='Pending'): ?>
          <form action="update_delivery.php" method="POST">
            <input type="hidden" name="action" value="claim">
            <input type="hidden" name="rider_name" value="<?= htmlspecialchars($rider_name) ?>">
            <input type="hidden" name="delivery_id" value="<?= htmlspecialchars($row['id']) ?>">
            <button type="submit" class="ClaimBtn">Claim Delivery</button>
          </form>
        <?php elseif($row['rider_name']==$rider_name && $row['delivery_status']=='In Transit'): ?>
          <form action="update_delivery.php" method="POST">
            <input type="hidden" name="action" value="delivered">
            <input type="hidden" name="delivery_id" value="<?= htmlspecialchars($row['id']) ?>">
            <button type="submit" class="ActionBtn">Mark as Delivered ✅</button>
          </form>

          <!-- Chatbox -->
          <div>
            <h4>💬 Chat with User</h4>
            <div class="chat-box" id="chat-box-<?= $row['id'] ?>"></div>
            <form class="chat-input" data-delivery-id="<?= $row['id'] ?>">
              <input type="text" name="message" placeholder="Type a message..." required>
              <button type="submit">Send</button>
              <input type="hidden" name="sender" value="rider">
              <input type="hidden" name="sender_name" value="<?= htmlspecialchars($rider_name) ?>">
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</section>

<!-- Trash Section -->
<section id="trash">
  <h2>🗑️ Deleted / Delivered Orders</h2>
  <?php
    $stmtTrash = $conn->prepare("SELECT * FROM deliveries WHERE rider_name=? AND is_deleted=1 ORDER BY delivery_date DESC");
    $stmtTrash->bind_param("s", $rider_name);
    $stmtTrash->execute();
    $trash = $stmtTrash->get_result();
    if ($trash->num_rows == 0):
  ?>
    <p>No deleted or delivered orders yet.</p>
  <?php else: ?>
    <?php while($row = $trash->fetch_assoc()): ?>
      <div class="card">
        <h3>Request ID: <?= htmlspecialchars($row['request_id'] ?? $row['id']) ?></h3>
        <p><strong>Recipient:</strong> <?= htmlspecialchars($row['recipient_name']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($row['delivery_status']) ?></p>
        <form action="update_delivery.php" method="POST">
          <input type="hidden" name="action" value="restore">
          <input type="hidden" name="delivery_id" value="<?= htmlspecialchars($row['id']) ?>">
          <button type="submit" class="ActionBtn">♻️ Restore</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</section>


</div>

<!-- Profile Modal -->
<div class="modal" id="profileModal">
  <div class="modal-content">
    <span class="modal-close" id="closeModal">&times;</span>
    <h3>Update Profile</h3>
    <form method="post" action="request_profile_update.php" enctype="multipart/form-data">
      <label>Profile Picture</label>
      <input type="file" name="profile_pic" accept="image/*">
      <label>Full Name</label>
      <input type="text" name="fullname" value="<?= htmlspecialchars($rider['fullname'] ?? $rider_name) ?>" required>
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($rider['username'] ?? $rider_username) ?>" required>
      <label>Contact</label>
      <input type="text" name="contact" value="<?= htmlspecialchars($rider['contact'] ?? '') ?>" required>
      <label>New Password</label>
      <input type="password" name="password" placeholder="Leave empty to keep current">
      <button type="submit">Submit Profile Update</button>
    </form>
  </div>
</div>

<script>
// Sidebar Profile Modal
const modal = document.getElementById('profileModal');
document.getElementById('profileBtn').onclick = () => { modal.style.display='flex'; }
document.getElementById('closeModal').onclick = () => { modal.style.display='none'; }
window.onclick = e => { if(e.target==modal){ modal.style.display='none'; } }

// Chat fetch & send logic
document.querySelectorAll('.chat-input').forEach(form => {
  const deliveryId = form.dataset.deliveryId;
  const chatBox = document.getElementById('chat-box-'+deliveryId);
  function fetchMessages() {
    fetch('fetch_chat.php?delivery_id='+deliveryId)
      .then(res=>res.json())
      .then(data=>{
        chatBox.innerHTML='';
        data.forEach(msg=>{
          const div=document.createElement('div');
          div.innerHTML=`<strong>${msg.sender_name}:</strong> ${msg.message} <small style="color:#888;">[${msg.timestamp}]</small>`;
          chatBox.appendChild(div);
        });
        chatBox.scrollTop=chatBox.scrollHeight;
      });
  }
  fetchMessages(); setInterval(fetchMessages,2000);
  form.addEventListener('submit', e=>{
    e.preventDefault();
    const fd=new FormData(form); fd.append('delivery_id',deliveryId);
    fetch('send_chat.php',{method:'POST',body:fd}).then(r=>r.json()).then(resp=>{
      if(resp.status==="success"){ form.message.value=''; fetchMessages(); }
      else{ alert(resp.message||"Error sending message."); }
    });
  });
});
</script>
</body>
</html>
