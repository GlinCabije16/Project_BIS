<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Delete transaction
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM payments WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  $stmt->close();
  header("Location: view_transactions.php");
  exit();
}

// Edit transaction
if(isset($_POST['edit_id'])) {
  $id = intval($_POST['edit_id']);
  $amount = floatval($_POST['amount']);
  $method = $_POST['payment_method'];
  $driver = $_POST['driver'];

  $stmt = $conn->prepare("UPDATE payments SET amount=?, payment_method=?, driver=? WHERE id=?");
  // Correct bind_param types: d=double, s=string, s=string, i=integer
  $stmt->bind_param("dssi", $amount, $method, $driver, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: view_transactions.php");
  exit();
}

// Fetch transactions
$result = $conn->query("SELECT p.*, d.document_type, d.username as request_user
                        FROM payments p
                        JOIN document_requests d ON p.request_id=d.id
                        ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Transactions</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6fc; margin:0; padding:0; }
header { background: linear-gradient(135deg, #2575fc, #6a11cb); padding:15px 40px; color:white; display:flex; justify-content:space-between; align-items:center;}
header h1 { font-size:22px; }
a.nav-link { color:white; text-decoration:none; margin-left:20px; font-weight:500; }
a.nav-link:hover { text-decoration:underline; }
.container { padding:40px; }
table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.1);}
th, td { padding:12px 15px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#2575fc; color:white; text-transform:uppercase; font-size:14px; }
tr:hover { background:#f1f1f1; }
.btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; font-weight:500; transition:0.3s; }
.btn-delete { background:#ef4444; color:white; }
.btn-delete:hover { background:#dc2626; }
.btn-edit { background:#22c55e; color:white; }
.btn-edit:hover { background:#16a34a; }
input, select { padding:4px; border-radius:4px; border:1px solid #ccc; }
</style>
</head>
<body>
<header>
<h1>💳 Payment Transactions</h1>
<nav>
  <a class="nav-link" href="admin_dashboard.php">🏠 Dashboard</a>
  <a class="nav-link" href="logout.php">🚪 Logout</a>
</nav>
</header>

<div class="container">
<h2>Transaction History</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>User</th>
<th>Document</th>
<th>Amount</th>
<th>Payment Method</th>
<th>Driver</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if($result->num_rows>0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['request_user']) ?></td>
<td><?= htmlspecialchars($row['document_type']) ?></td>
<td>
<form method="POST" style="display:inline-block;">
<input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
<input type="number" step="0.01" name="amount" value="<?= $row['amount'] ?>" style="width:70px">
</td>
<td>
<select name="payment_method">
<option value="GCash" <?= $row['payment_method']=="GCash"?"selected":"" ?>>GCash</option>
<option value="COD" <?= $row['payment_method']=="COD"?"selected":"" ?>>COD</option>
<option value="Cash on Pickup" <?= $row['payment_method']=="Cash on Pickup"?"selected":"" ?>>Cash on Pickup</option>
</select>
</td>
<td><input type="text" name="driver" value="<?= htmlspecialchars($row['driver'] ?? '') ?>"></td>
<td><?= $row['payment_date'] ?></td>
<td>
<button type="submit" class="btn btn-edit">Update</button>
</form>
<a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this transaction?')">
<button class="btn btn-delete">Delete</button>
</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8">No transactions found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</body>
</html>
