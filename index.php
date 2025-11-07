<?php
session_start();
include 'database.php';

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'nonaktif') {
                $error = "Akun Anda tidak aktif. Hubungi admin.";
            } else {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];

                switch ($user['role']) {
                    case 'gudang': header("Location: dashboard_gudang"); break;
                    case 'canvas': header("Location: dashboard_canvas.php"); break;
                    case 'retail': header("Location: dashboard_retail.php"); break;
                    case 'grosir': header("Location: dashboard_grosir.php"); break;
                    default: header("Location: dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Tennar Shoes Inventory System</title>
  <link rel="icon" type="image/png" href="images/tennar.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
      background-color: #f4f7fb;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      overflow: hidden;
      max-width: 1000px;
      width: 100%;
      display: flex;
      min-height: 550px;
    }

    /* Bagian kiri: biru dengan logo & deskripsi */
    .login-left {
      flex: 1;
      background-color: #29548a;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 50px 40px;
      text-align: center;
    }

    .login-left img.logo {
      width: 200px;
      margin-bottom: 25px;
    }

    .login-left h3 {
      font-weight: 700;
      font-size: 24px;
      margin-bottom: 15px;
    }

    .login-left p {
      font-size: 15px;
      line-height: 1.7;
      color: #f1f5ff;
      max-width: 360px;
    }

    /* Bagian kanan (form login) */
    .login-right {
      flex: 1.2;
      padding: 60px 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-color: #fff;
    }

    .login-right h2 {
      font-weight: 700;
      margin-bottom: 25px;
      color: #29548a;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px;
      border: 1.5px solid #ccc;
      margin-bottom: 15px;
      font-size: 15px;
    }

    .btn-login {
      background-color: #29548a;
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 12px;
      font-weight: 600;
      transition: 0.3s;
      font-size: 15px;
    }

    .btn-login:hover {
      background-color: #1f3e6d;
    }

    a.text-muted {
      font-size: 14px;
      text-decoration: none;
      color: #666;
    }

    a.text-muted:hover {
      color: #29548a;
    }

    /* Responsif */
    @media (max-width: 992px) {
      .login-card {
        flex-direction: column;
        max-width: 480px;
      }
      .login-left, .login-right {
        width: 100%;
        padding: 40px 30px;
      }
      .login-left {
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
      }
    }
  </style>
</head>
<body>

<div class="login-card">
  <!-- Kiri -->
  <div class="login-left">
    <img src="images/tennar.png" alt="Logo Tennar" class="logo">
    <h3>Tennar Shoes Inventory System</h3>
    <p>
      Sistem manajemen inventori modern untuk mempermudah pengelolaan stok, pemesanan, dan distribusi produk Tennar Shoes secara efisien dan real-time.
    </p>
  </div>

  <!-- Kanan -->
  <div class="login-right">
    <h2>Masuk ke Sistem</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" class="form-control" placeholder="Username" required>
      <input type="password" name="password" class="form-control" placeholder="Password" required>
      <button type="submit" class="btn btn-login w-100">Login</button>
    </form>
    <div class="text-center mt-3">
      <a href="tambah_akun" class="text-muted">+ Tambah Akun Baru</a>
    </div>
  </div>
</div>

</body>
</html>
