<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

include 'connection.php'; // Database Connection
if ($conn->connect_error) { die("Database Connection Failed!"); }

// 🌟 DELETE INQUIRY LOGIC 🌟
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']); // Security ke liye intval use kiya
    $sql = "DELETE FROM inquiries WHERE id = $del_id";
    
    if ($conn->query($sql) === TRUE) {
        // Delete hone ke baad page refresh ho jayega
        header("Location: inquiries.php");
        exit();
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
    }
}

// Fetch all inquiries
$inquiries = $conn->query("SELECT * FROM inquiries ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Inquiries - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; overflow-x: hidden; }
        
        /* SIDEBAR */
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
        
        .msg-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e5e7eb; border-left: 5px solid #0dcaf0; transition: 0.3s; }
        .msg-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
        .action-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="assets/logo.jpg" onerror="this.src='logo.jpg'" alt="MS Logo"></div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cogs"></i> Mechanical Seals</a></li>
            <li><a href="clients.php"><i class="fas fa-users"></i> Client Companies</a></li>
            <li><a href="orders.php"><i class="fas fa-file-invoice"></i> Orders & Billing</a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-pie"></i> Analysis</a></li>
            <li class="mt-4"><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0">Web Inquiries (Leads)</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i> Back to Dashboard</a>
        </div>

        <?php if($inquiries && mysqli_num_rows($inquiries) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($inquiries)): ?>
            <div class="msg-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['name']); ?></h4>
                        <div class="text-muted"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($row['company']) ?: 'No Company Given'; ?></div>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></span>
                </div>
                
                <div class="d-flex gap-4 mb-3 pb-3 border-bottom">
                    <div class="text-primary fw-medium"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                    <div class="text-info fw-medium"><i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($row['category']) ?: 'General Inquiry'; ?></div>
                </div>
                
                <div class="bg-light p-4 rounded text-secondary mb-3" style="font-size: 15px; border: 1px solid #f3f4f6;">
                    "<?php echo nl2br(htmlspecialchars($row['message'])); ?>"
                </div>
                
                <div class="action-buttons">
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($row['email']); ?>&su=Reply to your MS Engineering Inquiry" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fab fa-google me-2"></i> Reply via Gmail
                    </a>
                    
                    <a href="inquiries.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-outline-danger rounded-pill px-4 fw-bold" onclick="return confirm('Are you sure you want to delete this message permanently?');">
                        <i class="fas fa-trash-alt me-2"></i> Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center p-5 bg-white rounded-4 border shadow-sm">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4 class="text-muted fw-bold">No website inquiries yet!</h4>
                <p class="text-secondary">Messages sent from the website contact form will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>