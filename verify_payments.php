<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle verification
if (isset($_POST['verify_id'])) {
    $id = intval($_POST['verify_id']);
    $stmt = $conn->prepare("UPDATE payments SET payment_status='Completed', payment_date=NOW() WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: verify_payments.php");
    exit();
}

// Handle rejection
if (isset($_POST['reject_id'])) {
    $id = intval($_POST['reject_id']);
    $stmt = $conn->prepare("UPDATE payments SET payment_status='Failed' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: verify_payments.php");
    exit();
}

// Handle deletion
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM payments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: verify_payments.php");
    exit();
}

// Handle method/amount update
if (isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $amount = floatval($_POST['amount']);
    $method = $_POST['payment_method'];
    $stmt = $conn->prepare("UPDATE payments SET amount=?, payment_method=? WHERE id=?");
    $stmt->bind_param("dsi", $amount, $method, $id);
    $stmt->execute();
    header("Location: verify_payments.php");
    exit();
}

// Fetch pending payments
$sql = "SELECT p.*, u.username, d.document_type 
        FROM payments p
        LEFT JOIN users u ON p.username=u.username
        LEFT JOIN document_requests d ON p.request_id=d.id
        ORDER BY p.payment_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Verify Payments</title>
<link rel="icon" href="images/lgo.png" type="image/x-icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    font-family: 'Poppins', sans-serif; 
        background: linear-gradient(135deg, rgba(45, 126, 255, 0.85), rgba(106,17,203,0.85)),
                url('images/logo.png') center/cover no-repeat fixed;
    padding: 40px; 
}
.container { background: #fff; border-radius: 15px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.2);}
h2 { color: #4b3bff; margin-bottom: 20px; text-align:center; }
table { background: #f9f9f9; border-radius: 10px; overflow: hidden; }
th, td { text-align: center; vertical-align: middle !important; }
img.proof { max-width: 80px; border-radius: 6px; }
.btn-verify { background-color: #2563eb; color: white; border: none; border-radius: 6px; padding: 5px 10px; margin: 2px; transition: 0.3s; }
.btn-verify:hover { background-color: #1e40af; }
.btn-reject { background-color: #ef4444; color: white; border: none; border-radius: 6px; padding: 5px 10px; margin: 2px; transition: 0.3s; }
.btn-reject:hover { background-color: #b91c1c; }
.btn-delete { background-color: #6b7280; color: white; border: none; border-radius: 6px; padding: 5px 10px; margin: 2px; transition: 0.3s; }
.btn-delete:hover { background-color: #4b5563; }
.btn-update { background-color: #10b981; color: white; border: none; border-radius: 6px; padding: 5px 10px; margin: 2px; transition: 0.3s; }
.btn-update:hover { background-color: #047857; }
.badge-pending { background-color: #f59e0b; }
.badge-completed { background-color: #10b981; }
.badge-failed { background-color: #ef4444; }
.back-btn { background: #6b7280; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; margin-bottom: 20px; display: inline-block; transition: 0.3s; }
.back-btn:hover { background: #4b5563; }
input.amount, select.method { width: 90px; text-align: center; border-radius: 6px; border: 1px solid #ccc; padding: 2px; }
</style>
</head>
<body>

<div class="container">
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>📌 Verify User Payments</h2>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Document</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Proof</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count=1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['document_type']) ?></td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                            <input type="number" step="0.01" name="amount" class="amount" value="<?= $row['amount'] ?>">
                            <button type="submit" class="btn-update">Update</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                            <select name="payment_method" class="method">
                                <option value="Gcash" <?= $row['payment_method']=='Gcash'?'selected':'' ?>>Gcash</option>
                                <option value="Cash" <?= $row['payment_method']=='Cash'?'selected':'' ?>>Cash</option>
                                <option value="Bank Transfer" <?= $row['payment_method']=='Bank Transfer'?'selected':'' ?>>Bank Transfer</option>
                            </select>
                            <button type="submit" class="btn-update">Update</button>
                        </form>
                    </td>
                    <td><?= htmlspecialchars($row['reference_number'] ?? '-') ?></td>
                    <td>
                        <?php if($row['proof_image']): ?>
                            <a href="uploads/<?= $row['proof_image'] ?>" target="_blank">
                                <img src="uploads/<?= $row['proof_image'] ?>" class="proof">
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['payment_status']=='Pending'): ?>
                            <span class="badge badge-pending">Pending</span>
                        <?php elseif($row['payment_status']=='Completed'): ?>
                            <span class="badge badge-completed">Completed</span>
                        <?php else: ?>
                            <span class="badge badge-failed">Failed</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['payment_status']=='Pending'): ?>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="verify_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn-verify">Verify</button>
                            </form>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="reject_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn-reject">Reject</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this payment?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted" style="text-align:center;">No payments available at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
