<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['request_id']) || !isset($_FILES['proof_image'])) {
    die("Invalid Request");
}

$request_id = $_POST['request_id'];

// Create folder if not exists
$dir = "payment_proofs/";
if (!is_dir($dir)) mkdir($dir);

// Save file
$filename = time() . "_" . basename($_FILES["proof_image"]["name"]);
$target = $dir . $filename;

if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target)) {

    // Update DB
    $stmt = $conn->prepare("
        UPDATE document_requests 
        SET proof_image = ?, status = 'Paid', payment_date = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("si", $filename, $request_id);
    $stmt->execute();

    header("Location: history.php");
    exit();
} else {
    echo "Error uploading payment proof.";
}
?>
