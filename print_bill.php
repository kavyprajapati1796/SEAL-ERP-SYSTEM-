<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "seal_erp");
if ($conn->connect_error) { die("Database Connection Failed!"); }

if(!isset($_GET['id'])) { die("Order ID missing!"); }
$order_id = $_GET['id'];

$sql = "SELECT orders.*, clients.company_name, clients.contact_person, clients.email, clients.phone, 
        products.name AS product_name, products.price 
        FROM orders 
        JOIN clients ON orders.client_id = clients.id 
        JOIN products ON orders.product_id = products.id 
        WHERE orders.id = $order_id";

$result = $conn->query($sql);
if($result->num_rows == 0) { die("Order not found!"); }
$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - <?php echo $order['order_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; color: #111827; }
        .invoice-container { background: #fff; max-width: 850px; margin: 40px auto; padding: 50px; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        
        /* 🌟 PREMIUM LOGO & HEADER 🌟 */
        .header-logo { max-height: 80px; object-fit: contain; }
        .invoice-header { border-bottom: 3px solid #1e3a8a; padding-bottom: 25px; margin-bottom: 35px; }
        .factory-name { font-size: 32px; font-weight: 800; color: #1e3a8a; letter-spacing: 1px; }
        .invoice-title { font-size: 40px; font-weight: 800; color: #e5e7eb; text-transform: uppercase; letter-spacing: 5px; margin-bottom: -10px; }
        
        /* 🌟 DYNAMIC STAMP CSS (FIXED) 🌟 */
        .status-stamp { font-size: 26px; font-weight: 800; padding: 10px 20px; border: 4px solid; border-radius: 8px; transform: rotate(-10deg); opacity: 0.8; display: inline-block; letter-spacing: 1px;}
        .stamp-pending { color: #dc3545; border-color: #dc3545; }
        .stamp-paid { color: #198754; border-color: #198754; }

        .table-custom th { background-color: #1e3a8a !important; color: white !important; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; padding: 15px; }
        .table-custom td { padding: 15px; vertical-align: middle; border-color: #e5e7eb; }
        .total-row { font-size: 22px; font-weight: 800; background-color: #f8fafc; color: #1e3a8a; }
        
        .signature-box { margin-top: 60px; text-align: right; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.03; width: 60%; z-index: 0; pointer-events: none; }
        
        @media print {
            body { background-color: #fff; }
            .invoice-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container invoice-container">
        
        <img src="assets/logo.jpg" class="watermark" alt="Watermark" onerror="this.style.display='none'">

        <div class="text-end mb-4 no-print position-relative" style="z-index: 20;">
            <button onclick="window.print()" class="btn btn-dark px-4 shadow-sm"><i class="fas fa-print"></i> Print Professional Invoice</button>
        </div>

        <div class="invoice-header d-flex justify-content-between align-items-center position-relative" style="z-index: 20;">
            <div class="d-flex align-items-center gap-4">
                <img src="assets/logo.jpg" class="header-logo" alt="MS Logo" onerror="this.style.display='none'">
                <div>
                    <div class="factory-name">MS ENGINEERING</div>
                    <div class="text-secondary fw-medium">A-1 Heera Panna Complex, Nr Kotak Bank</div>
                    <div class="text-secondary fw-medium">Bus Stand Road, Siddhpur - 384151</div>
                    <div class="mt-2 text-dark"><small><b class="text-primary">Ph:</b> +91 78028 77186 | <b class="text-primary">Email:</b> info@mechsealeng.in</small></div>
                    <div class="text-dark"><small><b class="text-primary">Web:</b> www.windkooky.com</small></div>
                </div>
            </div>
            <div class="text-end">
                <div class="invoice-title">INVOICE</div>
                <div class="fw-bold mt-1 fs-5 text-dark">#<?php echo $order['order_no']; ?></div>
                <div class="text-muted fw-medium">Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
            </div>
        </div>

        <div class="row mb-5 position-relative align-items-center" style="z-index: 20;">
            <div class="col-sm-7">
                <div class="px-4 py-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-primary mb-2">BILLED TO:</h6>
                    <div class="fw-bold text-dark" style="font-size: 20px;"><?php echo $order['company_name']; ?></div>
                    <div class="text-secondary mt-1"><i class="fas fa-user text-muted me-2"></i> Attn: <?php echo $order['contact_person']; ?></div>
                    <div class="text-secondary"><i class="fas fa-envelope text-muted me-2"></i> <?php echo $order['email']; ?></div>
                    <div class="text-secondary"><i class="fas fa-phone text-muted me-2"></i> +91 <?php echo $order['phone']; ?></div>
                </div>
            </div>
            
            <div class="col-sm-5 text-center mt-4 mt-sm-0">
                <?php if($order['status'] == 'Pending'): ?>
                    <div class="status-stamp stamp-pending">PAYMENT PENDING</div>
                <?php else: ?>
                    <div class="status-stamp stamp-paid">PAYMENT RECEIVED</div>
                <?php endif; ?>
            </div>
        </div>

        <table class="table table-bordered table-custom position-relative" style="z-index: 20;">
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="50%">Product Description</th>
                    <th width="15%" class="text-center">Quantity</th>
                    <th width="15%" class="text-end">Unit Price</th>
                    <th width="15%" class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center fw-bold text-secondary">1</td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 16px;"><?php echo $order['product_name']; ?></div>
                        <small class="text-muted">High-Quality Industrial Mechanical Seal</small>
                    </td>
                    <td class="text-center fw-medium"><?php echo $order['qty']; ?> Nos</td>
                    <td class="text-end text-secondary fw-medium">₹<?php echo number_format($order['price']); ?></td>
                    <td class="text-end fw-bold text-dark">₹<?php echo number_format($order['qty'] * $order['price']); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-end">GRAND TOTAL:</td>
                    <td class="text-end">₹<?php echo number_format($order['total_amount']); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="row mt-5 position-relative" style="z-index: 20;">
            <div class="col-sm-6">
                <div class="fw-bold text-dark mb-2">Terms & Conditions:</div>
                <div class="text-secondary" style="font-size: 13px; line-height: 1.6;">
                    1. Payment is due within 30 days of the invoice date.<br>
                    2. Goods once sold will not be taken back or exchanged.<br>
                    3. All disputes are subject to Siddhpur jurisdiction.
                </div>
            </div>
            <div class="col-sm-6 signature-box">
                <div class="mb-5 fw-bold text-dark">For MS ENGINEERING</div>
                <div class="mt-4 border-top border-dark border-2 d-inline-block pt-2 px-4 text-dark fw-bold">Shaikh Mohsin <br><small class="text-muted fw-normal">(Founder)</small></div>
            </div>
        </div>

        <div style="height: 8px; background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%); margin-top: 50px; border-radius: 4px;"></div>
    </div>
</body>
</html>