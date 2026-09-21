<?php

// ============================================================
// SESSION
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';


// ============================================================
// CEK LOGIN ADMIN
// ============================================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// ============================================================
// AMBIL 3 KRITERIA
// ============================================================

$query = "
    SELECT id_kriteria, kode_kriteria, nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Gagal mengambil data kriteria: " . mysqli_error($koneksi));
}

$kriteria = [];

while ($row = mysqli_fetch_assoc($result)) {
    $kriteria[] = $row;
}


// ============================================================
// CEK JUMLAH KRITERIA
// ============================================================

if (count($kriteria) != 3) {
    die("
        <div style='
            font-family: Arial;
            max-width: 700px;
            margin: 80px auto;
            padding: 30px;
        '>

            <h3>Kriteria Belum Sesuai</h3>

            <p>
                Sistem membutuhkan tepat 3 kriteria.
            </p>

            <p>
                Jumlah kriteria saat ini:
                <strong>" . count($kriteria) . "</strong>
            </p>

            <a href='kelola_kriteria.php'>
                Periksa Kriteria
            </a>

        </div>
    ");
}


// ============================================================
// ID KRITERIA
// ============================================================

$c1 = (int) $kriteria[0]['id_kriteria'];
$c2 = (int) $kriteria[1]['id_kriteria'];
$c3 = (int) $kriteria[2]['id_kriteria'];


// ============================================================
// NAMA KRITERIA
// ============================================================

$nama_c1 = $kriteria[0]['nama_kriteria'];
$nama_c2 = $kriteria[1]['nama_kriteria'];
$nama_c3 = $kriteria[2]['nama_kriteria'];


// ============================================================
// PESAN
// ============================================================

$pesan = '';
$error = '';


// ============================================================
// NILAI YANG DIPERBOLEHKAN
// ============================================================

$nilai_valid = [
    '0.1111',
    '0.1429',
    '0.2',
    '0.3333',
    '1',
    '3',
    '5',
    '7',
    '9'
];


// ============================================================
// SIMPAN PREFERENSI
// ============================================================

if (isset($_POST['simpan'])) {

    $nilai_c1_c2 = $_POST['nilai_c1_c2'] ?? '';
    $nilai_c1_c3 = $_POST['nilai_c1_c3'] ?? '';
    $nilai_c2_c3 = $_POST['nilai_c2_c3'] ?? '';


    // ========================================================
    // VALIDASI
    // ========================================================

    if (
        !in_array($nilai_c1_c2, $nilai_valid, true) ||
        !in_array($nilai_c1_c3, $nilai_valid, true) ||
        !in_array($nilai_c2_c3, $nilai_valid, true)
    ) {

        $error = "Silakan pilih nilai AHP untuk semua perbandingan.";

    } else {

        $nilai_c1_c2_db = (float) $nilai_c1_c2;
        $nilai_c1_c3_db = (float) $nilai_c1_c3;
        $nilai_c2_c3_db = (float) $nilai_c2_c3;


        // ====================================================
        // HAPUS DATA PREFERENSI LAMA
        // ====================================================

        $hapus = mysqli_query(
            $koneksi,
            "DELETE FROM preferensi_kriteria"
        );

        if (!$hapus) {

            $error =
                "Gagal menghapus preferensi lama: "
                . mysqli_error($koneksi);

        } else {

            // =================================================
            // SIMPAN C1 VS C2
            // =================================================

            $simpan1 = mysqli_query(
                $koneksi,
                "
                INSERT INTO preferensi_kriteria
                (
                    kriteria_1,
                    kriteria_2,
                    nilai
                )
                VALUES
                (
                    $c1,
                    $c2,
                    $nilai_c1_c2_db
                )
                "
            );


            // =================================================
            // SIMPAN C1 VS C3
            // =================================================

            $simpan2 = mysqli_query(
                $koneksi,
                "
                INSERT INTO preferensi_kriteria
                (
                    kriteria_1,
                    kriteria_2,
                    nilai
                )
                VALUES
                (
                    $c1,
                    $c3,
                    $nilai_c1_c3_db
                )
                "
            );


            // =================================================
            // SIMPAN C2 VS C3
            // =================================================

            $simpan3 = mysqli_query(
                $koneksi,
                "
                INSERT INTO preferensi_kriteria
                (
                    kriteria_1,
                    kriteria_2,
                    nilai
                )
                VALUES
                (
                    $c2,
                    $c3,
                    $nilai_c2_c3_db
                )
                "
            );


            if (!$simpan1 || !$simpan2 || !$simpan3) {

                $error =
                    "Gagal menyimpan preferensi: "
                    . mysqli_error($koneksi);

            } else {

                $pesan =
                    "Preferensi berhasil disimpan.";

            }
        }
    }
}

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Input Preferensi Kriteria</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f6f8;
        }

        .container {
            max-width: 1100px;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .pair-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .pair-header {
            background: #198754;
            color: white;
            padding: 18px 20px;
        }

        .pair-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .pair-body {
            padding: 25px;
            background: white;
        }

        .scale-container {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: 8px;
            margin-top: 20px;
        }

        .scale-option {
            position: relative;
        }

        .scale-option input {
            position: absolute;
            opacity: 0;
        }

        .scale-option label {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 80px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            background: white;
            transition: 0.2s;
            text-align: center;
            padding: 8px 4px;
        }

        .scale-option label:hover {
            border-color: #198754;
            background: #f0fff7;
        }

        .scale-option input:checked + label {
            background: #198754;
            border-color: #198754;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .scale-number {
            font-size: 22px;
            font-weight: bold;
        }

        .scale-desc {
            font-size: 11px;
            margin-top: 5px;
            line-height: 1.2;
        }

        .direction-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .direction-left {
            color: #0d6efd;
            font-weight: 600;
        }

        .direction-right {
            color: #dc3545;
            font-weight: 600;
            text-align: right;
        }

        .same {
            text-align: center;
            color: #198754;
            font-weight: 600;
        }

        .info-scale {
            border-left: 5px solid #0dcaf0;
        }

        @media (max-width: 900px) {

            .scale-container {
                grid-template-columns: repeat(3, 1fr);
            }

        }

        @media (max-width: 500px) {

            .scale-container {
                grid-template-columns: repeat(2, 1fr);
            }

        }

    </style>

</head>


<body>


<div class="container py-4">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">
                Input Preferensi Kriteria
            </h3>

            <p class="text-muted mb-0">

                Tentukan tingkat kepentingan antar kriteria
                menggunakan skala perbandingan AHP.

            </p>

        </div>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>


    <!-- PESAN -->

    <?php if ($pesan != ''): ?>

        <div class="alert alert-success">

            <strong>Berhasil!</strong><br>

            <?= htmlspecialchars($pesan); ?>

        </div>

    <?php endif; ?>


    <?php if ($error != ''): ?>

        <div class="alert alert-danger">

            <strong>Perhatian!</strong><br>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- KRITERIA -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Kriteria yang Digunakan
            </h5>

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <strong>C1</strong><br>

                        <?= htmlspecialchars($nama_c1); ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <strong>C2</strong><br>

                        <?= htmlspecialchars($nama_c2); ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <strong>C3</strong><br>

                        <?= htmlspecialchars($nama_c3); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- PENJELASAN SKALA -->

    <div class="card shadow-sm mb-4 info-scale">

        <div class="card-body">

            <h5 class="fw-bold">
                Cara Memilih Nilai AHP
            </h5>

            <p class="mb-2">

                Bandingkan kriteria yang berada di sebelah kiri
                dengan kriteria yang berada di sebelah kanan.

            </p>

            <ul class="mb-0">

                <li>
                    <strong>1</strong> = kedua kriteria sama penting
                </li>

                <li>
                    <strong>3, 5, 7, 9</strong> =
                    kriteria sebelah kiri lebih penting
                </li>

                <li>
                    <strong>1/3, 1/5, 1/7, 1/9</strong> =
                    kriteria sebelah kanan lebih penting
                </li>

            </ul>

        </div>

    </div>


    <form method="POST">


        <!-- ================================================= -->
        <!-- C1 VS C2 -->
        <!-- ================================================= -->

        <div class="pair-card shadow-sm">

            <div class="pair-header">

                <h5>
                    Perbandingan 1:
                    C1 <?= htmlspecialchars($nama_c1); ?>
                    vs
                    C2 <?= htmlspecialchars($nama_c2); ?>
                </h5>

            </div>


            <div class="pair-body">

                <div class="direction-box">

                    <div class="direction-left">

                        ← <?= htmlspecialchars($nama_c1); ?>

                    </div>

                    <div class="same">

                        1 = Sama Penting

                    </div>

                    <div class="direction-right">

                        <?= htmlspecialchars($nama_c2); ?> →

                    </div>

                </div>


                <p class="fw-bold mt-4 mb-2">

                    Pilih nilai perbandingan:

                </p>


                <div class="scale-container">

                    <?php
                    $options = [
                        '0.1111' => '1/9',
                        '0.1429' => '1/7',
                        '0.2'    => '1/5',
                        '0.3333' => '1/3',
                        '1'      => '1',
                        '3'      => '3',
                        '5'      => '5',
                        '7'      => '7',
                        '9'      => '9'
                    ];

                    foreach ($options as $value => $display):
                    ?>

                        <div class="scale-option">

                            <input
                                type="radio"
                                id="c1c2_<?= str_replace('.', '_', $value); ?>"
                                name="nilai_c1_c2"
                                value="<?= $value; ?>"
                                required
                                <?= (isset($_POST['nilai_c1_c2']) &&
                                    $_POST['nilai_c1_c2'] == $value)
                                    ? 'checked'
                                    : ''; ?>
                            >

                            <label
                                for="c1c2_<?= str_replace('.', '_', $value); ?>"
                            >

                                <span class="scale-number">
                                    <?= $display; ?>
                                </span>

                                <span class="scale-desc">

                                    <?php
                                    if ($value == '1') {
                                        echo 'Sama';
                                    } elseif ((float)$value < 1) {
                                        echo 'C2 lebih penting';
                                    } else {
                                        echo 'C1 lebih penting';
                                    }
                                    ?>

                                </span>

                            </label>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- C1 VS C3 -->
        <!-- ================================================= -->

        <div class="pair-card shadow-sm">

            <div class="pair-header">

                <h5>
                    Perbandingan 2:
                    C1 <?= htmlspecialchars($nama_c1); ?>
                    vs
                    C3 <?= htmlspecialchars($nama_c3); ?>
                </h5>

            </div>


            <div class="pair-body">

                <div class="direction-box">

                    <div class="direction-left">

                        ← <?= htmlspecialchars($nama_c1); ?>

                    </div>

                    <div class="same">

                        1 = Sama Penting

                    </div>

                    <div class="direction-right">

                        <?= htmlspecialchars($nama_c3); ?> →

                    </div>

                </div>


                <p class="fw-bold mt-4 mb-2">

                    Pilih nilai perbandingan:

                </p>


                <div class="scale-container">

                    <?php foreach ($options as $value => $display): ?>

                        <div class="scale-option">

                            <input
                                type="radio"
                                id="c1c3_<?= str_replace('.', '_', $value); ?>"
                                name="nilai_c1_c3"
                                value="<?= $value; ?>"
                                required
                                <?= (isset($_POST['nilai_c1_c3']) &&
                                    $_POST['nilai_c1_c3'] == $value)
                                    ? 'checked'
                                    : ''; ?>
                            >

                            <label
                                for="c1c3_<?= str_replace('.', '_', $value); ?>"
                            >

                                <span class="scale-number">
                                    <?= $display; ?>
                                </span>

                                <span class="scale-desc">

                                    <?php
                                    if ($value == '1') {
                                        echo 'Sama';
                                    } elseif ((float)$value < 1) {
                                        echo 'C3 lebih penting';
                                    } else {
                                        echo 'C1 lebih penting';
                                    }
                                    ?>

                                </span>

                            </label>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- C2 VS C3 -->
        <!-- ================================================= -->

        <div class="pair-card shadow-sm">

            <div class="pair-header">

                <h5>
                    Perbandingan 3:
                    C2 <?= htmlspecialchars($nama_c2); ?>
                    vs
                    C3 <?= htmlspecialchars($nama_c3); ?>
                </h5>

            </div>


            <div class="pair-body">

                <div class="direction-box">

                    <div class="direction-left">

                        ← <?= htmlspecialchars($nama_c2); ?>

                    </div>

                    <div class="same">

                        1 = Sama Penting

                    </div>

                    <div class="direction-right">

                        <?= htmlspecialchars($nama_c3); ?> →

                    </div>

                </div>


                <p class="fw-bold mt-4 mb-2">

                    Pilih nilai perbandingan:

                </p>


                <div class="scale-container">

                    <?php foreach ($options as $value => $display): ?>

                        <div class="scale-option">

                            <input
                                type="radio"
                                id="c2c3_<?= str_replace('.', '_', $value); ?>"
                                name="nilai_c2_c3"
                                value="<?= $value; ?>"
                                required
                                <?= (isset($_POST['nilai_c2_c3']) &&
                                    $_POST['nilai_c2_c3'] == $value)
                                    ? 'checked'
                                    : ''; ?>
                            >

                            <label
                                for="c2c3_<?= str_replace('.', '_', $value); ?>"
                            >

                                <span class="scale-number">
                                    <?= $display; ?>
                                </span>

                                <span class="scale-desc">

                                    <?php
                                    if ($value == '1') {
                                        echo 'Sama';
                                    } elseif ((float)$value < 1) {
                                        echo 'C3 lebih penting';
                                    } else {
                                        echo 'C2 lebih penting';
                                    }
                                    ?>

                                </span>

                            </label>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- CATATAN -->

        <div class="alert alert-warning shadow-sm">

            <strong>Contoh:</strong>

            <br>

            Jika pada C1 vs C2 Anda memilih <strong>5</strong>,
            berarti <strong><?= htmlspecialchars($nama_c1); ?></strong>
            lebih penting daripada
            <strong><?= htmlspecialchars($nama_c2); ?></strong>
            dengan tingkat kepentingan 5.

            <br><br>

            Jika memilih <strong>1/5</strong>,
            berarti <strong><?= htmlspecialchars($nama_c2); ?></strong>
            lebih penting daripada
            <strong><?= htmlspecialchars($nama_c1); ?></strong>
            dengan tingkat kepentingan 5.

        </div>


        <!-- BUTTON -->

        <div class="d-flex gap-2 mb-5">

            <button
                type="submit"
                name="simpan"
                class="btn btn-success btn-lg"
            >
                Simpan Preferensi
            </button>


            <a
                href="admin_dashboard.php"
                class="btn btn-secondary btn-lg"
            >
                Kembali
            </a>

        </div>


    </form>


</div>


</body>

</html>