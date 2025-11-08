<?php
// database.php
$host = "localhost";
$user = "root";        
$pass = "";            
$db   = "tennarshoes";

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
