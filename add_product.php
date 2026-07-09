<?php
session_start();

// Security Lock
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Database se connection banayein
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "seal_erp";
$conn = new mysqli($host, $user, $pass, $dbname);

// Connection check karein
if ($conn->connect_error) {
    die("Database Error: " . $conn->connect_error);
}

// Jab form Submit (POST) hoga tab ye chalega
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form se saara data uthana
    $name = $_POST['name'];
    $category = $_POST['category'];
    $material = $_POST['material'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];

    // Photo Upload Logic
    $image_name = $_FILES['image']['name']; // Photo ka naam (jaise o-ring.png)
    $tmp_name = $_FILES['image']['tmp_name']; // Temporary location
    $target_dir = "assets/"; // Kahan save karna hai
    $target_file = $target_dir . basename($image_name);

    // Photo ko 'assets' folder me move karna
    if(move_uploaded_file($tmp_name, $target_file)) {
        
        // Photo folder me aa gayi, ab details Database me daalo
        $sql = "INSERT INTO products (name, category, material, price, qty, image) 
                VALUES ('$name', '$category', '$material', '$price', '$qty', '$image_name')";

        if ($conn->query($sql) === TRUE) {
            // Sab kuch successfully save ho gaya! Wapas products page par bhej do
            header("Location: products.php");
            exit();
        } else {
            echo "Error saving data: " . $conn->error;
        }
    } else {
        echo "Sorry, error uploading product image. Make sure 'assets' folder exists.";
    }
}
?>