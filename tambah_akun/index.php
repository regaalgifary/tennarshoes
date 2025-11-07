<?php
session_start();
include '../database.php';

// Proses tambah akun
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role         = $_POST['role'];
    $status       = $_POST['status'];

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Karena email & no_hp tidak diinput, set NULL
        $query = "INSERT INTO user (nama_lengkap, username, password, email, no_hp, role, status)
                  VALUES (?, ?, ?, NULL, NULL, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $nama_lengkap, $username, $password, $role, $status);

        if ($stmt->execute()) {
            $success = "Akun berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan akun!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun | Sistem Inventaris Tennar Shoes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #E3EAFD, #F6F8FF);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            padding: 50px 40px;
        }
        .login-left h2 {
            font-weight: 600;
            margin-bottom: 15px;
        }
        .login-left p {
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-login {
            background: #6C63FF;
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px;
            font-weight: 500;
        }
        .btn-login:hover {
            background: #5a52e0;
        }
        .login-right {
            background: linear-gradient(145deg, #6C63FF, #8E80FF);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-right img {
            width: 80%;
            max-width: 300px;
            border-radius: 20px;
        }
        .error, .success {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
            }
            .login-right {
                padding: 20px;
            }
            .login-right img {
                width: 60%;
            }
        }
    </style>
</head>
<body>

<div class="login-card d-flex flex-column flex-md-row">
    <!-- KIRI: Form Tambah Akun -->
    <div class="login-left col-md-6">
        <h2>TAMBAH AKUN</h2>
        <p>Tambah pengguna baru untuk sistem Tennar Shoes</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
            </div>
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="gudang">Gudang</option>
                    <option value="canvas">Canvas</option>
                    <option value="retail">Retail</option>
                    <option value="grosir">Grosir</option>
                </select>
            </div>
            <div class="mb-3">
                <select name="status" class="form-select" required>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-login w-100">Tambah Akun</button>
        </form>

        <div class="text-center mt-3">
            <a href="../" class="text-decoration-none text-muted">← Kembali ke Login</a>
        </div>
    </div>

    <!-- KANAN: Gambar -->
    <div class="login-right col-md-6">
        <img src="tennar.png" alt="Logo Tennar Shoes">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
