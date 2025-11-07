<?php
session_start();

// Hapus semua data sesi
$_SESSION = [];

// Hancurkan sesi
session_destroy();

// Arahkan ke halaman login utama
header("Location: ../tennarshoes");
exit;
