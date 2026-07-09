<?php
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: index.php"); 
    exit(); 
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { 
    die("Database Connection Failed!"); 
}

// Agar form submit hua hai
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company = $conn->real_escape_string($_POST['company_name']);
    $person = $conn->real_escape_string($_POST['contact_person']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Data insert karne ki query
    $sql = "INSERT INTO clients (company_name, contact_person, email, phone) 
            VALUES ('$company', '$person', '$email', '$phone')";
    
    if ($conn->query($sql) === TRUE) {
        // Success hone par wapas clients page par bhej do
        header("Location: clients.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>