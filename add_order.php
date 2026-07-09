<?php
session_start();

// Security Lock
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "seal_erp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form se data uthana
    $client_id = $_POST['client_id'];
    $qty = $_POST['qty'];
    
    // Product ki ID aur Price ko alag karna (Kyunki humne value me id|price bheja tha)
    $product_data = explode('|', $_POST['product_id']);
    $product_id = $product_data[0];
    $product_price = $product_data[1];

    // 🧠 ASLI JADOO: Total Amount Calculate karna (Qty x Price)
    $total_amount = $qty * $product_price;

    // Order Number Generate karna (Jaise: ORD-48291)
    $order_no = "ORD-" . rand(10000, 99999);

    // SQL Query: Data ko 'orders' table me daalna
    $sql = "INSERT INTO orders (order_no, client_id, product_id, qty, total_amount) 
            VALUES ('$order_no', '$client_id', '$product_id', '$qty', '$total_amount')";

    if ($conn->query($sql) === TRUE) {
        // Stock me se product ki quantity kam karna (Inventory Update)
        $update_stock = "UPDATE products SET qty = qty - $qty WHERE id = $product_id";
        $conn->query($update_stock);

        // Sab save hone ke baad wapas Orders page par
        header("Location: orders.php");
        exit();
    } else {
        echo "Error saving order: " . $conn->error;
    }
}

$conn->close();
?>