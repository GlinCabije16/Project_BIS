<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$request_id = $_GET['request_id'] ?? null;

// If upload form is submitted
if (isset($_POST['upload_proof'])) {
    require "database.php";

    $request_id = $_POST['request_id'];

    // Upload folder
    $targetDir = "uploads/";

    // Create if not exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // File name
    $fileName = time() . "_" . basename($_FILES["proof"]["name"]);
    $targetFile = $targetDir . $fileName;

    // Validate image
    $check = getimagesize($_FILES["proof"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move to uploads/
    if (move_uploaded_file($_FILES["proof"]["tmp_name"], $targetFile)) {
        
        // Insert into DB (Adjust table name)
        $stmt = $conn->prepare("INSERT INTO payments (request_id, username, proof_image, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $request_id, $_SESSION['username'], $fileName);
        $stmt->execute();

        // Redirect to tracking
        header("Location: delivery_tracking.php?request_id=" . $request_id);
        exit();
    } else {
        die("Error uploading file.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
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
            width: 450px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        img { width: 220px; margin: 20px 0; }
        button {
            padding: 12px 20px;
            background: #2575fc;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        input[type="file"] {
            margin: 15px 0;
        }
    </style>
</head>
<body>

<div class="qr-box">
    <h2>Scan QR to Pay via GCash</h2>
    <img src="images/gcash.jpeg" alt="GCash QR Code">
    <p>After paying, upload your payment screenshot.</p>

    <!-- Upload proof form -->
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="request_id" value="<?= $request_id ?>">
        
        <label><strong>Upload Payment Proof (Screenshot)</strong></label><br>
        <input type="file" name="proof" accept="image/*" required><br>

        <button type="submit" name="upload_proof">Submit Payment Proof</button>
    </form>
</div>

</body>
</html>
