<?php
session_start();

// Security Lock
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. Total Seals
$sql_seals = "SELECT COUNT(id) as count FROM products";
$total_seals = $conn->query($sql_seals)->fetch_assoc()['count'] ?? 0;

// 2. Monthly Revenue
$sql_rev = "SELECT SUM(total_amount) as total FROM orders WHERE status='Paid'";
$total_revenue = $conn->query($sql_rev)->fetch_assoc()['total'] ?? 0;

// 3. Pending Orders
$sql_pend = "SELECT COUNT(id) as count FROM orders WHERE status='Pending'";
$total_pending = $conn->query($sql_pend)->fetch_assoc()['count'] ?? 0;

// 4. Total Inquiries
$sql_inq = "SELECT COUNT(id) as count FROM inquiries";
$total_inquiries = $conn->query($sql_inq)->fetch_assoc()['count'] ?? 0;

// Fetch Recent 5 Orders
$sql_recent = "SELECT o.order_no, c.company_name, p.name as seal_name, o.created_at, o.status, o.total_amount 
               FROM orders o 
               LEFT JOIN clients c ON o.client_id = c.id 
               LEFT JOIN products p ON o.product_id = p.id 
               ORDER BY o.id DESC LIMIT 5";
$recent_orders = $conn->query($sql_recent);

// Fetch Recent 5 Inquiries
$recent_inquiries = $conn->query("SELECT * FROM inquiries ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MS Engineering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; overflow-x: hidden; }
        
        /* ORIGINAL DARK SIDEBAR */
        .sidebar { width: 260px; background: #111827; height: 100vh; color: white; position: fixed; top: 0; left: 0; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 999; }
        .sidebar-logo { text-align: center; padding: 25px 20px; border-bottom: 1px solid #1f2937; }
        .sidebar-logo img { max-width: 130px; border-radius: 6px; background: white; padding: 5px; }
        .menu { list-style: none; margin-top: 20px; padding: 0; }
        .menu li { padding: 12px 25px; margin: 5px 15px; border-radius: 8px; transition: 0.3s; }
        .menu li.active { background: #3b82f6; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3); }
        .menu li:hover:not(.active) { background: #1f2937; cursor: pointer; }
        .menu li a { color: #d1d5db; text-decoration: none; font-size: 15px; display: flex; align-items: center; gap: 15px; }
        .menu li.active a { color: white; font-weight: 500; }
        
        /* MAIN CONTENT */
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        
        /* STAT CARDS */
        .stat-card { background: white; border-radius: 15px; padding: 25px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e5e7eb; transition: 0.3s; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 24px; }
        .stat-info h3 { margin: 0; font-size: 26px; font-weight: 700; color: #111827; }
        .stat-info p { margin: 0; color: #6b7280; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* TABLES */
        .panel-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e5e7eb; margin-bottom: 30px; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; margin-bottom: 20px; }
        .panel-header h4 { font-size: 18px; font-weight: 600; color: #111827; margin: 0; }
        
        .table th { color: #6b7280; font-weight: 600; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; padding: 12px; }
        .table td { vertical-align: middle; padding: 15px 12px; color: #374151; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        
        .badge-paid { background-color: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .badge-pending { background-color: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        
        .msg-preview { display: block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #6b7280; font-size: 13px; margin-top: 4px; }
        .empty-state { text-align: center; padding: 30px; color: #9ca3af; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="assets/logo.jpg" alt="MS Logo"></div>
        <ul class="menu">
            <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
            <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
            <li><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-pie"></i> Analysis</a></li>
            <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0">Executive Dashboard</h3>
            <span class="text-muted"><i class="fas fa-user-circle me-1"></i> Admin Logged In</span>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_seals; ?></h3>
                        <p>Total Seals</p>
                    </div>
                    <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-box"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($total_revenue); ?></h3>
                        <p>Revenue</p>
                    </div>
                    <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-rupee-sign"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_pending; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                    <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_inquiries; ?></h3>
                        <p>Web Inquiries</p>
                    </div>
                    <div class="stat-icon" style="background: #f3e8ff; color: #9333ea;"><i class="fas fa-envelope"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-7">
                <div class="panel-card">
                    <div class="panel-header">
                        <h4><i class="fas fa-shopping-cart text-primary me-2"></i> Recent Orders</h4>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                    <?php while($row = $recent_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong class="text-primary"><?php echo $row['order_no']; ?></strong></td>
                                            <td class="fw-medium text-dark"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                            <td>
                                                <?php if ($row['status'] == 'Paid'): ?>
                                                    <span class="badge-paid">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge-pending">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold text-dark">₹<?php echo number_format($row['total_amount']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="empty-state"><i class="fas fa-box-open mb-2 fs-4"></i><br>No orders yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="panel-card">
                    <div class="panel-header">
                        <h4><i class="fas fa-inbox text-info me-2"></i> Recent Web Inquiries</h4>
                        <a href="inquiries.php" class="btn btn-sm btn-outline-info rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Client & Email</th>
                                    <th>Inquiry Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($recent_inquiries && $recent_inquiries->num_rows > 0): ?>
                                    <?php while($row = $recent_inquiries->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                                <div class="small text-primary fw-medium"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border mb-1"><?php echo htmlspecialchars($row['category']) ?: 'General'; ?></span>
                                                <span class="msg-preview" title="<?php echo htmlspecialchars($row['message']); ?>">
                                                    "<?php echo htmlspecialchars($row['message']); ?>"
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="empty-state"><i class="fas fa-envelope-open-text mb-2 fs-4"></i><br>No new inquiries from website.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>