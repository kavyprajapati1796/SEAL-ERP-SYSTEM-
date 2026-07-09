<?php
session_start();

// Security Lock
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "seal_erp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Database se purana data nikalna form me dikhane ke liye
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
}

// 2. Jab 'Update Product' button dabega tab ye chalega
if (isset($_POST['update_btn'])) {
    $update_id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $material = $_POST['material'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];

    // Check karo ki nayi photo upload hui hai ya nahi
    if (!empty($_FILES['image']['name'])) {
        // Nayi photo aayi hai
        $image_name = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "assets/" . basename($image_name));
        
        $sql = "UPDATE products SET name='$name', category='$category', material='$material', price='$price', qty='$qty', image='$image_name' WHERE id=$update_id";
    } else {
        // Nayi photo nahi aayi, purani hi rehne do
        $sql = "UPDATE products SET name='$name', category='$category', material='$material', price='$price', qty='$qty' WHERE id=$update_id";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: products.php"); // Update hote hi wapas catalog pe
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .edit-card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 30px; border: none; }
        .preview-img { width: 150px; height: 150px; object-fit: contain; border: 1px solid #ddd; border-radius: 10px; padding: 10px; background: #f8f9fa; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0"><i class="fas fa-edit text-warning me-2"></i> Edit Mechanical Seal</h3>
                <a href="products.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i> Back to Catalog</a>
            </div>

            <div class="card edit-card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary">Seal Name / Model</label>
                            <input type="text" name="name" class="form-control shadow-sm" value="<?php echo $product['name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary">Category</label>
                            <input type="text" name="category" class="form-control shadow-sm" value="<?php echo $product['category']; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-secondary">Est. Price (₹)</label>
                            <input type="number" name="price" class="form-control shadow-sm" value="<?php echo $product['price']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-secondary">Material</label>
                            <input type="text" name="material" class="form-control shadow-sm" value="<?php echo $product['material']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-secondary">Factory Stock Qty</label>
                            <input type="number" name="qty" class="form-control shadow-sm" value="<?php echo $product['qty']; ?>" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold text-secondary">Update Product Image (Optional)</label>
                            <input type="file" name="image" class="form-control shadow-sm" accept=".png, .jpg, .jpeg">
                            <small class="text-muted mt-2 d-block">Leave this empty if you don't want to change the current image.</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="form-label fw-bold text-secondary d-block">Current Image</label>
                            <img src="assets/<?php echo $product['image']; ?>" class="preview-img" alt="Current Seal Image">
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <button type="submit" name="update_btn" class="btn btn-warning w-100 py-3 fw-bold shadow rounded-pill text-dark">
                        <i class="fas fa-sync-alt me-2"></i> Update Product Details
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>