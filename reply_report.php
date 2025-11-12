<?php
session_start();
require 'database.php';

if (!isset($_GET['id'])) {
    header("Location: view_complaints.php");
    exit();
}

$report_id = $_GET['id'];

// Fetch report info
$stmt = $conn->prepare("SELECT * FROM reports WHERE id=?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();
$report = $result->fetch_assoc();

if (!$report) {
    echo "Report not found!";
    exit();
}

// Handle admin reply and certificate upload
if (isset($_POST['submit'])) {
    $admin_reply = $_POST['admin_reply'];
    
    // Certificate upload
    $certificate_path = $report['certificate']; // default to existing
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] == 0) {
        $target_dir = "certificates/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES['certificate']['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $target_file)) {
            $certificate_path = $target_file;
        }
    }

    // Update report
    $stmt = $conn->prepare("UPDATE reports SET admin_reply=?, status='Completed', certificate=? WHERE id=?");
    $stmt->bind_param("ssi", $admin_reply, $certificate_path, $report_id);
    $stmt->execute();

    echo "<script>alert('Reply and certificate sent successfully!'); window.location.href='view_complaints.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>Reply Report</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Reply to Report</h2>
    <p><strong>User:</strong> <?= htmlspecialchars($report['username']) ?></p>
    <p><strong>Title:</strong> <?= htmlspecialchars($report['title']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($report['description']) ?></p>
    <?php if ($report['evidence']): ?>
        <p><strong>Evidence:</strong><br><img src="<?= htmlspecialchars($report['evidence']) ?>" style="max-width:200px;"></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Admin Reply</label>
            <textarea name="admin_reply" class="form-control" required><?= htmlspecialchars($report['admin_reply']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Attach Certificate (PDF/Image)</label>
            <input type="file" name="certificate" class="form-control">
            <?php if ($report['certificate']): ?>
                <small>Current: <a href="<?= htmlspecialchars($report['certificate']) ?>" target="_blank">View Certificate</a></small>
            <?php endif; ?>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Send Reply & Certificate</button>
    </form>
    <a href="view_complaints.php" class="btn btn-secondary mt-2">Back</a>


</div>
</body>
</html>
