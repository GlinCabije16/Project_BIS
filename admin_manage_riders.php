<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// --- Handle Add Rider ---
if (isset($_POST['add_rider'])) {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $status = $_POST['status'] ?? 'Active';

    $stmt = $conn->prepare("INSERT INTO riders (fullname, email, contact, status, profile_pic) VALUES (?, ?, ?, ?, 'default_profile.png')");
    $stmt->bind_param("ssss", $fullname, $email, $contact, $status);
    $stmt->execute();

    $_SESSION['toast'] = "✅ Rider added successfully!";
    header("Location: admin_manage_riders.php");
    exit();
}

// --- Handle Update Rider Status ---
if (isset($_POST['update_rider'])) {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? 'Active';
    $stmt = $conn->prepare("UPDATE riders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    $_SESSION['toast'] = "✅ Rider status updated!";
    header("Location: admin_manage_riders.php");
    exit();
}

// --- Handle Delete Rider ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM riders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['toast'] = "🗑️ Rider deleted successfully!";
    header("Location: admin_manage_riders.php");
    exit();
}

// --- Handle Approve/Reject Profile Update ---
if (isset($_POST['approve_update'])) {
    $update_id = $_POST['update_id'];
    $stmt = $conn->prepare("SELECT * FROM rider_profile_updates WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $update_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $rider_id = $result['rider_id'];
        $new_fullname = $result['new_fullname'];
        $new_contact = $result['new_contact'];
        $new_password = $result['new_password'];
        $new_profile_pic = $result['new_profile_pic'] ?? null;

        $updateStmt = $conn->prepare("UPDATE riders SET fullname=?, contact=?, password=COALESCE(?, password), profile_pic=COALESCE(?, profile_pic) WHERE id=?");
        $updateStmt->bind_param("ssssi", $new_fullname, $new_contact, $new_password, $new_profile_pic, $rider_id);
        $updateStmt->execute();

        $conn->query("UPDATE rider_profile_updates SET status='Approved' WHERE id=$update_id");
        $_SESSION['toast'] = "✅ Profile update approved!";
    }
    header("Location: admin_manage_riders.php");
    exit();
}

if (isset($_POST['reject_update'])) {
    $update_id = $_POST['update_id'];
    $conn->query("UPDATE rider_profile_updates SET status='Rejected' WHERE id=$update_id");
    $_SESSION['toast'] = "❌ Profile update rejected!";
    header("Location: admin_manage_riders.php");
    exit();
}

// --- Fetch All Riders ---
$riders = $conn->query("SELECT * FROM riders ORDER BY id DESC");

// --- Fetch Pending Updates ---
$updates = $conn->query("
    SELECT rpu.*, r.fullname AS current_name, r.contact AS current_contact, r.profile_pic AS current_pic
    FROM rider_profile_updates rpu
    JOIN riders r ON r.id = rpu.rider_id
    WHERE rpu.status='Pending'
    ORDER BY rpu.request_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Manage Riders</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    margin:0;
    font-family:'Poppins', sans-serif;
    background: white;
    color:#333;
}

/* Sidebar */
.sidebar {
    width:250px; height:100vh;
     background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
    position:fixed; display:flex; flex-direction:column;
    padding:25px 20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.3);
    transition:transform 0.3s ease-in-out;
}
.sidebar h2 { font-weight:600; margin-bottom:20px; color:#e5e7eb; }
.sidebar a {
    color:#ccc; text-decoration:none; margin:10px 0; padding:10px;
    border-radius:8px; transition:0.3s;
    display:flex; align-items:center; gap:8px;
}
.sidebar a:hover, .sidebar a.active { background:#2563eb; color:#fff; }
.logout-btn {
    margin-top:auto; background:#ef4444; color:#fff; border:none;
    padding:10px; border-radius:8px; cursor:pointer; font-weight:500;
}
.logout-btn:hover { background:#b91c1c; }

/* Main Content */
.main-content {
    margin-left:270px; padding:30px;
    transition:margin-left 0.3s ease-in-out;
}
header {
    display:flex; justify-content:space-between; align-items:center;
    flex-wrap:wrap; gap:10px;
}
h1 {
    font-size:26px; color:#111827;
}
a.back-link { color:#2563eb; text-decoration:none; font-weight:500; }
a.back-link:hover { text-decoration:underline; }

/* Container */
.container {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 16px rgba(0,0,0,0.08);
    margin-bottom:30px;
    overflow-x:auto;
    transition:0.3s;
}
.container:hover {
    transform:scale(1.01);
}

/* Add Form */
.add-form {
    display:flex; flex-wrap:wrap; gap:10px; margin-bottom:15px;
}
.add-form input, .add-form select {
    padding:10px 12px; border-radius:8px; border:1px solid #ccc;
    flex:1; min-width:150px;
}
.add-form button {
    background:#10b981; color:#fff; border:none;
    border-radius:8px; padding:10px 15px; cursor:pointer;
    transition:0.3s;
}
.add-form button:hover { background:#059669; transform:scale(1.05); }

/* Tables */
table { width:100%; border-collapse:collapse; margin-top:15px; min-width:600px; }
th, td { padding:12px; border-bottom:1px solid #e5e7eb; text-align:center; }
th { background:#2563eb; color:#fff; }
tr:hover { background:#f9fafb; transition:0.2s; }
.profile-pic {
    width:45px; height:45px; border-radius:50%; object-fit:cover; border:2px solid #ddd;
}

/* Buttons */
.btn {
    padding:6px 10px; border:none; border-radius:6px; cursor:pointer;
    color:#fff; font-size:14px; transition:0.2s;
}
.btn-edit { background:#10b981; }
.btn-edit:hover { background:#059669; }
.btn-delete { background:#ef4444; }
.btn-delete:hover { background:#b91c1c; }
.btn-approve { background:#2563eb; }
.btn-approve:hover { background:#1d4ed8; }
.btn-reject { background:#f59e0b; }
.btn-reject:hover { background:#b45309; }

/* Toast */
.toast {
    position:fixed; top:20px; right:20px;
    background:#111827; color:#fff;
    padding:12px 18px; border-radius:8px;
    opacity:0; transform:translateY(-20px);
    transition:0.5s; z-index:1000;
}
.toast.show { opacity:1; transform:translateY(0); }

/* ✅ Responsive */
@media (max-width:900px) {
    .sidebar { transform:translateX(-100%); }
    .main-content { margin-left:0; padding:20px; }
}
@media (max-width:600px) {
    table { font-size:13px; }
    th, td { padding:8px; }
    .add-form { flex-direction:column; }
    .add-form input, .add-form select, .add-form button { width:100%; }
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>🚴‍♂️ Admin Panel</h2>
  <a href="admin_dashboard.php">🏠 Dashboard</a>
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
  <form method="post" action="logout.php">
    <button type="submit" class="logout-btn">🚪 Logout</button>
  </form>
</div>

<div class="main-content">
<header>
    <h1>Manage Riders</h1>
    <a href="admin_dashboard.php" class="back-link">← Back</a>
</header>

<div class="container">
    <form method="POST" class="add-form">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="contact" placeholder="Contact Number" required>
        <select name="status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <button type="submit" name="add_rider">+ Add Rider</button>
    </form>

    <table>
        <tr>
            <th>Profile</th><th>ID</th><th>Full Name</th><th>Email</th>
            <th>Contact</th><th>Status</th><th>Action</th>
        </tr>
        <?php while($r = $riders->fetch_assoc()): ?>
        <tr>
            <td><img class="profile-pic" src="<?= htmlspecialchars($r['profile_pic'] ?? 'default_profile.png') ?>" alt="Profile"></td>
            <td><?= $r['id']; ?></td>
            <td><?= htmlspecialchars($r['fullname']); ?></td>
            <td><?= htmlspecialchars($r['email']); ?></td>
            <td><?= htmlspecialchars($r['contact']); ?></td>
            <td>
                <form method="POST" style="display:flex; align-items:center; gap:5px; justify-content:center;">
                    <input type="hidden" name="id" value="<?= $r['id']; ?>">
                    <select name="status">
                        <option value="Active" <?= ($r['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= ($r['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <button type="submit" name="update_rider" class="btn btn-edit">Update</button>
                </form>
            </td>
            <td><a href="?delete=<?= $r['id']; ?>" onclick="return confirm('Delete this rider?');" class="btn btn-delete">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php if($updates && $updates->num_rows > 0): ?>
<div class="container">
    <h2>Pending Profile Update Requests</h2>
    <table>
        <tr>
            <th>Current Pic</th>
            <th>Current Name</th>
            <th>New Name</th>
            <th>Current Contact</th>
            <th>New Contact</th>
            <th>Action</th>
        </tr>
        <?php while($u = $updates->fetch_assoc()): ?>
        <tr>
            <td><img class="profile-pic" src="<?= htmlspecialchars($u['current_pic'] ?? 'default_profile.png'); ?>" alt="Profile"></td>
            <td><?= htmlspecialchars($u['current_name']); ?></td>
            <td><?= htmlspecialchars($u['new_fullname']); ?></td>
            <td><?= htmlspecialchars($u['current_contact']); ?></td>
            <td><?= htmlspecialchars($u['new_contact']); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="update_id" value="<?= $u['id']; ?>">
                    <button type="submit" name="approve_update" class="btn btn-approve">Approve</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="update_id" value="<?= $u['id']; ?>">
                    <button type="submit" name="reject_update" class="btn btn-reject">Reject</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php endif; ?>

<div class="toast" id="toast"></div>
<script>
<?php if(isset($_SESSION['toast'])): ?>
let toast = document.getElementById('toast');
toast.innerText = "<?= $_SESSION['toast']; ?>";
toast.classList.add('show');
setTimeout(() => toast.classList.remove('show'), 3000);
<?php unset($_SESSION['toast']); endif; ?>
</script>

</div>
</body>
</html>
