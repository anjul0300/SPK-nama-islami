<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$current_page = basename($_SERVER['PHP_SELF']);
$admin_user = $_SESSION['username'] ?? $_SESSION['nama'] ?? 'Administrator';
$active = function($pages) use ($current_page) {
    return in_array($current_page, (array)$pages, true) ? 'active' : '';
};
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="includes/admin.css">
<body class="admin-body">
<header class="admin-topbar">
  <div class="admin-topbar-inner">
    <a href="admin_dashboard.php" class="admin-brand">
      <span class="admin-brand-icon"><i class="fa-solid fa-moon"></i></span>
      <span>Islamic <b>Names Finder</b><small></small></span>
    </a>
    <div class="admin-user-area">
      <span class="admin-user"><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($admin_user) ?></span>
      <a href="logout.php" class="admin-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span></a>
    </div>
  </div>
</header>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-section-title">Menu Utama</div>
    <nav class="admin-nav">
      <a href="admin_dashboard.php" class="<?= $active('admin_dashboard.php') ?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
      <a href="kelola_kriteria.php" class="<?= $active('kelola_kriteria.php') ?>"><i class="fa-solid fa-list-check"></i><span>Kriteria</span></a>
      <a href="input_preferensi_kriteria.php" class="<?= $active(['input_preferensi_kriteria.php','preferensi_kriteria.php']) ?>"><i class="fa-solid fa-scale-balanced"></i><span>Prioritas Kriteria</span></a>
      <a href="kelola_alternatif.php" class="<?= $active('kelola_alternatif.php') ?>"><i class="fa-solid fa-book"></i><span>Data Nama</span></a>
      <a href="input_nilai_alternatif.php" class="<?= $active(['input_nilai_alternatif.php','otomatis_nilai_c2_c3.php','proses_nilai_otomatis.php','populate_nilai.php']) ?>"><i class="fa-solid fa-star-half-stroke"></i><span>Nilai Alternatif</span></a>
      <a href="perhitungan_ahp.php" class="<?= $active(['perhitungan_ahp.php','matriks_ahp.php']) ?>"><i class="fa-solid fa-calculator"></i><span>Proses AHP</span></a>
      <a href="hasil_ahp.php" class="<?= $active(['hasil_ahp.php','hasil_perankingan.php']) ?>"><i class="fa-solid fa-ranking-star"></i><span>Hasil Perhitungan</span></a>
    </nav>
    <div class="admin-section-title system-title">Sistem</div>
    <nav class="admin-nav">
      <a href="index.php"><i class="fa-solid fa-house"></i><span>Halaman Pengguna</span></a>
      <a href="logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span></a>
    </nav>
  </aside>
  <main class="admin-main">
