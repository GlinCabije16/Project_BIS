<?php
require 'database.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    die("Invalid request. Missing request ID.");
}

$request_id = intval($_GET['request_id']);
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM document_requests WHERE id=? AND username=?");
$stmt->bind_param("is", $request_id, $username);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) die("Request not found.");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $method = $_POST['payment_method'] ?? '';

    if (empty($recipient_name) || empty($contact_number) || empty($address) || empty($method)) {
        $error = "Please fill in all fields.";
    } else {
        $update = $conn->prepare("UPDATE document_requests SET recipient_name=?, contact_number=?, address=? WHERE id=?");
        $update->bind_param("sssi", $recipient_name, $contact_number, $address, $request_id);
        $update->execute();
        $update->close();

        $amount = $request['amount'];
        if ($method == "COD") $amount += 10;

        $date = date('Y-m-d H:i:s');
        $status = 'Paid';

        $stmt2 = $conn->prepare("INSERT INTO payments (request_id, username, amount, payment_method, payment_status, payment_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isdsss", $request_id, $username, $amount, $method, $status, $date);
        $stmt2->execute();
        $stmt2->close();

        $update2 = $conn->prepare("UPDATE document_requests SET status='Paid', payment_method=?, payment_status='Paid' WHERE id=?");
        $update2->bind_param("si", $method, $request_id);
        $update2->execute();
        $update2->close();

        $insertDelivery = $conn->prepare("INSERT INTO deliveries (request_id, username, recipient_name, address, contact_number, delivery_status, delivery_date) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $insertDelivery->bind_param("issss", $request_id, $username, $recipient_name, $address, $contact_number);
        $insertDelivery->execute();
        $insertDelivery->close();

        if ($method == "GCash") header("Location: gcash_qr.php?request_id=$request_id");
        else header("Location: delivery_tracking.php?request_id=$request_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Checkout</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family:'Poppins',sans-serif; }

body {
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.payment-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    width: 480px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    position: relative;
    overflow: hidden;
}

.payment-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, rgba(37,117,252,0.2), rgba(106,17,203,0.2));
    transform: rotate(45deg);
    z-index: 0;
}

.payment-card * { position: relative; z-index: 1; }

h2 {
    color: #764ba2;
    text-align: center;
    margin-bottom: 25px;
    font-size: 28px;
    font-weight: 600;
}

.payment-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background: #f3f3f3;
    border-radius: 12px;
    font-weight: 500;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input, textarea, select {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: 0.3s;
}

input:focus, textarea:focus, select:focus {
    border-color: #764ba2;
    outline: none;
}

button {
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 16px;
    cursor: pointer;
    background: linear-gradient(45deg,#667eea,#764ba2);
    color: #fff;
    transition: 0.3s;
}

button:hover {
    background: linear-gradient(45deg,#764ba2,#667eea);
}

.cancel-btn {
    background: #e11d48 !important;
    margin-top: 8px;
}

.error {
    color: #e11d48;
    text-align: center;
    margin-bottom: 10px;
    font-weight: 500;
}
</style>
</head>
<body>

<div class="payment-card">
    <h2>Checkout Payment</h2>

    <div class="payment-info">
        <span>Document:</span>
        <span><?= htmlspecialchars($request['document_type']) ?></span>
    </div>
    <div class="payment-info">
        <span>Amount:</span>
        <span>₱<?= number_format($request['amount'],2) ?></span>
    </div>

    <?php if(!empty($error)) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>

    <form method="POST">
        <input type="text" name="recipient_name" placeholder="Full Name" value="<?= htmlspecialchars($request['recipient_name'] ?? '') ?>" required>
        <input type="text" name="contact_number" placeholder="Contact Number" value="<?= htmlspecialchars($request['contact_number'] ?? '') ?>" required>
        <textarea name="address" rows="3" placeholder="Delivery Address" required><?= htmlspecialchars($request['address'] ?? '') ?></textarea>
        <select name="payment_method" required>
            <option value="">Select Payment Method</option>
            <option value="GCash">GCash</option>
            <option value="COD">Cash on Delivery (+₱10)</option>
            <option value="Cash on Pickup">Cash on Pickup</option>
        </select>
        <button type="submit">Proceed to Payment</button>
    </form>

   <button type="button" class="cancel-btn" onclick="confirmCancel()">Cancel Request</button>

<script>
function confirmCancel() {
    if (confirm("Are you sure you want to cancel this request?")) {
        window.location.href = "cancel_request.php?request_id=<?= $request_id ?>";
    }
}
</script>
</body>
</html>
