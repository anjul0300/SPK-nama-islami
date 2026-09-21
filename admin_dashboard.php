<?php
include_once 'koneksi.php';
include_once 'auth.php';
require_role('admin');

function admin_count($koneksi, $table) {
    $allowed = ['kriteria','alternatif','nilai_alternatif','preferensi_kriteria'];
    if (!in_array($table, $allowed, true)) return 0;
    $q = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM $table");
    if (!$q) return 0;
    $r = mysqli_fetch_assoc($q);
    return (int)$r['total'];
}

$jumlah_kriteria = admin_count($koneksi, 'kriteria');
$jumlah_nama = admin_count($koneksi, 'alternatif');
$jumlah_nilai = admin_count($koneksi, 'nilai_alternatif');
$jumlah_pref = admin_count($koneksi, 'preferensi_kriteria');

$pref_lengkap = ($jumlah_pref >= 3);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - SPK Nama Islami</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="includes/admin.css">
</head>
<?php include __DIR__ . "/includes/admin_header.php"; ?><main class="admin-main">
        <div class="mb-4">
            <h1 class="admin-page-title">Dashboard Admin</h1>
            <p class="admin-page-subtitle">Kelola data SPK, bobot AHP, nilai alternatif, dan hasil perhitungan dari satu tempat.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-list-check"></i></div>
                    <div class="stat-number"><?= $jumlah_kriteria ?></div>
                    <div class="stat-label">Kriteria</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-book"></i></div>
                    <div class="stat-number"><?= $jumlah_nama ?></div>
                    <div class="stat-label">Nama Alternatif</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-star"></i></div>
                    <div class="stat-number"><?= $jumlah_nilai ?></div>
                    <div class="stat-label">Data Nilai Alternatif</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon <?= $pref_lengkap ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                        <i class="fa-solid <?= $pref_lengkap ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                    </div>
                    <div class="stat-number"><?= $jumlah_pref ?>/3</div>
                    <div class="stat-label">Perbandingan Kriteria</div>
                </div>
            </div>
        </div>

        <div class="admin-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Alur Kerja Admin</h4>
                    <p class="text-muted small mb-0">Ikuti urutan ini agar data tidak tertukar dan proses AHP mudah diperiksa.</p>
                </div>
                <span class="badge rounded-pill <?= $pref_lengkap ? 'text-bg-success' : 'text-bg-warning' ?>">
                    <?= $pref_lengkap ? 'Data preferensi siap' : 'Preferensi belum lengkap' ?>
                </span>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h5>Kriteria</h5>
                        <p>Pastikan hanya ada C1 Arti Nama, C2 Tingkat Keunikan, dan C3 Nilai Keislaman.</p>
                        <a href="kelola_kriteria.php" class="btn btn-outline-success btn-sm btn-admin">Buka</a>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h5>Prioritas</h5>
                        <p>Bandingkan tiga pasangan kriteria untuk mendapatkan bobot AHP.</p>
                        <a href="input_preferensi_kriteria.php" class="btn btn-outline-success btn-sm btn-admin">Atur</a>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h5>Data Nama</h5>
                        <p>Tambah, ubah, atau hapus nama Islami yang menjadi alternatif.</p>
                        <a href="kelola_alternatif.php" class="btn btn-outline-success btn-sm btn-admin">Kelola</a>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h5>Nilai</h5>
                        <p>Isi C1, C2, dan C3 untuk masing-masing nama alternatif.</p>
                        <a href="input_nilai_alternatif.php" class="btn btn-outline-success btn-sm btn-admin">Isi Nilai</a>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <h5>Proses</h5>
                        <p>Sistem menghitung matriks, bobot, CI, CR, dan nilai akhir.</p>
                        <a href="perhitungan_ahp.php" class="btn btn-outline-success btn-sm btn-admin">Proses</a>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="step-card">
                        <div class="step-number">6</div>
                        <h5>Hasil</h5>
                        <p>Lihat bobot kriteria dan ranking nama setelah perhitungan selesai.</p>
                        <a href="hasil_ahp.php" class="btn btn-success btn-sm btn-admin">Lihat</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card p-4">
            <h4 class="fw-bold mb-3">Struktur Kriteria Penelitian</h4>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Kode</th><th>Kriteria</th><th>Fungsi</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><span class="badge text-bg-success">C1</span></td><td><strong>Arti Nama</strong></td><td class="text-muted">Menilai kualitas/makna nama.</td></tr>
                        <tr><td><span class="badge text-bg-info">C2</span></td><td><strong>Tingkat Keunikan</strong></td><td class="text-muted">Menilai tingkat keunikan nama.</td></tr>
                        <tr><td><span class="badge text-bg-warning">C3</span></td><td><strong>Nilai Keislaman</strong></td><td class="text-muted">Menilai tingkat nilai keislaman nama.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="fa-solid fa-circle-info me-1"></i>
                Jenis kelamin digunakan untuk penyaringan rekomendasi pengguna dan <strong>bukan</strong> kriteria AHP.
            </div>
        </div>

        <div class="admin-footer">SPK Pemilihan Nama-Nama Islami · Analytical Hierarchy Process (AHP)</div>
    <?php include __DIR__ . "/includes/admin_footer.php"; ?>
