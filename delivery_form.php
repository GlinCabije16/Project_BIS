<?php
require 'database.php';
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: user_login.php");
  exit();
}

$request_id = $_GET['request_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $delivery_method = $_POST['delivery_method'];
  $address = $_POST['address'];
  $delivery_fee = ($delivery_method == 'Delivery') ? 10.00 : 0.00;
  $date_submitted = date('Y-m-d H:i:s');

  $stmt = $conn->prepare("INSERT INTO delivery_options (request_id, username, delivery_method, address, delivery_fee, date_submitted)
                          VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isssds", $request_id, $username, $delivery_method, $address, $delivery_fee, $date_submitted);
  $stmt->execute();

  echo "<script>alert('Your choice has been submitted successfully!'); window.location='user_dashboard.php';</script>";
  exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Delivery Option</title></head>
<body style="font-family:Poppins,sans-serif;text-align:center;">
  <h2>Choose Delivery Method</h2>
  <form method="POST">
    <label><input type="radio" name="delivery_method" value="Pickup" required> Pickup</label><br>
    <label><input type="radio" name="delivery_method" value="Delivery" required> Delivery (₱10 fee)</label><br><br>
    <textarea name="address" placeholder="Enter delivery address if Delivery" rows="3" cols="30"></textarea><br><br>
    <button type="submit" style="background:#2575fc;color:white;padding:10px 20px;border:none;border-radius:5px;">Submit</button>
  </form>
</body>
</html>
