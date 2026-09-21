<?php
// index.php - Form Input Prioritas Kriteria Pengguna
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Rekomendasi Nama Islami - AHP Dinamis</title>
    <!-- Framework Bootstrap 5 untuk Tampilan UI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white p-4 text-center">
                    <h3 class="mb-1">Pencarian Nama Islami (AHP)</h3>
                    <p class="mb-0 text-white-50">Tentukan prioritas Anda untuk mendapatkan rekomendasi nama yang paling sesuai</p>
                </div>
                <div class="card-body p-4">
                    
                    <form action="hasil_rekomendasi.php" method="POST">
                        
                        <!-- Filter Jenis Kelamin -->
                        <div class="mb-4">
                            <label for="gender" class="form-label fw-bold">1. Filter Jenis Kelamin</label>
                            <select class="form-select" name="gender" id="gender" required>
                                <option value="Semua">Semua (Laki-laki & Perempuan)</option>
                                <option value="L">Laki-laki Saja (L)</option>
                                <option value="P">Perempuan Saja (P)</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-primary">Atur Tingkat Kepentingan Kriteria</h5>

                        <!-- Kriteria C1 -->
                        <div class="mb-3">
                            <label for="pilihan_c1" class="form-label fw-semibold">
                                Keindahan & Kebaikan Arti Nama (C1)
                            </label>
                            <select class="form-select" name="pilihan_c1" id="pilihan_c1" required>
                                <option value="1">1 - Kurang Diutamakan</option>
                                <option value="2">2 - Cukup Diutamakan</option>
                                <option value="3" selected>3 - Standar / Netral</option>
                                <option value="4">4 - Penting</option>
                                <option value="5">5 - Sangat Penting (Utama)</option>
                            </select>
                        </div>

                        <!-- Kriteria C2 -->
                        <div class="mb-3">
                            <label for="pilihan_c2" class="form-label fw-semibold">
                                Kesesuaian Gender / Kejelasan Nama (C2)
                            </label>
                            <select class="form-select" name="pilihan_c2" id="pilihan_c2" required>
                                <option value="1">1 - Kurang Diutamakan</option>
                                <option value="2">2 - Cukup Diutamakan</option>
                                <option value="3" selected>3 - Standar / Netral</option>
                                <option value="4">4 - Penting</option>
                                <option value="5">5 - Sangat Ketat / Mutlak</option>
                            </select>
                        </div>

                        <!-- Kriteria C3 -->
                        <div class="mb-4">
                            <label for="pilihan_c3" class="form-label fw-semibold">
                                Nilai Keislaman / Latar Belakang Tokoh (C3)
                            </label>
                            <select class="form-select" name="pilihan_c3" id="pilihan_c3" required>
                                <option value="1">1 - Kurang Diutamakan</option>
                                <option value="2">2 - Cukup Diutamakan</option>
                                <option value="3" selected>3 - Standar / Netral</option>
                                <option value="4">4 - Penting</option>
                                <option value="5">5 - Sangat Penting (Harus Nama Nabi / Sahabat)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            Hitung & Tampilkan Rekomendasi
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>