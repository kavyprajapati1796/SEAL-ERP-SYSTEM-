    <?php
    session_start();

    // Security Lock
    if (!isset($_SESSION['admin_logged_in'])) {
        header("Location: index.php");
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "seal_erp");
    if ($conn->connect_error) { die("Database Connection Failed!"); }

    // 📊 DATA FOR CHART 1: Revenue by Product
    $product_names = [];
    $product_revenues = [];
    $rev_sql = "SELECT products.name, SUM(orders.total_amount) as revenue 
                FROM orders 
                JOIN products ON orders.product_id = products.id 
                GROUP BY products.name";
    $rev_res = $conn->query($rev_sql);
    if($rev_res) {
        while($row = $rev_res->fetch_assoc()) {
            $product_names[] = $row['name'];
            $product_revenues[] = $row['revenue'];
        }
    }

    // 📊 DATA FOR CHART 2: Orders by Client
    $client_names = [];
    $client_orders = [];
    $cli_sql = "SELECT clients.company_name, COUNT(orders.id) as order_count 
                FROM orders 
                JOIN clients ON orders.client_id = clients.id 
                GROUP BY clients.company_name";
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
        <title>Business Analysis - Admin Portal</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
            body { background-color: #f0f2f5; overflow-x: hidden; }
            
            /* PERFECT SIDEBAR CSS */
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
            
            .chart-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #e5e7eb; height: 100%; }
            .chart-title { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; }
        </style>
    </head>
    <body>

        <div class="sidebar">
            <div class="sidebar-logo"><img src="assets/logo.jpg" alt="MS Logo"></div>
            <ul class="menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
                <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
                <li><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
                <li class="active"><a href="analysis.php"><i class="fas fa-chart-pie"></i> Analysis</a></li>
                <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold text-dark m-0">Business Analytics</h3>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="fas fa-sync-alt me-1"></i> Live Data</span>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="chart-card">
                        <div class="chart-title"><i class="fas fa-chart-bar text-primary me-2"></i> Revenue by Product (₹)</div>
                        <canvas id="revenueChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="chart-card">
                        <div class="chart-title"><i class="fas fa-chart-pie text-success me-2"></i> Order Distribution by Client</div>
                        <canvas id="clientChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Data from PHP to JavaScript
            const productNames = <?php echo json_encode($product_names); ?>;
            const productRevenues = <?php echo json_encode($product_revenues); ?>;
            
            const clientNames = <?php echo json_encode($client_names); ?>;
            const clientOrders = <?php echo json_encode($client_orders); ?>;

            // 📊 1. Render Revenue Bar Chart
            if(productNames.length > 0) {
                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: productNames,
                        datasets: [{
                            label: 'Total Revenue (₹)',
                            data: productRevenues,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 📊 2. Render Client Doughnut Chart
            if(clientNames.length > 0) {
                new Chart(document.getElementById('clientChart'), {
                    type: 'doughnut',
                    data: {
                        labels: clientNames,
                        datasets: [{
                            data: clientOrders,
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(99, 102, 241, 0.7)',
                                'rgba(236, 72, 153, 0.7)',
                                'rgba(14, 165, 233, 0.7)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        </script>
    </body>
    </html>