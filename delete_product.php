<?php
session_start();

// Security Lock
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Database Connection
    $conn = new mysqli("localhost", "root", "", "seal_erp");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete query
    $sql = "DELETE FROM products WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Delete hone ke baad wapas products page par redirect karega
        header("Location: products.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $conn->close();
} else {
    header("Location: products.php");
    exit();
}
?>