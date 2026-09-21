<?php
// 1. PAKSA PHP MENAMPILKAN ERROR (Sangat penting agar tidak muncul layar putih polos)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';
include_once 'auth.php';

require_role('admin');

$pesan_sukses = "";
$pesan_error = "";

// PROSES EDIT KRITERIA (Ketika tombol simpan ditekan)
if (isset($_POST['update_kriteria'])) {
    $id_kriteria = $_POST['id_kriteria'];
    $nama_kriteria = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);

    $query_update = "UPDATE kriteria SET nama_kriteria='$nama_kriteria' WHERE id_kriteria='$id_kriteria'";
    if (mysqli_query($koneksi, $query_update)) {
        $pesan_sukses = "Kriteria berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui kriteria: " . mysqli_error($koneksi);
    }
}

// AMBIL DATA KRITERIA DARI DATABASE
$query = "SELECT * FROM kriteria ORDER BY kode_kriteria ASC";
$result = mysqli_query($koneksi, $query);

// 2. CEK JIKA QUERY GAGAL (Antisipasi Fatal Error PHP 8)
if (!$result) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:2px solid red; background:#fff5f5;'>
            <h3>Query Database Gagal!</h3>
            <b>Pesan Error:</b> " . mysqli_error($koneksi) . "<br><br>
            <b>Solusi:</b> Pastikan Anda sudah menjalankan perintah SQL Pembuatan Tabel Kriteria yang ada di <b>Tahap 2</b> melalui phpMyAdmin.
         </div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kriteria - Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="includes/admin.css">
</head>
<?php include __DIR__ . "/includes/admin_header.php"; ?>

<div class="admin-page-head">
    <div>
        <h1 class="admin-page-title">Kelola Kriteria</h1>
        <p class="admin-page-subtitle">Kelola tiga kriteria yang digunakan dalam perhitungan AHP.</p>
    </div>
    <span class="admin-page-badge"><i class="fa-solid fa-scale-balanced"></i> Metode AHP</span>
</div>

<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Kriteria Penilaian</h4>
            <p class="text-muted small mb-0">Sistem menggunakan C1 Arti Nama, C2 Tingkat Keunikan, dan C3 Nilai Keislaman.</p>
        </div>
    </div>

                <hr>

                <!-- Notifikasi Sistem -->
                <?php if($pesan_sukses != ""): ?>
                    <div class="alert alert-success py-2 small"><?= $pesan_sukses; ?></div>
                <?php endif; ?>
                <?php if($pesan_error != ""): ?>
                    <div class="alert alert-danger py-2 small"><?= $pesan_error; ?></div>
                <?php endif; ?>
                
                <!-- Tabel Kriteria -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success text-center">
                            <tr>
                                <th style="width: 10%;">No</th>
                                <th style="width: 20%;">Kode Kriteria</th>
                                <th style="width: 50%;">Nama Kriteria dalam Skripsi</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><strong><?= $row['kode_kriteria']; ?></strong></td>
                                <td><?= $row['nama_kriteria']; ?></td>
                                <td class="text-center">
                                    <!-- Tombol Trigger Modal Edit -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_kriteria']; ?>">
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL POPUP UNTUK EDIT DATA KRITERIA -->
                            <div class="modal fade" id="editModal<?= $row['id_kriteria']; ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog">
                                <form action="" method="POST">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title">Ubah Data Kriteria <?= $row['kode_kriteria']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        <input type="hidden" name="id_kriteria" value="<?= $row['id_kriteria']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Kode Kriteria</label>
                                            <input type="text" class="form-control bg-light" value="<?= $row['kode_kriteria']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kriteria</label>
                                            <input type="text" class="form-control" name="nama_kriteria" value="<?= $row['nama_kriteria']; ?>" required>
                                        </div>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="update_kriteria" class="btn btn-success btn-sm">Simpan Perubahan</button>
                                      </div>
                                    </div>
                                </form>
                              </div>
                            </div>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

</div>

<?php include __DIR__ . "/includes/admin_footer.php"; ?>
