<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_SESSION['username'];
  $document_type = $_POST['document_type'];
  $purpose = $_POST['purpose']; // Get purpose input

  $prices = [
      'Barangay Clearance' => 150,
      'Barangay Certificate' => 150,
      'Barangay Indigency' => 100,
      'Residency Certificate' => 150,
      'Cenomar' => 150
  ];

  $price = $prices[$document_type] ?? 100;
  $request_date = date('Y-m-d H:i:s');
  $status = 'Pending';

  $stmt = $conn->prepare("
      INSERT INTO document_requests (username, document_type, purpose, amount, request_date, status)
      VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param("sssdds", $username, $document_type, $purpose, $price, $request_date, $status);
  $stmt->execute();

  echo "<script>
      alert('Request submitted successfully! Please wait for admin approval.');
      window.location.href = 'dashboard.php';
  </script>";
  exit();
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>Request Document</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap');
  body {
     background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
    font-family: 'Poppins', sans-serif;
    display: flex; justify-content: center; align-items: center;
    height: 100vh; color: #333;
  }
  .container {
    background: white; padding: 30px; border-radius: 15px;
    width: 400px; text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  select, input, button, textarea {
    width: 100%; padding: 10px; margin-top: 15px;
    border-radius: 10px; border: 1px solid #ccc;
    font-size: 16px;
  }
  textarea {
    resize: none;
    height: 80px;
  }
  button {
    background: #2575fc; color: white; border: none;
    cursor: pointer; transition: 0.3s;
  }
  button:hover { background: #1e5ed3; }
  .dashboard-btn {
    background: green; margin-top: 10px;
  }
  .dashboard-btn:hover { background: #520fa0; }
</style>
</head>
<body>
  <div class='container'>
    <h2>Barangay Document Request</h2>
    <form method='POST'>
      <label for='document_type'>Select Document:</label>
      <select name='document_type' id='document_type' required>
        <option value=''>-- Choose Document --</option>
        <option value='Barangay Clearance'>Barangay Clearance - ₱150</option>
        <option value='Barangay Certificate'>Barangay Certificate - ₱150</option>
        <option value='Barangay Indigency'>Barangay Indigency - ₱100</option>
        <option value='Residency Certificate'>Residency Certificate - ₱150</option>
        <option value='Cenomar'>Cenomar - ₱150</option>
      </select>

      <label for='purpose'>Purpose:</label>
      <textarea name='purpose' id='purpose' placeholder='Enter purpose of your request...' required></textarea>

      <button type='submit'>Submit Request</button>
    </form>

    <!-- Dashboard button -->
    <form action="dashboard.php" method="get">
      <button type="submit" class="dashboard-btn">Back to Dashboard</button>
    </form>
  </div>
</body>
</html>
