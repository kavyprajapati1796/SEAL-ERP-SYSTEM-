<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { die("Database Connection Failed!"); }

// 💰 1. FINANCIAL SUMMARY DATA 
$total_paid = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status='Paid'")->fetch_assoc()['total'] ?? 0;
$total_pending = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(id) as total FROM orders")->fetch_assoc()['total'] ?? 0;

// 📊 2. DATA FOR CHART 1: Revenue by Product (Top 5)
$product_names = [];
$product_revenues = [];
$rev_sql = "SELECT products.name, SUM(orders.total_amount) as revenue 
            FROM orders 
            JOIN products ON orders.product_id = products.id 
            GROUP BY products.name ORDER BY revenue DESC LIMIT 5";
$rev_res = $conn->query($rev_sql);
if($rev_res) {
    while($row = $rev_res->fetch_assoc()) {
        $product_names[] = $row['name'];
        $product_revenues[] = $row['revenue'];
    }
}

// 📊 3. DATA FOR CHART 2: Orders by Client (Top 5)
$client_names = [];
$client_orders = [];
$cli_sql = "SELECT clients.company_name, COUNT(orders.id) as order_count 
            FROM orders 
            JOIN clients ON orders.client_id = clients.id 
            GROUP BY clients.company_name ORDER BY order_count DESC LIMIT 5";
$cli_res = $conn->query($cli_sql);
if($cli_res) {
    while($row = $cli_res->fetch_assoc()) {
        $client_names[] = $row['company_name'];
        $client_orders[] = $row['order_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Analytics - MS Engineering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f7f6; overflow-x: hidden; color: #2c3e50; }
        
        /* 🌟 SIDEBAR 🌟 */
        .sidebar { width: 260px; background: #111827; height: 100vh; color: white; position: fixed; top: 0; left: 0; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 999; }
        .sidebar-logo { text-align: center; padding: 25px 20px; border-bottom: 1px solid #1f2937; }
        .sidebar-logo img { max-width: 130px; border-radius: 6px; background: white; padding: 5px; }
        .menu { list-style: none; margin-top: 20px; padding: 0; }
        .menu li { padding: 12px 25px; margin: 5px 15px; border-radius: 8px; transition: 0.3s; }
        .menu li.active { background: #3b82f6; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3); }
        .menu li:hover:not(.active) { background: #1f2937; cursor: pointer; }
        .menu li a { color: #d1d5db; text-decoration: none; font-size: 15px; display: flex; align-items: center; gap: 15px; }
        .menu li.active a { color: white; font-weight: 500; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        /* 🌟 MODERN FINANCIAL CARDS 🌟 */
        .fin-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: none; position: relative; overflow: hidden; transition: 0.3s; }
        .fin-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .fin-icon { position: absolute; right: -15px; top: -15px; font-size: 100px; opacity: 0.05; }
        .fin-title { font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #7f8c8d; margin-bottom: 10px; }
        .fin-value { font-size: 32px; font-weight: 800; color: #2c3e50; }
        
        /* 🌟 CHART CONTAINERS 🌟 */
        .chart-wrapper { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); height: 100%; border-top: 4px solid #3b82f6; }
        .chart-header { font-size: 18px; font-weight: 700; color: #1e3a8a; border-bottom: 2px solid #f1f2f6; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="assets/logo.jpg" alt="MS Logo" onerror="this.style.display='none'"></div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
            <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
            <li><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
            <li class="active"><a href="analytics.php"><i class="fas fa-chart-pie"></i> Analysis</a></li>
            <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark m-0" style="letter-spacing: -1px;">Financial Analytics</h2>
                <p class="text-muted mt-1 mb-0">Live revenue and sales tracking for MS Engineering.</p>
            </div>
            <button class="btn btn-dark rounded-pill px-4 py-2 shadow-sm" onclick="window.location.reload();"><i class="fas fa-sync-alt me-2"></i> Refresh Data</button>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="fin-card" style="border-bottom: 4px solid #10b981;">
                    <i class="fas fa-wallet fin-icon text-success"></i>
                    <div class="fin-title">Total Revenue (Paid)</div>
                    <div class="fin-value text-success">₹<?php echo number_format($total_paid); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="fin-card" style="border-bottom: 4px solid #ef4444;">
                    <i class="fas fa-hand-holding-usd fin-icon text-danger"></i>
                    <div class="fin-title">Pending Payments</div>
                    <div class="fin-value text-danger">₹<?php echo number_format($total_pending); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="fin-card" style="border-bottom: 4px solid #3b82f6;">
                    <i class="fas fa-shopping-cart fin-icon text-primary"></i>
                    <div class="fin-title">Total Orders</div>
                    <div class="fin-value text-primary"><?php echo number_format($total_orders); ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="chart-wrapper">
                    <div class="chart-header">
                        <span><i class="fas fa-chart-bar me-2 text-primary"></i> Top Selling Products (Revenue)</span>
                        <span class="badge bg-light text-dark border">All Time</span>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="revenueBarChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="chart-wrapper" style="border-top-color: #8b5cf6;">
                    <div class="chart-header">
                        <span><i class="fas fa-chart-pie me-2" style="color: #8b5cf6;"></i> Client Order Distribution</span>
                    </div>
                    <div style="height: 350px; display: flex; justify-content: center;">
                        <canvas id="clientDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const pNames = <?php echo json_encode($product_names); ?>;
        const pRevs = <?php echo json_encode($product_revenues); ?>;
        const cNames = <?php echo json_encode($client_names); ?>;
        const cOrders = <?php echo json_encode($client_orders); ?>;

        // Common Chart Defaults for Premium Look
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#7f8c8d';

        // 1. STYLISH BAR CHART
        if(pNames.length > 0) {
            new Chart(document.getElementById('revenueBarChart'), {
                type: 'bar',
                data: {
                    labels: pNames,
                    datasets: [{
                        label: 'Revenue Generated (₹)',
                        data: pRevs,
                        backgroundColor: 'rgba(59, 130, 246, 0.85)', // Premium Blue
                        hoverBackgroundColor: 'rgba(30, 58, 138, 1)',
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#111827',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 14 },
                            callbacks: {
                                label: function(context) {
                                    return ' ₹ ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f2f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. MODERN DOUGHNUT CHART
        if(cNames.length > 0) {
            new Chart(document.getElementById('clientDoughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: cNames,
                    datasets: [{
                        data: cOrders,
                        backgroundColor: [
                            '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%', // Makes it look like a thin, elegant ring
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' } },
                        tooltip: {
                            backgroundColor: '#111827',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.raw + ' Orders';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>