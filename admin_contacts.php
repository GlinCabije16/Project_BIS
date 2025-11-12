<?php
session_start();
require 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_contacts.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_id'])) {
    $id = intval($_POST['contact_id']);
    $user_message = trim($_POST['user_message']);      // editable user message
    $admin_reply = trim($_POST['admin_reply']);        // admin reply
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE contacts SET message=?, admin_reply=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $user_message, $admin_reply, $status, $id);
    $stmt->execute();
    $success = "Contact updated successfully!";
}

// Fetch all contacts
$result = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Admin - Manage Contacts</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body {
    font-family: 'Poppins', sans-serif;
  background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
    color: #fff;
    min-height: 100vh;
    padding: 30px 0;
}
.container {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
h2 {
    color: #fff;
    font-weight: 600;
}
.dashboard-btn {
    margin-bottom: 20px;
}
.table {
    background: rgba(255,255,255,0.05);
    border-radius: 15px;
    overflow: hidden;
}
.table thead {
    background: linear-gradient(90deg, #2575fc, #6a11cb);
    color: #fff;
}
.table tbody tr {
    transition: transform 0.2s, box-shadow 0.2s;
}
.table tbody tr:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}
textarea {
    resize: none;
    border-radius: 10px;
    padding: 5px;
    background: rgba(255,255,255,0.8);
}
select.form-select {
    border-radius: 10px;
}
.btn-success, .btn-danger {
    border-radius: 10px;
    transition: 0.3s;
}
.btn-success:hover {
    background: #28a745cc;
    transform: scale(1.05);
}
.btn-danger:hover {
    background: #dc3545cc;
    transform: scale(1.05);
}
.status-badge {
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 12px;
    color: #fff;
}
.status-pending { background: orange; }
.status-replied { background: #28a745; }
.alert-success {
    border-radius: 12px;
}
</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📞 Manage User Contacts</h2>
        <a href="admin_dashboard.php" class="btn btn-light dashboard-btn">🏠 Dashboard</a>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-hover text-dark">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>User Message</th>
                <th>Status</th>
                <th>Admin Reply</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <textarea name="user_message" class="form-control" rows="2"><?= htmlspecialchars($row['message']) ?></textarea>
                    </td>
                    <td>
                        <select name="status" class="form-select">
                            <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                            <option value="Replied" <?= $row['status']=='Replied'?'selected':'' ?>>Replied</option>
                        </select>
                        <div class="status-badge <?= strtolower($row['status']) ?> mt-1"><?= $row['status'] ?></div>
                    </td>
                    <td>
                        <textarea name="admin_reply" class="form-control" rows="2"><?= htmlspecialchars($row['admin_reply']) ?></textarea>
                    </td>
                    <td>
                        <input type="hidden" name="contact_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm mb-1">Save</button>
                        <a href="admin_contacts.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
