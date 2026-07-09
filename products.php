<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }
$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { die("Database Connection Failed!"); }

$products = [];
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) { $products[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanical Seals - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; overflow-x: hidden; }
        .sidebar { width: 260px; background: #111827; height: 100vh; color: white; position: fixed; top: 0; left: 0; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 999; }
        .sidebar-logo { text-align: center; padding: 25px 20px; border-bottom: 1px solid #1f2937; }
        .sidebar-logo img { max-width: 130px; border-radius: 6px; background: white; padding: 5px; }
        .menu { list-style: none; margin-top: 20px; padding: 0; }
        .menu li { padding: 12px 25px; margin: 5px 15px; border-radius: 8px; transition: 0.3s; }
        .menu li.active { background: #3b82f6; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3); }
        .menu li:hover:not(.active) { background: #1f2937; cursor: pointer; }
        .menu li a { color: #d1d5db; text-decoration: none; font-size: 15px; display: flex; align-items: center; gap: 15px; }
        .menu li.active a { color: white; font-weight: 500; }
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        .product-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.04); transition: all 0.3s ease; border: 1px solid #e5e7eb; height: 100%; display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); border-color: #3b82f6; }
        .img-wrapper { height: 220px; background-color: #ffffff; display: flex; align-items: center; justify-content: center; padding: 15px; border-bottom: 1px solid #f0f2f5; }
        .img-wrapper img { width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s ease; }
        .product-card:hover .img-wrapper img { transform: scale(1.05); }
        .card-body-custom { padding: 20px; flex-grow: 1; }
        .seal-title { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 5px; }
        .seal-sku { font-size: 12px; color: #6b7280; margin-bottom: 15px; }
        .seal-detail { font-size: 13px; color: #4b5563; margin-bottom: 5px; }
        .seal-price { font-size: 18px; font-weight: 700; color: #10b981; margin-top: 15px; }
        .card-footer-custom { padding: 15px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .action-btn { width: 35px; height: 35px; display: inline-flex; justify-content: center; align-items: center; border-radius: 8px; transition: 0.2s; border: none; }
        .action-btn:hover { transform: scale(1.1); }
        .empty-state { text-align: center; padding: 50px; background: white; border-radius: 15px; width: 100%; }
        .modal-3d-bg { background-color: #000000; color: white; }
        .spin-3d-container { perspective: 1000px; display: flex; justify-content: center; padding: 40px; }
        .spin-3d-image { width: 250px; height: 250px; object-fit: contain; animation: spin3D 8s linear infinite; filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.5)); }
        @keyframes spin3D { 0% { transform: rotateY(0deg); } 100% { transform: rotateY(360deg); } }
        .search-input:focus { box-shadow: none; border-color: #ced4da; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="assets/logo.jpg" alt="MS Logo"></div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="active"><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
            <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
            <li><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Analysis</a></li> <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0">Factory Seals Catalog</h3>
            <button class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addSealModal">
                <i class="fas fa-plus me-2"></i> Add New Product
            </button>
        </div>

        <div class="row mb-4">
            <div class="col-md-5">
                <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 border-light px-3"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" onkeyup="liveSearch()" class="form-control border-start-0 border-light search-input py-2" placeholder="Search by name, SKU, or category...">
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-4" id="catalogGrid">
            <?php if(empty($products)): ?>
                <div class="col-12"><div class="empty-state"><i class="fas fa-box-open fa-3x text-muted mb-3"></i><h4 class="fw-bold text-secondary">No Products Found</h4></div></div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                <?php 
                    if($product['qty'] >= 100) { $badg = "bg-success text-success"; $txt = "In Stock (" . number_format($product['qty']) . ")"; } 
                    elseif($product['qty'] > 0) { $badg = "bg-warning text-warning"; $txt = "Low Stock (" . number_format($product['qty']) . ")"; } 
                    else { $badg = "bg-danger text-danger"; $txt = "Out of Stock"; }
                ?>
                <div class="col product-item">
                    <div class="product-card">
                        <div class="img-wrapper"><img src="assets/<?php echo htmlspecialchars($product['image']); ?>" alt="Seal Image" onerror="this.src='https://via.placeholder.com/200x200?text=No+Photo'"></div>
                        <div class="card-body-custom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div><h4 class="seal-title"><?php echo htmlspecialchars($product['name']); ?></h4><div class="seal-sku">SKU: MS-<?php echo 1000 + $product['id']; ?></div></div>
                                <span class="badge <?php echo $badg; ?> bg-opacity-10 rounded-pill"><?php echo $txt; ?></span>
                            </div>
                            <div class="seal-detail"><i class="fas fa-tag text-muted me-2"></i> <?php echo htmlspecialchars($product['category']); ?></div>
                            <div class="seal-detail"><i class="fas fa-hammer text-muted me-2"></i> <?php echo htmlspecialchars($product['material']); ?></div>
                            <div class="seal-price">₹<?php echo number_format($product['price']); ?></div>
                        </div>
                        <div class="card-footer-custom">
                            <button type="button" onclick="open3DView('<?php echo htmlspecialchars($product['image']); ?>', '<?php echo htmlspecialchars($product['name']); ?>')" class="action-btn bg-primary bg-opacity-10 text-primary border-0" title="View 3D Model"><i class="fas fa-cube"></i></button>
                            <div>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="action-btn bg-warning bg-opacity-10 text-warning me-2"><i class="fas fa-edit"></i></a>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');" class="action-btn bg-danger bg-opacity-10 text-danger"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addSealModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content border-0 shadow-lg"><div class="modal-header bg-dark text-white"><h5 class="modal-title fw-bold">Add New Product</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4 bg-light"><form action="add_product.php" method="POST" enctype="multipart/form-data"><div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">Name / Model</label><input type="text" name="name" class="form-control shadow-sm" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">Category</label><select name="category" class="form-select shadow-sm" required><option>Pusher Seals</option><option>Cartridge Seals</option><option>O-Rings & Gaskets</option></select></div></div><div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">Price (₹)</label><input type="number" name="price" class="form-control shadow-sm" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">Material</label><input type="text" name="material" class="form-control shadow-sm" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">Factory Stock</label><input type="number" name="qty" class="form-control shadow-sm" required></div></div><div class="mb-4"><label class="form-label fw-bold">Product Image</label><input type="file" name="image" class="form-control shadow-sm" accept=".png, .jpg, .jpeg" required></div><button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow">Save Seal</button></form></div></div></div></div>

    <div class="modal fade" id="view3DModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content modal-3d-bg border-0 shadow-lg" style="border-radius: 20px;"><div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold text-info" id="model3DTitle">3D Viewer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><div class="spin-3d-container"><img id="model3DImage" src="" class="spin-3d-image"></div></div></div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function open3DView(imageName, productName) { document.getElementById('model3DImage').src = 'assets/' + imageName; document.getElementById('model3DTitle').innerHTML = '<i class="fas fa-cube me-2"></i> ' + productName; var myModal = new bootstrap.Modal(document.getElementById('view3DModal')); myModal.show(); }
        function liveSearch() { let input = document.getElementById('searchInput').value.toLowerCase(); let productCards = document.querySelectorAll('.product-item'); productCards.forEach(function(card) { let text = card.innerText.toLowerCase(); if(text.includes(input)) { card.style.display = ""; } else { card.style.display = "none"; } }); }
    </script>
</body>
</html>