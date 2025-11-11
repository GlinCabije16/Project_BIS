<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM deliveries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_manage_deliveries.php");
    exit();
}

// Handle update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $delivery_status = $_POST['delivery_status'];

    $stmt = $conn->prepare("UPDATE deliveries SET delivery_status = ? WHERE id = ?");
    $stmt->bind_param("si", $delivery_status, $id);
    $stmt->execute();
    header("Location: admin_manage_deliveries.php");
    exit();
}

// Fetch deliveries
$result = $conn->query("SELECT * FROM deliveries ORDER BY delivery_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Deliveries</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 0; }
    .container { width: 90%; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #333; }
    .dashboard-btn { display:inline-block; margin-bottom:15px; background:#007bff; color:#fff; padding:8px 15px; border-radius:6px; text-decoration:none; transition:0.3s; }
    .dashboard-btn:hover { background:#0056b3; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background-color: #007bff; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .btn { padding: 5px 10px; text-decoration: none; border-radius: 5px; }
    .btn-edit { background-color: #28a745; color: white; }
    .btn-delete { background-color: #dc3545; color: white; }
    .btn-edit:hover { background-color: #218838; }
    .btn-delete:hover { background-color: #c82333; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Manage Deliveries</h2>

    <!-- Back to Dashboard Button -->
    <a href="admin_dashboard.php" class="dashboard-btn">← Back to Dashboard</a>

    <table>
      <tr>
        <th>ID</th>
        <th>Rider Name</th>
        <th>Recipient</th>
        <th>Address</th>
        <th>Contact</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id']; ?></td>
          <td><?= htmlspecialchars($row['rider_name']); ?></td>
          <td><?= htmlspecialchars($row['recipient_name']); ?></td>
          <td><?= htmlspecialchars($row['address']); ?></td>
          <td><?= htmlspecialchars($row['contact_number']); ?></td>
          <td>
            <form method="POST" style="display:inline-block;">
              <input type="hidden" name="id" value="<?= $row['id']; ?>">
              <select name="delivery_status">
                <option <?= $row['delivery_status']=='Pending'?'selected':''; ?>>Pending</option>
                <option <?= $row['delivery_status']=='In Transit'?'selected':''; ?>>In Transit</option>
                <option <?= $row['delivery_status']=='Delivered'?'selected':''; ?>>Delivered</option>
                <option <?= $row['delivery_status']=='Cancelled'?'selected':''; ?>>Cancelled</option>
              </select>
              <button type="submit" name="update" class="btn btn-edit">Update</button>
            </form>
          </td>
          <td><?= $row['delivery_date']; ?></td>
          <td>
            <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this delivery?');" class="btn btn-delete">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
