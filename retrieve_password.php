<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['user_id']);

    // Validate ID
    if (empty($user_id)) {
        die("Invalid user ID value");
    }

    $stmt = $conn->prepare("SELECT username, password FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<h3>User Information</h3>";
        echo "Username: " . htmlspecialchars($row['username']) . "<br>";
        echo "Password: " . htmlspecialchars($row['password']) . "<br>";
    } else {
        echo "User not found.";
    }

    $stmt->close();
}
?>
