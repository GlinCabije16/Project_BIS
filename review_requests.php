<?php
session_start();
require 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $action = $_GET['action'];

  if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE document_requests SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('✅ Request approved successfully.'); window.location='review_requests.php';</script>";
    exit();
  } elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE document_requests SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('❌ Request rejected successfully.'); window.location='review_requests.php';</script>";
    exit();
  } elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM document_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('🗑️ Request deleted successfully.'); window.location='review_requests.php';</script>";
    exit();
  }
}

// Handle update
if (isset($_POST['update'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];
  $amount = floatval($_POST['amount']);

  $stmt = $conn->prepare("UPDATE document_requests SET status = ?, amount = ? WHERE id = ?");
  $stmt->bind_param("sdi", $status, $amount, $id);
  $stmt->execute();

  echo "<script>alert('🔄 Request updated successfully.'); window.location='review_requests.php';</script>";
  exit();
}

// Fetch all document requests
$result = $conn->query("SELECT * FROM document_requests ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Review Requests</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #dbeafe, #ede9fe);
      margin: 0;
      padding: 0;
    }

    header {
      background: linear-gradient(90deg, #2575fc, #6a11cb);
      padding: 20px 40px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    header h1 {
      font-size: 24px;
      letter-spacing: 1px;
    }

    a.nav-link {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-weight: 500;
      transition: 0.3s;
    }
    a.nav-link:hover {
      text-decoration: underline;
    }

    .container {
      padding: 50px;
    }

    h2 {
      color: #1e40af;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255, 255, 255, 0.85);
      border-radius: 15px;
      backdrop-filter: blur(8px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: 0.3s ease-in-out;
    }

    th, td {
      padding: 15px 18px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #2575fc;
      color: white;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
    }

    tr:hover {
      background-color: rgba(226, 232, 240, 0.4);
    }

    .btn {
      padding: 8px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      margin: 3px;
    }

    .btn-approve { background-color: #22c55e; color: white; }
    .btn-approve:hover { background-color: #16a34a; transform: scale(1.05); }

    .btn-reject { background-color: #ef4444; color: white; }
    .btn-reject:hover { background-color: #dc2626; transform: scale(1.05); }

    .btn-update { background-color: #3b82f6; color: white; }
    .btn-update:hover { background-color: #1d4ed8; transform: scale(1.05); }

    .btn-delete { background-color: #f97316; color: white; }
    .btn-delete:hover { background-color: #c2410c; transform: scale(1.05); }

    .status {
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 8px;
      display: inline-block;
    }

    .status.Pending { background: #fde68a; color: #78350f; }
    .status.Approved { background: #bbf7d0; color: #065f46; }
    .status.Rejected { background: #fecaca; color: #7f1d1d; }

    form.update-form {
      display: inline;
    }
    input, select {
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-family: 'Poppins', sans-serif;
    }

    .back {
      display: inline-block;
      margin-top: 25px;
      text-decoration: none;
      color: #2575fc;
      font-weight: 600;
      transition: 0.3s;
    }
    .back:hover {
      text-decoration: underline;
      transform: translateX(-3px);
    }
  </style>
</head>
<body>
  <header>
    <h1>📄 Review Document Requests</h1>
    <nav>
      <a class="nav-link" href="admin_dashboard.php">🏠 Dashboard</a>
      <a class="nav-link" href="logout.php">🚪 Logout</a>
    </nav>
  </header>

  <div class="container">
    <h2>Manage Document Requests</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Document Type</th>
          <th>Amount (₱)</th>
          <th>Request Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['document_type']) ?></td>
              <td><?= number_format($row['amount'], 2) ?></td>
              <td><?= htmlspecialchars($row['request_date']) ?></td>
              <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
              <td>
                <?php if ($row['status'] == 'Pending'): ?>
                  <a href="?action=approve&id=<?= $row['id'] ?>"><button class="btn btn-approve">✅ Approve</button></a>
                  <a href="?action=reject&id=<?= $row['id'] ?>"><button class="btn btn-reject">🚫 Reject</button></a>
                <?php endif; ?>

                <form class="update-form" method="POST" action="">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <select name="status">
                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                  </select>
                  <input type="number" name="amount" value="<?= $row['amount'] ?>" step="0.01" min="0">
                  <button type="submit" name="update" class="btn btn-update">💾 Update</button>
                </form>

                <a href="?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this request?');">
                  <button class="btn btn-delete">🗑️ Delete</button>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No document requests found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <a href="admin_dashboard.php" class="back">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
