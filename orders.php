<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { die("Database Connection Failed!"); }

$clients = $conn->query("SELECT * FROM clients ORDER BY company_name ASC");
$products = $conn->query("SELECT * FROM products ORDER BY name ASC");

$sql = "SELECT orders.*, clients.company_name, products.name AS product_name, products.price 
        FROM orders 
        JOIN clients ON orders.client_id = clients.id 
        JOIN products ON orders.product_id = products.id 
        ORDER BY orders.id DESC";
$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders & Billing - Admin Portal</title>
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
        .order-table { width: 100%; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-collapse: separate; border-spacing: 0; overflow: hidden; }
        .order-table thead { background-color: #f9fafb; }
        .order-table th { color: #6b7280; font-weight: 600; font-size: 13px; text-transform: uppercase; padding: 18px 25px; border-bottom: 1px solid #e5e7eb; }
        .order-table td { padding: 15px 25px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
        .order-no { font-weight: 700; color: #3b82f6; font-size: 15px; }
        .empty-state { text-align: center; padding: 50px; background: white; border-radius: 12px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="assets/logo.jpg" alt="MS Logo"></div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
            <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
            <li class="active"><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analysis</a></li>
            <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0">Orders & Billing</h3>
            <button class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-plus-circle me-2"></i> Create New Order
            </button>
        </div>

        <?php if(!$orders || $orders->num_rows == 0): ?>
            <div class="empty-state shadow-sm"><i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i><h4 class="fw-bold text-secondary">No Orders Found</h4></div>
        <?php else: ?>
            <table class="order-table">
                <thead><tr><th>Order ID</th><th>Client Company</th><th>Product & Qty</th><th>Total Amount</th><th>Status</th><th style="text-align: right;">Action</th></tr></thead>
                <tbody>
                    <?php while($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><div class="order-no">#<?php echo $order['order_no']; ?></div><small class="text-muted"><?php echo date('d M Y', strtotime($order['created_at'])); ?></small></td>
                        <td class="fw-bold text-dark"><?php echo $order['company_name']; ?></td>
                        <td><div><?php echo $order['product_name']; ?></div><small class="text-muted">Qty: <?php echo $order['qty']; ?> x ₹<?php echo number_format($order['price']); ?></small></td>
                        <td class="fw-bold text-success" style="font-size: 16px;">₹<?php echo number_format($order['total_amount']); ?></td>
                        
                        <td>
                            <?php if($order['status'] == 'Pending'): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 mb-1 d-block">Pending</span>
                                <a href="update_status.php?id=<?php echo $order['id']; ?>&status=Paid" class="btn btn-sm btn-success py-0 w-100" style="font-size: 11px;">Mark as Paid</a>
                            <?php else: ?>
                                <span class="badge bg-success rounded-pill px-3 py-1 mb-1 d-block">Paid</span>
                                <a href="update_status.php?id=<?php echo $order['id']; ?>&status=Pending" class="btn btn-sm btn-outline-danger py-0 w-100" style="font-size: 11px;"><i class="fas fa-undo"></i> Undo (Pending)</a>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: right;">
                            <a href="print_bill.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="fas fa-file-pdf me-1"></i> Print Bill</a>
                        </td>
                    </tr>
                    <?php endwhile; ?> 
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="addOrderModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg"><div class="modal-header bg-dark text-white"><h5 class="modal-title fw-bold">Create B2B Order</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4 bg-light"><form action="add_order.php" method="POST"><div class="mb-3"><label class="form-label fw-bold">Select Client</label><select name="client_id" class="form-select shadow-sm" required><option value="">-- Choose Client --</option><?php if($clients) { while($client = $clients->fetch_assoc()): ?><option value="<?php echo $client['id']; ?>"><?php echo $client['company_name']; ?></option><?php endwhile; } ?></select></div><div class="mb-3"><label class="form-label fw-bold">Select Product</label><select name="product_id" class="form-select shadow-sm" required><option value="">-- Choose Seal --</option><?php if($products) { while($product = $products->fetch_assoc()): ?><option value="<?php echo $product['id']; ?>|<?php echo $product['price']; ?>"><?php echo $product['name']; ?> (₹<?php echo number_format($product['price']); ?>)</option><?php endwhile; } ?></select></div><div class="mb-4"><label class="form-label fw-bold">Quantity Needed</label><input type="number" name="qty" class="form-control shadow-sm" placeholder="e.g. 100" required></div><button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow"><i class="fas fa-file-invoice me-2"></i> Generate Order</button></form></div></div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>