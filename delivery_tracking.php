<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
  die("Invalid request ID.");
}

$request_id = intval($_GET['request_id']);
$username = $_SESSION['username'];

// Fetch delivery record linked to this request
$stmt = $conn->prepare("
  SELECT d.*, r.full_name AS rider_fullname, r.contact AS rider_contact
  FROM deliveries d
  LEFT JOIN riders r ON d.rider_name = r.full_name
  WHERE d.request_id = ? AND d.username = ?
");
$stmt->bind_param("is", $request_id, $username);
$stmt->execute();
$delivery = $stmt->get_result()->fetch_assoc();

if (!$delivery) {
  die("No delivery found for this request.");
}

// Define progress based on delivery_status
switch ($delivery['delivery_status']) {
  case 'Pending':
    $progress = 25;
    $current_status = "Payment Received";
    break;
  case 'In Transit':
    $progress = 75;
    $current_status = "Out for Delivery";
    break;
  case 'Delivered':
    $progress = 100;
    $current_status = "Delivered";
    break;
  default:
    $progress = 50;
    $current_status = "Preparing Order";
}

$expected_date = date("F j, Y", strtotime($delivery['delivery_date'] . " +2 days"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Delivery Tracking</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
                  url('images/logo.png') center/cover no-repeat fixed;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .track-box {
    background: white;
    padding: 30px;
    border-radius: 14px;
    text-align: center;
    width: 450px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  }
  h2 {
    color: #2575fc;
    margin-bottom: 10px;
  }
  .status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 25px;
    position: relative;
  }
  .status-line {
    position: absolute;
    top: 50%;
    left: 5%;
    width: 90%;
    height: 4px;
    background: #e0e0e0;
    z-index: 0;
  }
  .status-progress {
    position: absolute;
    top: 50%;
    left: 5%;
    height: 4px;
    background: #2575fc;
    z-index: 1;
    width: 0%;
    transition: width 0.5s ease-in-out;
  }
  .status-step {
    background: #fff;
    border: 3px solid #ccc;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2;
  }
  .status-step.active {
    border-color: #2575fc;
    background: #2575fc;
    color: white;
  }
  .step-label {
    margin-top: 8px;
    font-size: 13px;
  }
  .info {
    text-align: left;
    margin-top: 25px;
  }
  .info p {
    margin: 6px 0;
  }
  button {
    background: #2575fc;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: 0.3s;
  }
  button:hover {
    background: #1a5be0;
  }
</style>
</head>
<body>
  <div class="track-box">
    <h2>🚚 Delivery Tracking</h2>
    <p>Request ID: <strong>#<?= htmlspecialchars($request_id) ?></strong></p>

    <div class="status">
      <div class="status-line"></div>
      <div class="status-progress" id="progress-bar"></div>

      <div class="status-step <?= ($current_status == 'Payment Received' || $current_status == 'Preparing Order' || $current_status == 'Out for Delivery' || $current_status == 'Delivered') ? 'active' : '' ?>">1</div>
      <div class="status-step <?= ($current_status == 'Preparing Order' || $current_status == 'Out for Delivery' || $current_status == 'Delivered') ? 'active' : '' ?>">2</div>
      <div class="status-step <?= ($current_status == 'Out for Delivery' || $current_status == 'Delivered') ? 'active' : '' ?>">3</div>
      <div class="status-step <?= ($current_status == 'Delivered') ? 'active' : '' ?>">4</div>
    </div>

    <div class="status-labels" style="display: flex; justify-content: space-between; margin-top: 10px;">
      <span class="step-label">Payment</span>
      <span class="step-label">Preparing</span>
      <span class="step-label">Out</span>
      <span class="step-label">Delivered</span>
    </div>

<div class="info">
  <p><strong>Current Status:</strong> <?= htmlspecialchars($current_status) ?></p>
  <p><strong>Rider:</strong> <?= $delivery['rider_name'] ? htmlspecialchars($delivery['rider_name']) : 'Not assigned yet' ?></p>
  <?php if ($delivery['rider_contact']): ?>
    <p><strong>Contact:</strong> <?= htmlspecialchars($delivery['rider_contact']) ?></p>
  <?php endif; ?>
  <p><strong>Expected Delivery:</strong> <?= $expected_date ?></p>
</div>


    <button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
  </div>

  <script>
    // Animate progress bar based on current status
 const progress = <?= $progress ?>;
  document.getElementById('progress-bar').style.width = progress + '%';

    switch (status) {
      case 'Payment Received': progress = 25; break;
      case 'Preparing Order': progress = 50; break;
      case 'Out for Delivery': progress = 75; break;
      case 'Delivered': progress = 100; break;
    }
    progressBar.style.width = progress + '%';
  </script>
</body>
</html>
