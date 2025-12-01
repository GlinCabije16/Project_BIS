<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch transaction history with payment info (joined from payments)
$query = "
    SELECT dr.id, dr.document_type, dr.amount, dr.status AS doc_status, dr.request_date, 
           p.payment_date, p.reference_number, p.payment_method, p.payment_status
    FROM document_requests dr
    LEFT JOIN payments p ON dr.id = p.request_id
    WHERE dr.username = ?
    ORDER BY dr.request_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>History Transaction</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { font-family: 'Poppins', sans-serif; background-color: #f1f5f9; margin:0; }
.sidebar { position: fixed; top:0; left:0; width:250px; height:100%; background: linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)), url('images/logo.png') center center fixed; color:#fff; padding-top:20px;}
.sidebar h2 { text-align:center; margin-bottom:40px;}
.sidebar a { display:block; padding:12px 20px; color:#e2e8f0; text-decoration:none; margin:5px 10px; border-radius:6px;}
.sidebar a:hover, .sidebar a.active { background:#60a5fa; color:white;}
.main-content { margin-left:250px; padding:30px;}
.card { border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.1); background:white; padding:25px;}
.badge { font-size:0.9em;}
.logout { color:#f87171 !important; font-weight:600;}
.payment-info { background:#e0f7ec; border-left:4px solid #10b981; padding:10px 15px; border-radius:8px; margin-top:5px; font-size:0.9em;}
.payment-info strong { color:#065f46; }
</style>
</head>
<body>

<div class="sidebar">
  <h2>🏡 Barangay System</h2>
  <a href="dashboard.php">💻 Dashboard</a>
  <a href="user_request.php">📄 Document Request</a>
  <a href="history.php" class="active">📜 History</a>
  <a href="reports.php">📊 Reports</a>
  <a href="feedback.php">💬 Feedback</a>
  <a href="announcement.php">📢 Announcements</a>
  <a href="contact.php">📞 Contact</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main-content">
  <div class="card">
    <h2>📜 Transaction History</h2>
    <hr>
    <table class="table table-striped table-hover align-middle">
      <thead>
        <tr>
          <th>Document Type</th>
          <th>Amount</th>
          <th>Request Date</th>
          <th>Payment Date</th>
          <th>Reference No.</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
              $status = $row['payment_status'] ?? $row['doc_status'];
              $status = ucfirst(strtolower($status));
              $isPaid = in_array(strtolower($status), ['paid', 'completed']);
            ?>
            <tr>
              <td><?= htmlspecialchars($row['document_type']) ?></td>
              <td>₱<?= number_format($row['amount'],2) ?></td>
              <td><?= date('F d, Y', strtotime($row['request_date'])) ?></td>
              <td><?= $row['payment_date'] ? date('F d, Y', strtotime($row['payment_date'])) : '<span class="text-muted">—</span>' ?></td>
              <td><?= $row['reference_number'] ? htmlspecialchars($row['reference_number']) : '<span class="text-muted">N/A</span>' ?></td>
              <td>
                <?php
                  switch ($status) {
                      case 'Pending': echo '<span class="badge bg-secondary">Pending</span>'; break;
                      case 'Approved': echo '<span class="badge bg-success">Approved</span>'; break;
                      case 'Paid': echo '<span class="badge bg-primary">Paid</span>'; break;
                      case 'Completed': echo '<span class="badge bg-info">Completed</span>'; break;
                      case 'Cancelled': echo '<span class="badge bg-danger">Cancelled</span>'; break;
                      default: echo '<span class="badge bg-light text-dark">Unknown</span>';
                  }
                ?>
              </td>
            </tr>
            <?php if ($isPaid): ?>
            <tr>
              <td colspan="6">
                <div class="payment-info">
                  <strong>✅ Payment Successful</strong><br>
                  Method: <?= htmlspecialchars($row['payment_method'] ?? '—') ?><br>
                  Reference Number: <?= htmlspecialchars($row['reference_number'] ?? '—') ?><br>
                  Payment Date: <?= $row['payment_date'] ? date('F d, Y', strtotime($row['payment_date'])) : '—' ?><br>
                  Amount Paid: ₱<?= number_format($row['amount'],2) ?><br>
                  Status: <?= htmlspecialchars($status) ?>
                </div>
              </td>
            </tr>
            <?php endif; ?>
          <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center text-muted">No transaction history found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
