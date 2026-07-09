<?php
session_start();
include 'connection.php'; // Database se connection

$error_msg = ""; // Error dikhane ke liye khali variable

// Jab user Login button dabayega
if (isset($_POST['login_btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database me check kar rahe hain
    $query = "SELECT * FROM users WHERE email='$email' AND Password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Agar details match ho gayi toh Dashboard par bhej do
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        // Agar match nahi hui toh error dikhao
        $error_msg = "Galat Email ya Password! Wapas try karo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Pehle wala CSS same rahega */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-wrapper { background: rgba(255, 255, 255, 0.98); width: 420px; padding: 45px 40px; border-radius: 12px; box-shadow: 0px 20px 40px rgba(0, 0, 0, 0.4); text-align: center; }
        .logo-img { max-width: 150px; margin-bottom: 15px; }
        .login-wrapper h2 { color: #111; font-weight: 600; margin-bottom: 5px; font-size: 26px; letter-spacing: 0.5px; }
        .login-wrapper p { color: #666; font-size: 14px; margin-bottom: 25px; }
        .input-box { text-align: left; margin-bottom: 25px; }
        .input-box label { display: block; font-size: 13px; color: #333; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-box input { width: 100%; padding: 14px 15px; border: 2px solid #e1e5ee; border-radius: 8px; font-size: 15px; outline: none; transition: all 0.3s ease; background-color: #f8f9fa; }
        .input-box input:focus { border-color: #2c5364; background-color: #fff; box-shadow: 0 0 8px rgba(44, 83, 100, 0.2); }
        .btn-login { width: 100%; padding: 14px; background: linear-gradient(to right, #203a43, #2c5364); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(44, 83, 100, 0.4); }
        .error-text { color: red; font-size: 14px; margin-bottom: 15px; font-weight: 500; }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <img src="assets/logo.jpg" alt="Company Logo" class="logo-img">
        
        <h2>Admin Portal</h2>
        <p>Secure access to ERP system</p>

        <?php if($error_msg != "") { echo "<div class='error-text'>$error_msg</div>"; } ?>

        <form action="" method="POST" autocomplete="off">
            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="admin@ms-seal.com" autocomplete="new-password" required>
            </div>
            <div class="input-box">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" autocomplete="new-password" required>
            </div>
            <button type="submit" name="login_btn" class="btn-login">Secure Login</button>
        </form>
    </div>

</body>
</html>