<?php
session_start();
require 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $report_date = date('Y-m-d H:i:s');

    // Handle file upload
    $evidence_path = NULL;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["evidence"]["name"]);
        $target_file = $target_dir . $file_name;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["evidence"]["tmp_name"], $target_file);
            $evidence_path = $target_file;
        } else {
            echo "<script>alert('Invalid file type. Only JPG, PNG, GIF allowed.');</script>";
        }
    }

    // Insert report into database
    $stmt = $conn->prepare("INSERT INTO reports (username, title, description, report_date, evidence) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $title, $description, $report_date, $evidence_path);
    $stmt->execute();

    echo "<script>alert('Report submitted successfully!'); window.location.href='dashboard.php';</script>";
    exit();
}

// Fetch user reports
$stmt = $conn->prepare("SELECT * FROM reports WHERE username=? ORDER BY report_date DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Incident Reports</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body {
     background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
    font-family: 'Poppins', sans-serif;
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    background-color: #fff;
}

h2 {
    color: #0d6efd;
    font-weight: 600;
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
    border-radius: 8px;
    transition: 0.3s;
}
.btn-primary:hover {
    background-color: #0b5ed7;
}

.btn-secondary {
    border-radius: 8px;
}

textarea, input[type="text"], input[type="file"] {
    border-radius: 8px;
}

.report-item {
    background: #f8f9fa;
    border-left: 5px solid #0d6efd;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}
.report-item img {
    max-width: 300px;
    border-radius: 8px;
    margin-top: 10px;
}
</style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="card p-4 mb-5">
        <h2 class="mb-4">📝 Submit a Report</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" class="form-control mb-3" placeholder="Report Title" required>
            <textarea name="description" class="form-control mb-3" placeholder="Describe the incident..." rows="4" required></textarea>
            <label for="evidence" class="form-label fw-semibold">Upload Evidence (optional):</label>
            <input type="file" name="evidence" class="form-control mb-3" accept="image/*">
            <button type="submit" name="submit" class="btn btn-primary px-4">Submit Report</button>
        </form>
    </div>

    <div class="card p-4">
        <h2 class="mb-4">📋 My Reports & Admin Replies</h2>
        <?php if ($reports->num_rows > 0): ?>
            <?php while ($row = $reports->fetch_assoc()): ?>
                <div class="report-item">
                    <p><strong>📌 Title:</strong> <?= htmlspecialchars($row['title']) ?></p>
                    <p><strong>🗒️ Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
                    <p><strong>📅 Status:</strong> <span class="badge <?= $row['status']=='Resolved'?'bg-success':'bg-warning' ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </span></p>
                    <?php if ($row['admin_reply']): ?>
                        <p><strong>💬 Admin Reply:</strong> <?= htmlspecialchars($row['admin_reply']) ?></p>
                    <?php endif; ?>
                    <?php if ($row['evidence']): ?>
                        <p><strong>🖼️ Evidence:</strong><br>
                        <img src="<?= htmlspecialchars($row['evidence']) ?>" alt="Evidence"></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted text-center">No reports submitted yet.</p>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
