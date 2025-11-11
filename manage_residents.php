<?php
session_start();
require 'database.php';

// Check admin session
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Add new resident
if (isset($_POST['add'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $occupation = $_POST['occupation'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $date_moved_in = $_POST['date_moved_in'];

    $stmt = $conn->prepare("INSERT INTO residents 
        (first_name,last_name,birthdate,gender,civil_status,occupation,contact_number,address,date_moved_in) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $first_name,$last_name,$birthdate,$gender,$civil_status,$occupation,$contact_number,$address,$date_moved_in);
    $stmt->execute();
    header("Location: manage_residents.php");
    exit();
}

// Update resident
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $occupation = $_POST['occupation'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $date_moved_in = $_POST['date_moved_in'];

    $stmt = $conn->prepare("UPDATE residents SET 
        first_name=?, last_name=?, birthdate=?, gender=?, civil_status=?, occupation=?, contact_number=?, address=?, date_moved_in=? 
        WHERE id=?");
    $stmt->bind_param("sssssssssi", $first_name,$last_name,$birthdate,$gender,$civil_status,$occupation,$contact_number,$address,$date_moved_in,$id);
    $stmt->execute();
    header("Location: manage_residents.php");
    exit();
}

// Delete resident
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM residents WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_residents.php");
    exit();
}

// Fetch all residents
$residents = $conn->query("SELECT * FROM residents ORDER BY last_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Residents Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Poppins', sans-serif;
 background:
        linear-gradient(rgba(37,117,252,0.85), rgba(106,17,203,0.85)),
        url('images/logo.png') center center fixed;
  color: #333;
  min-height: 100vh;
}

.container {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  padding: 40px;
  margin-top: 50px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.1);
  animation: fadeIn 0.6s ease-in-out;
}

h2 {
  color: #2575fc;
  text-align: center;
  font-weight: 600;
  margin-bottom: 30px;
}

.card {
  border: none;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  transition: 0.3s;
}

.card:hover {
  transform: translateY(-5px);
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

.btn-primary {
  background: linear-gradient(90deg, #6a11cb, #2575fc);
  border: none;
  font-weight: 500;
}
.btn-primary:hover {
  background: linear-gradient(90deg, #2575fc, #6a11cb);
  transform: translateY(-2px);
}

.table {
  border-radius: 10px;
  overflow: hidden;
}
thead {
  background: linear-gradient(90deg, #2575fc, #6a11cb);
  color: white;
}
tbody tr:hover {
  background-color: rgba(37,117,252,0.1);
}

.modal-content {
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<div class="container">
  <h2>🏠 Residents Management</h2>
  <div class="text-start mb-3">
    <a href="admin_dashboard.php" class="btn btn-success">← Back to Dashboard</a>
  </div>

  <!-- Add Resident Form -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">➕ Add New Resident</h4>
    <form method="POST">
      <div class="row">
        <div class="col"><input type="text" name="first_name" class="form-control mb-2" placeholder="First Name" required></div>
        <div class="col"><input type="text" name="last_name" class="form-control mb-2" placeholder="Last Name" required></div>
      </div>
      <input type="date" name="birthdate" class="form-control mb-2" required>
      <div class="row">
        <div class="col">
          <select name="gender" class="form-control mb-2" required>
            <option value="">-- Select Gender --</option>
            <option>Male</option>
            <option>Female</option>
          </select>
        </div>
        <div class="col">
          <select name="civil_status" class="form-control mb-2" required>
            <option value="">-- Civil Status --</option>
            <option>Single</option>
            <option>Married</option>
            <option>Widow</option>
            <option>Widower</option>
            <option>Separated</option>
          </select>
        </div>
      </div>
      <input type="text" name="occupation" class="form-control mb-2" placeholder="Occupation">
      <input type="text" name="contact_number" class="form-control mb-2" placeholder="Contact Number">
      <input type="text" name="address" class="form-control mb-2" placeholder="Address" required>
      <input type="date" name="date_moved_in" class="form-control mb-3" required>
      <button type="submit" name="add" class="btn btn-primary w-100">Add Resident</button>
    </form>
  </div>

  <!-- Residents Table -->
  <div class="card p-4">
    <h4 class="mb-3">👥 Residents List (Population: <?= $residents->num_rows ?>)</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Birthdate</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Civil Status</th>
            <th>Occupation</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Years in Barangay</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $residents->fetch_assoc()):
            $age = date_diff(date_create($row['birthdate']), date_create())->y;
            $years_lived = date_diff(date_create($row['date_moved_in']), date_create())->y;
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></strong></td>
            <td><?= htmlspecialchars($row['birthdate']) ?></td>
            <td><?= $age ?> yrs</td>
            <td><?= htmlspecialchars($row['gender']) ?></td>
            <td><?= htmlspecialchars($row['civil_status']) ?></td>
            <td><?= htmlspecialchars($row['occupation']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= $years_lived ?> yrs</td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
              <a href="manage_residents.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this resident?')">Delete</a>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title">Edit Resident</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <div class="row">
                      <div class="col"><input type="text" name="first_name" class="form-control mb-2" value="<?= htmlspecialchars($row['first_name']) ?>" required></div>
                      <div class="col"><input type="text" name="last_name" class="form-control mb-2" value="<?= htmlspecialchars($row['last_name']) ?>" required></div>
                    </div>
                    <input type="date" name="birthdate" class="form-control mb-2" value="<?= htmlspecialchars($row['birthdate']) ?>" required>
                    <select name="gender" class="form-control mb-2" required>
                      <option <?= $row['gender']=='Male'?'selected':'' ?>>Male</option>
                      <option <?= $row['gender']=='Female'?'selected':'' ?>>Female</option>
                    </select>
                    <select name="civil_status" class="form-control mb-2" required>
                      <option <?= $row['civil_status']=='Single'?'selected':'' ?>>Single</option>
                      <option <?= $row['civil_status']=='Married'?'selected':'' ?>>Married</option>
                      <option <?= $row['civil_status']=='Widow'?'selected':'' ?>>Widow</option>
                      <option <?= $row['civil_status']=='Widower'?'selected':'' ?>>Widower</option>
                      <option <?= $row['civil_status']=='Separated'?'selected':'' ?>>Separated</option>
                    </select>
                    <input type="text" name="occupation" class="form-control mb-2" value="<?= htmlspecialchars($row['occupation']) ?>">
                    <input type="text" name="contact_number" class="form-control mb-2" value="<?= htmlspecialchars($row['contact_number']) ?>">
                    <input type="text" name="address" class="form-control mb-2" value="<?= htmlspecialchars($row['address']) ?>" required>
                    <input type="date" name="date_moved_in" class="form-control mb-2" value="<?= htmlspecialchars($row['date_moved_in']) ?>" required>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
