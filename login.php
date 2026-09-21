<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query  = "SELECT * FROM pengguna WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['id_pengguna'] = $row['id_pengguna'];
        $_SESSION['username']    = $row['username'];
        $_SESSION['role']        = $row['role'];

        if ($row['role'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: user_dashboard.php");
            exit();
        }
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SPK Nama Islami</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
<div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h4 class="text-center mb-3">Login Admin</h4>
    <p class="text-center text-muted small mb-4">Sistem Pendukung Keputusan Pemilihan Nama Islami</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Masuk sebagai Admin</button>
    </form>

    <hr>
    
    <!-- Tombol Akses Pengguna/Tamu -->
    <div class="text-center">
        <p class="small text-muted mb-2">Ingin mencari rekomendasi nama anak?</p>
        <a href="user_dashboard.php" class="btn btn-outline-success w-100">
            Masuk sebagai Pengguna / Tamu
        </a>
    </div>
</div>
</body>
</html>