<?php
session_start();
require 'database.php';

// Fetch all reports
$result = $conn->query("SELECT * FROM reports ORDER BY report_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/lgo.png" type="image/x-icon">
<meta charset="UTF-8">
<title>User Complaints</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
         background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
        min-height: 100vh;
        color: #333;
    }

    .container {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 40px;
        margin-top: 50px;
        animation: fadeIn 0.6s ease-in-out;
    }

    h2 {
        text-align: center;
        color: #2575fc;
        font-weight: 600;
        margin-bottom: 30px;
    }

    .btn-success {
        background: linear-gradient(90deg, #00c851, #007e33);
        border: none;
        font-weight: 500;
        transition: transform 0.2s;
    }
    .btn-success:hover {
        transform: scale(1.05);
        background: linear-gradient(90deg, #007e33, #00c851);
    }

    table {
        border-radius: 10px;
        overflow: hidden;
    }

    thead {
        background: linear-gradient(90deg, #2575fc, #6a11cb);
        color: white;
    }

    tbody tr:hover {
        background-color: rgba(37, 117, 252, 0.1);
        transition: 0.3s;
    }

    img.evidence {
        max-width: 120px;
        max-height: 120px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s;
    }

    img.evidence:hover {
        transform: scale(1.1);
    }

    .btn-primary {
        background: linear-gradient(90deg, #6a11cb, #2575fc);
        border: none;
        transition: 0.3s;
        font-weight: 500;
    }
    .btn-primary:hover {
        background: linear-gradient(90deg, #2575fc, #6a11cb);
        transform: translateY(-2px);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .no-data {
        background: #f9fafb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        font-style: italic;
        color: #666;
    }
</style>
</head>
<body>

<div class="container">
    <h2>📢 User Complaints</h2>

    <!-- Dashboard Button -->
    <div class="text-start mb-3">
        <a href="admin_dashboard.php" class="btn btn-success">← Back to Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle table-bordered table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Evidence</th>
                    <th>Admin Reply</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($row['status'] == 'Resolved'): ?>
                                    <span class="badge bg-success">Resolved</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['evidence'])): ?>
                                    <img src="<?= htmlspecialchars($row['evidence']) ?>" alt="Evidence" class="evidence">
                                <?php else: ?>
                                    <span class="text-muted">No evidence</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['admin_reply']) ?: '<span class="text-muted">No reply yet</span>' ?></td>
                            <td>
                                <a href="reply_report.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Reply</a>
    <a href="delete_report.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
</td>

                            </td>
                            
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data">No complaints submitted yet.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
