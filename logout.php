<?php
session_start(); // Session chalu karo
session_unset(); // Saara data (jaise admin_logged_in) hata do
session_destroy(); // Session ko poori tarah khatam kar do

// Wapas Login page par bhej do
header("Location: admin_login.php");
exit();
?>