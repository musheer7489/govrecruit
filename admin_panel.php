<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access Denied!</div>";
    exit();
}
echo "<h2>Admin Dashboard</h2>";
?>
