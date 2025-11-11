<?php
session_start();
require 'database.php';

if (!isset($_SESSION['rider']) || !isset($_SESSION['rider_name'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_name = $_SESSION['rider_name'];
$delivery_id = intval($_POST['delivery_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$delivery_id || !$action) {
    header("Location: rider_dashboard.php");
    exit();
}

switch($action) {

    // ✅ Claim order
    case 'claim':
        $rider_contact = $_POST['rider_contact'] ?? '';
        $stmt = $conn->prepare("UPDATE deliveries 
            SET rider_name = ?, rider_contact = ?, delivery_status='In Transit' 
            WHERE id=? AND (rider_name IS NULL OR rider_name='' OR LOWER(rider_name)='unassigned')");
        if ($stmt) { 
            $stmt->bind_param("ssi", $rider_name, $rider_contact, $delivery_id); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;

    // ✅ Delivered → Move to Trash (is_deleted = 1)
    case 'delivered':
        $stmt = $conn->prepare("UPDATE deliveries 
            SET delivery_status='Delivered', is_deleted=1 
            WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("is", $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        // Optional: delete chat messages for that delivery
        $conn->query("DELETE FROM chat_messages WHERE delivery_id=$delivery_id");
        break;

    // ✅ Cancel claim
    case 'cancel':
        $stmt = $conn->prepare("UPDATE deliveries 
            SET delivery_status='Cancelled', rider_name=NULL, rider_contact=NULL 
            WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("is", $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;

    // ✅ Edit number of items
    case 'edit':
        $number_of_items = intval($_POST['number_of_items'] ?? 1);
        $stmt = $conn->prepare("UPDATE deliveries 
            SET number_of_items=? WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("iis", $number_of_items, $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;

    // ✅ Soft delete (move manually to trash)
    case 'delete':
        $stmt = $conn->prepare("UPDATE deliveries 
            SET is_deleted=1 WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("is", $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;

    // ✅ Restore from trash
    case 'restore':
        $stmt = $conn->prepare("UPDATE deliveries 
            SET is_deleted=0 WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("is", $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;

    // ✅ Permanently delete from trash
    case 'permadelete':
        // Delete both the delivery and its chat messages
        $conn->query("DELETE FROM chat_messages WHERE delivery_id=$delivery_id");
        $stmt = $conn->prepare("DELETE FROM deliveries WHERE id=? AND rider_name=?");
        if ($stmt) { 
            $stmt->bind_param("is", $delivery_id, $rider_name); 
            $stmt->execute(); 
            $stmt->close(); 
        }
        break;
}

header("Location: rider_dashboard.php");
exit();
?>
