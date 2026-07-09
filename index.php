<?php
include 'connection.php'; // Database connection

// 🌟 AJAX BACKEND LOGIC 🌟
if(isset($_POST['ajax_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO inquiries (name, email, company, category, message) VALUES ('$name', '$email', '$company', '$category', '$message')";
    if(mysqli_query($conn, $sql)) {
        echo "success"; 
        exit;
    } else {
        echo "error";
        exit;
    }
}

// Live ERP Stats
$total_clients = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM clients"));
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders"));

// Fetch ALL Products & Categories
$product_query = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$category_query = mysqli_query($conn, "SELECT DISTINCT category FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS Engineering | Premium Mechanical Seals</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #F4F7FB;
            --card-bg: #FFFFFF;
            --text-main: #0B192C; 
            --text-muted: #64748B;
            --accent-cyan: #00D4FF; 
            --accent-blue: #1E3A8A; 
        }

        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-color); color: var(--text-main); overflow-x: hidden; -webkit-font-smoothing: antialiased; }
        h1, h2, h3, h4 { font-family: 'Montserrat', sans-serif; text-transform: uppercase; }

        /* NAVBAR */
        .navbar { background: #ffffff; padding: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-bottom: 3px solid var(--accent-cyan); }
        .nav-link { color: var(--text-main) !important; font-weight: 600; font-size: 15px; text-transform: uppercase; margin: 0 15px; letter-spacing: 1px; transition: 0.2s; cursor: pointer; }
        .nav-link:hover { color: var(--accent-cyan) !important; }
        .btn-erp { background: var(--accent-blue); color: #fff !important; padding: 10px 30px; border-radius: 50px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; transition: 0.2s; }
        .btn-erp:hover { background: var(--accent-cyan); color: var(--text-main) !important; }

        /* HERO SECTION */
        .hero { background: #ffffff; padding: 120px 0 100px; text-align: center; position: relative; }
        .main-logo { max-width: 100%; height: auto; max-height: 250px; margin-bottom: 40px; }
        .hero h1 { font-size: 4rem; font-weight: 900; letter-spacing: -1px; margin-bottom: 20px; color: var(--text-main); }
        .hero h1 span { color: var(--accent-cyan); }
        .hero p { font-size: 1.2rem; color: var(--text-muted); max-width: 700px; margin: 0 auto; line-height: 1.7; font-weight: 400; }

        /* LIVE STATS */
        .stats-bar { background: var(--text-main); padding: 50px 0; color: white; border-top: 4px solid var(--accent-cyan); border-bottom: 4px solid var(--accent-cyan); }
        .stat-box { text-align: center; border-right: 1px solid rgba(255,255,255,0.1); }
        .stat-box:last-child { border: none; }
        .stat-box h2 { font-size: 3.5rem; font-weight: 900; color: var(--accent-cyan); margin-bottom: 5px; line-height: 1; }
        .stat-box p { font-size: 14px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin: 0; opacity: 0.9; }

        /* CATALOG SECTION & FILTER */
        .catalog-section { padding: 100px 0; }
        .section-title { text-align: center; margin-bottom: 40px; }
        .section-title h2 { font-size: 3rem; color: var(--text-main); }
        
        .filter-container { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 50px; border: 1px solid #E2E8F0; }
        .form-select-lg { border: 2px solid var(--accent-blue); font-weight: 700; color: var(--accent-blue); cursor: pointer; box-shadow: none; }

        /* VERTICAL PRODUCT ROW */
        .product-row { background: var(--card-bg); border-radius: 16px; border: 1px solid #E2E8F0; margin-bottom: 40px; display: flex; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .product-row:hover { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); border-color: var(--accent-cyan); transform: translateY(-5px); }

        .p-img-area { width: 35%; background: #ffffff; padding: 40px; display: flex; align-items: center; justify-content: center; border-right: 1px solid #E2E8F0; }
        .p-img-area img { max-width: 100%; max-height: 250px; object-fit: contain; }

        .p-info-area { width: 40%; padding: 40px; }
        .p-category { display: inline-block; background: rgba(0, 212, 255, 0.1); color: var(--accent-blue); padding: 8px 18px; border-radius: 50px; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
        .p-title { font-size: 28px; color: var(--text-main); margin-bottom: 25px; }
        
        .tech-list { list-style: none; padding: 0; margin: 0; }
        .tech-list li { display: flex; align-items: center; margin-bottom: 15px; font-size: 15px; color: var(--text-muted); font-weight: 500; }
        .tech-icon { width: 35px; height: 35px; background: #F1F5F9; color: var(--accent-blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 14px; }
        .tech-list li strong { color: var(--text-main); margin-left: 5px; font-weight: 700; }

        .p-action-area { width: 25%; padding: 40px; background: #F8FAFC; display: flex; flex-direction: column; justify-content: center; text-align: center; border-left: 1px solid #E2E8F0; }
        .price-title { font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .p-price { font-size: 38px; font-weight: 900; color: var(--accent-blue); margin-bottom: 30px; }
        
        .btn-3d { background: #fff; color: var(--accent-blue); border: 2px solid var(--accent-blue); padding: 12px; font-weight: 700; border-radius: 8px; transition: 0.2s; width: 100%; margin-bottom: 15px; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;}
        .btn-3d:hover { background: var(--accent-blue); color: #fff; }
        .btn-quote { background: var(--accent-cyan); color: var(--text-main); border: none; padding: 12px; font-weight: 800; border-radius: 8px; transition: 0.2s; width: 100%; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;}
        .btn-quote:hover { background: var(--text-main); color: var(--accent-cyan); }

        /* CONTACT SECTION */
        .contact-section { background: #ffffff; padding: 100px 0; border-top: 1px solid #E2E8F0; }
        .contact-box { background: var(--bg-color); padding: 40px; border-radius: 16px; height: 100%; border: 1px solid #E2E8F0; }
        .contact-icon-large { width: 60px; height: 60px; background: var(--accent-cyan); color: var(--text-main); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 25px; }
        .form-control { background: #ffffff; border: 1px solid #E2E8F0; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 15px; }
        .btn-submit { background: var(--accent-blue); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; transition: 0.2s; }
        
        #formStatus { display: none; margin-top: 20px; padding: 15px; border-radius: 8px; font-weight: 600; text-align: center; }
        .success-msg { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        /* FOOTER */
        footer { background: var(--text-main); color: rgba(255,255,255,0.6); padding: 60px 0; text-align: center; border-top: 5px solid var(--accent-cyan); }
        
        /* 360 SPIN MODAL */
        .spin-perspective-container { perspective: 1200px; display: flex; justify-content: center; align-items: center; }
        .spin-3d { width: 320px; height: 320px; object-fit: contain; animation: spinY 5s linear infinite; transform-style: preserve-3d; }
        @keyframes spinY { 0% { transform: rotateY(0deg); } 100% { transform: rotateY(360deg); } }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container justify-content-center position-relative">
            <div class="collapse navbar-collapse justify-content-center" id="navContent">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="document.getElementById('catalog').scrollIntoView({behavior: 'smooth'});">Factory Catalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="document.getElementById('contact').scrollIntoView({behavior: 'smooth'});">Contact</a></li>
                    <li class="nav-item ms-lg-5">
                        <a href="admin_login.php" class="btn btn-erp"><i class="fas fa-chart-pie me-2"></i> ERP Portal</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container" data-aos="fade-down">
            <img src="assets/logo.jpg" onerror="this.src='logo.jpg'" alt="MS Mechanical Seal" class="main-logo">
            <h1>Precision <span>Engineered</span> Sealing.</h1>
            <p>India's trusted manufacturer of high-performance mechanical seals. Fully integrated with state-of-the-art ERP systems.</p>
        </div>
    </section>

    <section class="stats-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-4 stat-box" data-aos="fade-up" data-aos-delay="100">
                    <h2><?php echo $total_products; ?>+</h2>
                    <p>Factory Models</p>
                </div>
                <div class="col-md-4 stat-box" data-aos="fade-up" data-aos-delay="200">
                    <h2><?php echo $total_clients; ?>+</h2>
                    <p>B2B Partners</p>
                </div>
                <div class="col-md-4 stat-box" data-aos="fade-up" data-aos-delay="300">
                    <h2><?php echo number_format($total_orders); ?>+</h2>
                    <p>Orders Delivered</p>
                </div>
            </div>
        </div>
    </section>

    <section id="catalog" class="catalog-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Product Directory</h2>
            </div>

            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-lg-6">
                    <div class="filter-container text-center">
                        <select id="categoryFilter" class="form-select form-select-lg" onchange="filterProducts(this.value)">
                            <option value="all">Show All Seal Categories</option>
                            <?php while($cat = mysqli_fetch_assoc($category_query)): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>"><?php echo htmlspecialchars($cat['category']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="productContainer">
                <?php while($row = mysqli_fetch_assoc($product_query)): ?>
                <div class="product-row" data-category="<?php echo htmlspecialchars($row['category']); ?>" data-aos="fade-up">
                    <div class="p-img-area">
                        <img src="assets/<?php echo $row['image']; ?>" onerror="this.src='seal1.png'" alt="Seal" loading="lazy">
                    </div>
                    <div class="p-info-area">
                        <span class="p-category"><?php echo $row['category']; ?></span>
                        <h3 class="p-title"><?php echo $row['name']; ?></h3>
                        <ul class="tech-list">
                            <li><div class="tech-icon"><i class="fas fa-barcode"></i></div> SKU: MS-<?php echo 1000 + $row['id']; ?></li>
                            <li><div class="tech-icon"><i class="fas fa-layer-group"></i></div> Material: <?php echo $row['material']; ?></li>
                            <li><div class="tech-icon"><i class="fas fa-box-open"></i></div> Stock: <?php echo ($row['qty'] > 0) ? $row['qty'].' Ready' : 'MTO'; ?></li>
                        </ul>
                    </div>
                    <div class="p-action-area">
                        <div class="p-price">₹<?php echo number_format($row['price']); ?></div>
                        <button onclick="openFrontend3D('assets/<?php echo $row['image']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')" class="btn-3d"><i class="fas fa-sync-alt me-2"></i> 360° View</button>
                        
                        <button onclick="document.getElementById('contact').scrollIntoView({behavior: 'smooth'});" class="btn-quote w-100">Request Quote</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-title mb-5" data-aos="fade-up">
                <h2>Get In Touch</h2>
            </div>
            
            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="contact-box">
                        <div class="contact-icon-large"><i class="fas fa-map-marker-alt"></i></div>
                        <h3 class="mb-3">Head Office</h3>
                        <p class="text-muted mb-4">Siddhpur, Gujarat - 384151, India</p>
                        <p><i class="fas fa-envelope me-2 text-primary"></i> info@mechsealeng.in</p>
                        <p><i class="fas fa-phone me-2 text-primary"></i> +91 78028 77186</p>
                    </div>
                </div>
                
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="contact-box" style="background: #ffffff;">
                        <h3 class="mb-4">Send an Inquiry</h3>
                        
                        <form id="contactForm">
                            <input type="hidden" name="ajax_submit" value="1">
                            <div class="row">
                                <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Your Name *" required></div>
                                <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Work Email *" required></div>
                            </div>
                            <input type="text" name="company" class="form-control" placeholder="Company Name">
                            <select name="category" class="form-control mb-3">
                                <option value="">Select Category</option>
                                <option>Pusher Seals</option>
                                <option>Cartridge Seals</option>
                            </select>
                            <textarea name="message" class="form-control" rows="4" placeholder="Message..." required></textarea>
                            <button type="submit" id="submitBtn" class="btn-submit">Submit Request <i class="fas fa-paper-plane ms-2"></i></button>
                        </form>
                        
                        <div id="formStatus"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <h4>MS ENGINEERING</h4>
            <p>&copy; 2026 MS Engineering ERP System. All Rights Reserved.</p>
        </div>
    </footer>

    <div class="modal fade" id="frontend3DModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="front3DTitle">360° Inspection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <div class="spin-perspective-container">
                        <img id="front3DImage" src="" class="spin-3d">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });

        // AJAX FORM SUBMISSION LOGIC
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const status = document.getElementById('formStatus');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = 'Sending... <i class="fas fa-spinner fa-spin ms-2"></i>';

            fetch('index.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                if(data.trim() === 'success') {
                    status.className = 'success-msg';
                    status.innerHTML = '<i class="fas fa-check-circle me-2"></i> Inquiry Sent Successfully! We will contact you soon.';
                    status.style.display = 'block';
                    this.reset();
                } else {
                    alert('Error submitting form. Try again.');
                }
            })
            .catch(error => alert('Connection error.'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Submit Request <i class="fas fa-paper-plane ms-2"></i>';
            });
        });

        // Product Filter
        function filterProducts(category) {
            let products = document.querySelectorAll('.product-row');
            products.forEach(prod => {
                if (category === 'all' || prod.getAttribute('data-category') === category) {
                    prod.style.display = 'flex';
                } else {
                    prod.style.display = 'none';
                }
            });
        }

        // 360 View Modal
        function openFrontend3D(img, name) { 
            document.getElementById('front3DImage').src = img; 
            document.getElementById('front3DTitle').innerHTML = name; 
            new bootstrap.Modal(document.getElementById('frontend3DModal')).show(); 
        }
    </script>
</body>
</html>