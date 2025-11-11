<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
$request_id = $_GET['request_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GCash Payment</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: #f1f5f9;
    text-align: center;
    padding-top: 80px;
  }
  .qr-box {
    background: white;
    width: 400px;
    margin: auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  img {
    width: 200px;
    margin: 20px 0;
  }
  button {
    padding: 12px 20px;
    background: #2575fc;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
  }
</style>
</head>
<body>
  <div class="qr-box">
    <h2>Scan QR to Pay via GCash</h2>
    <img src="images/gcash.jpeg" alt="GCash QR Code">
    <p>Once payment is done, click below.</p>
    <form action="delivery_tracking.php" method="get">
      <input type="hidden" name="request_id" value="<?= $request_id ?>">
      <button type="submit">I've Paid</button>
    </form>
  </div>
</body>
</html>
