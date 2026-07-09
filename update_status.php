<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "seal_erp");

if(isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = $_GET['id'];
    $new_status = $_GET['status']; // 'Paid' ya 'Pending' aayega
    
    // Security check
    if($new_status == 'Paid' || $new_status == 'Pending') {
        $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    }
}

// Wapas orders page par bhej do
header("Location: orders.php");
exit();
?>