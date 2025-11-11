<?php
session_start();
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}
?>
