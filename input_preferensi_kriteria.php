<?php
session_start();
include 'koneksi.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA KRITERIA
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| HARUS 3 KRITERIA
|--------------------------------------------------------------------------
*/
if (count($kriteria) != 3) {
    die("
        <div style='font-family:Arial; max-width:700px; margin:80px auto; padding:30px'>
            <h3>Kriteria Belum Sesuai</h3>
            <p>Sistem membutuhkan tepat 3 kriteria.</p>
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

/*
|--------------------------------------------------------------------------
| DATA KRITERIA
|--------------------------------------------------------------------------
*/
$c1 = (int)$kriteria[0]['id_kriteria'];
$c2 = (int)$kriteria[1]['id_kriteria'];
$c3 = (int)$kriteria[2]['id_kriteria'];

$kode_c1 = $kriteria[0]['kode_kriteria'];
$kode_c2 = $kriteria[1]['kode_kriteria'];
$kode_c3 = $kriteria[2]['kode_kriteria'];

$nama_c1 = $kriteria[0]['nama_kriteria'];
$nama_c2 = $kriteria[1]['nama_kriteria'];
$nama_c3 = $kriteria[2]['nama_kriteria'];

/*
|--------------------------------------------------------------------------
| NILAI DEFAULT
|--------------------------------------------------------------------------
*/
$nilai_c1_c2 = '';
$nilai_c1_c3 = '';
$nilai_c2_c3 = '';

$pesan = '';
$error = '';

$hasil_cr = null;

/*
|--------------------------------------------------------------------------
| FUNGSI KONVERSI NILAI SAATY
|--------------------------------------------------------------------------
| Kita menggunakan STRING agar nilai 1/3, 1/5, 1/7, 1/9 aman.
|--------------------------------------------------------------------------
*/
function nilaiSaaty($nilai)
{
    switch ((string)$nilai) {
        case '9':
            return 9;

        case '7':
            return 7;

        case '5':
            return 5;

        case '3':
            return 3;

        case '1':
            return 1;

        case '1/3':
            return 1 / 3;

        case '1/5':
            return 1 / 5;

        case '1/7':
            return 1 / 7;

        case '1/9':
            return 1 / 9;

        default:
            return null;
    }
}

/*
|--------------------------------------------------------------------------
| FUNGSI FORMAT NILAI
|--------------------------------------------------------------------------
*/
function tampilNilai($nilai)
{
    switch ((string)$nilai) {
        case '1/3':
            return '1/3';

        case '1/5':
            return '1/5';

        case '1/7':
            return '1/7';

        case '1/9':
            return '1/9';

        default:
            return $nilai;
    }
}

/*
|--------------------------------------------------------------------------
| PROSES HITUNG KONSISTENSI
|--------------------------------------------------------------------------
*/
if (isset($_POST['hitung'])) {

    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA DARI FORM
    |--------------------------------------------------------------------------
    */
    $nilai_c1_c2 = isset($_POST['nilai_c1_c2'])
        ? trim($_POST['nilai_c1_c2'])
        : '';

    $nilai_c1_c3 = isset($_POST['nilai_c1_c3'])
        ? trim($_POST['nilai_c1_c3'])
        : '';

    $nilai_c2_c3 = isset($_POST['nilai_c2_c3'])
        ? trim($_POST['nilai_c2_c3'])
        : '';

    /*
    |--------------------------------------------------------------------------
    | CEK KOSONG
    |--------------------------------------------------------------------------
    */
    if (
        $nilai_c1_c2 === '' ||
        $nilai_c1_c3 === '' ||
        $nilai_c2_c3 === ''
    ) {

        $error = "Silakan pilih nilai AHP pada ketiga perbandingan.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | KONVERSI KE ANGKA
        |--------------------------------------------------------------------------
        */
        $a12 = nilaiSaaty($nilai_c1_c2);
        $a13 = nilaiSaaty($nilai_c1_c3);
        $a23 = nilaiSaaty($nilai_c2_c3);

        /*
        |--------------------------------------------------------------------------
        | VALIDASI NILAI
        |--------------------------------------------------------------------------
        */
        if (
            $a12 === null ||
            $a13 === null ||
            $a23 === null
        ) {

            $error = "Nilai AHP tidak valid. Silakan pilih kembali.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | BENTUK MATRIKS AHP
            |--------------------------------------------------------------------------
            */
            $matrix = [
                [
                    1,
                    $a12,
                    $a13
                ],
                [
                    1 / $a12,
                    1,
                    $a23
                ],
                [
                    1 / $a13,
                    1 / $a23,
                    1
                ]
            ];

            /*
            |--------------------------------------------------------------------------
            | JUMLAH KOLOM
            |--------------------------------------------------------------------------
            */
            $jumlah_kolom = [];

            for ($j = 0; $j < 3; $j++) {

                $jumlah_kolom[$j] = 0;

                for ($i = 0; $i < 3; $i++) {

                    $jumlah_kolom[$j] += $matrix[$i][$j];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NORMALISASI
            |--------------------------------------------------------------------------
            */
            $normalisasi = [];

            for ($i = 0; $i < 3; $i++) {

                for ($j = 0; $j < 3; $j++) {

                    $normalisasi[$i][$j] =
                        $matrix[$i][$j] / $jumlah_kolom[$j];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | BOBOT PRIORITAS
            |--------------------------------------------------------------------------
            */
            $bobot = [];

            for ($i = 0; $i < 3; $i++) {

                $bobot[$i] =
                    (
                        $normalisasi[$i][0] +
                        $normalisasi[$i][1] +
                        $normalisasi[$i][2]
                    ) / 3;
            }

            /*
            |--------------------------------------------------------------------------
            | WEIGHTED SUM
            |--------------------------------------------------------------------------
            */
            $weighted_sum = [];

            for ($i = 0; $i < 3; $i++) {

                $weighted_sum[$i] =
                    ($matrix[$i][0] * $bobot[0]) +
                    ($matrix[$i][1] * $bobot[1]) +
                    ($matrix[$i][2] * $bobot[2]);
            }

            /*
            |--------------------------------------------------------------------------
            | LAMBDA
            |--------------------------------------------------------------------------
            */
            $lambda = [];

            for ($i = 0; $i < 3; $i++) {

                if ($bobot[$i] != 0) {

                    $lambda[$i] =
                        $weighted_sum[$i] / $bobot[$i];

                } else {

                    $lambda[$i] = 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | LAMBDA MAX
            |--------------------------------------------------------------------------
            */
            $lambda_max =
                (
                    $lambda[0] +
                    $lambda[1] +
                    $lambda[2]
                ) / 3;

            /*
            |--------------------------------------------------------------------------
            | CI
            |--------------------------------------------------------------------------
            */
            $n = 3;

            $CI = ($lambda_max - $n) / ($n - 1);

            /*
            |--------------------------------------------------------------------------
            | RI
            |--------------------------------------------------------------------------
            */
            $RI = 0.58;

            /*
            |--------------------------------------------------------------------------
            | CR
            |--------------------------------------------------------------------------
            */
            $CR = $CI / $RI;

            /*
            |--------------------------------------------------------------------------
            | SIMPAN HASIL
            |--------------------------------------------------------------------------
            */
            $hasil_cr = [
                'matrix' => $matrix,
                'jumlah_kolom' => $jumlah_kolom,
                'normalisasi' => $normalisasi,
                'bobot' => $bobot,
                'weighted_sum' => $weighted_sum,
                'lambda' => $lambda,
                'lambda_max' => $lambda_max,
                'CI' => $CI,
                'RI' => $RI,
                'CR' => $CR
            ];
        }
    }
}

/*
|--------------------------------------------------------------------------
| SIMPAN BOBOT JIKA KONSISTEN
|--------------------------------------------------------------------------
*/
if (isset($_POST['simpan'])) {

    $nilai_c1_c2 = isset($_POST['nilai_c1_c2'])
        ? trim($_POST['nilai_c1_c2'])
        : '';

    $nilai_c1_c3 = isset($_POST['nilai_c1_c3'])
        ? trim($_POST['nilai_c1_c3'])
        : '';

    $nilai_c2_c3 = isset($_POST['nilai_c2_c3'])
        ? trim($_POST['nilai_c2_c3'])
        : '';

    $a12 = nilaiSaaty($nilai_c1_c2);
    $a13 = nilaiSaaty($nilai_c1_c3);
    $a23 = nilaiSaaty($nilai_c2_c3);

    if (
        $a12 === null ||
        $a13 === null ||
        $a23 === null
    ) {

        $error = "Data perbandingan tidak valid.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | BENTUK ULANG MATRIKS
        |--------------------------------------------------------------------------
        */
        $matrix = [
            [1, $a12, $a13],
            [1 / $a12, 1, $a23],
            [1 / $a13, 1 / $a23, 1]
        ];

        /*
        |--------------------------------------------------------------------------
        | JUMLAH KOLOM
        |--------------------------------------------------------------------------
        */
        $jumlah_kolom = [];

        for ($j = 0; $j < 3; $j++) {

            $jumlah_kolom[$j] = 0;

            for ($i = 0; $i < 3; $i++) {

                $jumlah_kolom[$j] += $matrix[$i][$j];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI
        |--------------------------------------------------------------------------
        */
        $normalisasi = [];

        for ($i = 0; $i < 3; $i++) {

            for ($j = 0; $j < 3; $j++) {

                $normalisasi[$i][$j] =
                    $matrix[$i][$j] / $jumlah_kolom[$j];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | BOBOT
        |--------------------------------------------------------------------------
        */
        $bobot = [];

        for ($i = 0; $i < 3; $i++) {

            $bobot[$i] =
                (
                    $normalisasi[$i][0] +
                    $normalisasi[$i][1] +
                    $normalisasi[$i][2]
                ) / 3;
        }

        /*
        |--------------------------------------------------------------------------
        | WEIGHTED SUM
        |--------------------------------------------------------------------------
        */
        $weighted_sum = [];

        for ($i = 0; $i < 3; $i++) {

            $weighted_sum[$i] =
                ($matrix[$i][0] * $bobot[0]) +
                ($matrix[$i][1] * $bobot[1]) +
                ($matrix[$i][2] * $bobot[2]);
        }

        /*
        |--------------------------------------------------------------------------
        | LAMBDA MAX
        |--------------------------------------------------------------------------
        */
        $lambda_max =
            (
                ($weighted_sum[0] / $bobot[0]) +
                ($weighted_sum[1] / $bobot[1]) +
                ($weighted_sum[2] / $bobot[2])
            ) / 3;

        /*
        |--------------------------------------------------------------------------
        | CI DAN CR
        |--------------------------------------------------------------------------
        */
        $CI = ($lambda_max - 3) / 2;

        $RI = 0.58;

        $CR = $CI / $RI;

        /*
        |--------------------------------------------------------------------------
        | JIKA TIDAK KONSISTEN
        |--------------------------------------------------------------------------
        */
        if ($CR > 0.10) {

            $error =
                "Perbandingan tidak konsisten. Nilai CR = "
                . number_format($CR, 4)
                . ". Silakan ubah pilihan perbandingan.";

            /*
            | Tampilkan hasil juga
            */
            $hasil_cr = [
                'lambda_max' => $lambda_max,
                'CI' => $CI,
                'RI' => $RI,
                'CR' => $CR,
                'bobot' => $bobot
            ];

        } else {

            /*
            |--------------------------------------------------------------------------
            | HAPUS BOBOT LAMA
            |--------------------------------------------------------------------------
            */
            $hapus = mysqli_query(
                $koneksi,
                "DELETE FROM bobot_kriteria"
            );

            if (!$hapus) {

                $error =
                    "Gagal menghapus bobot lama: "
                    . mysqli_error($koneksi);

            } else {

                /*
                |--------------------------------------------------------------------------
                | SIMPAN BOBOT K1
                |--------------------------------------------------------------------------
                */
                $stmt1 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO bobot_kriteria
                    (id_kriteria, bobot)
                    VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt1,
                    "id",
                    $c1,
                    $bobot[0]
                );

                $simpan1 = mysqli_stmt_execute($stmt1);

                mysqli_stmt_close($stmt1);

                /*
                |--------------------------------------------------------------------------
                | SIMPAN BOBOT K2
                |--------------------------------------------------------------------------
                */
                $stmt2 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO bobot_kriteria
                    (id_kriteria, bobot)
                    VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt2,
                    "id",
                    $c2,
                    $bobot[1]
                );

                $simpan2 = mysqli_stmt_execute($stmt2);

                mysqli_stmt_close($stmt2);

                /*
                |--------------------------------------------------------------------------
                | SIMPAN BOBOT K3
                |--------------------------------------------------------------------------
                */
                $stmt3 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO bobot_kriteria
                    (id_kriteria, bobot)
                    VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt3,
                    "id",
                    $c3,
                    $bobot[2]
                );

                $simpan3 = mysqli_stmt_execute($stmt3);

                mysqli_stmt_close($stmt3);

                /*
                |--------------------------------------------------------------------------
                | SIMPAN PREFERENSI
                |--------------------------------------------------------------------------
                */
                mysqli_query(
                    $koneksi,
                    "DELETE FROM preferensi_kriteria"
                );

                /*
                | C1 VS C2
                */
                $stmt4 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO preferensi_kriteria
                    (kriteria_1, kriteria_2, nilai)
                    VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt4,
                    "iid",
                    $c1,
                    $c2,
                    $a12
                );

                $pref1 = mysqli_stmt_execute($stmt4);

                mysqli_stmt_close($stmt4);

                /*
                | C1 VS C3
                */
                $stmt5 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO preferensi_kriteria
                    (kriteria_1, kriteria_2, nilai)
                    VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt5,
                    "iid",
                    $c1,
                    $c3,
                    $a13
                );

                $pref2 = mysqli_stmt_execute($stmt5);

                mysqli_stmt_close($stmt5);

                /*
                | C2 VS C3
                */
                $stmt6 = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO preferensi_kriteria
                    (kriteria_1, kriteria_2, nilai)
                    VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt6,
                    "iid",
                    $c2,
                    $c3,
                    $a23
                );

                $pref3 = mysqli_stmt_execute($stmt6);

                mysqli_stmt_close($stmt6);

                /*
                |--------------------------------------------------------------------------
                | CEK HASIL SIMPAN
                |--------------------------------------------------------------------------
                */
                if (
                    $simpan1 &&
                    $simpan2 &&
                    $simpan3 &&
                    $pref1 &&
                    $pref2 &&
                    $pref3
                ) {

                    $pesan =
                        "Bobot AHP dan preferensi berhasil disimpan.";

                    $hasil_cr = [
                        'lambda_max' => $lambda_max,
                        'CI' => $CI,
                        'RI' => $RI,
                        'CR' => $CR,
                        'bobot' => $bobot
                    ];

                } else {

                    $error =
                        "Terjadi kesalahan saat menyimpan data: "
                        . mysqli_error($koneksi);
                }
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

    <title>Input Preferensi Kriteria - AHP</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>

<?php include __DIR__ . "/includes/admin_header.php"; ?>

<div class="container py-4">

    <!-- =======================================================
         HEADER
    ======================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">
                Input Preferensi Kriteria
            </h3>

            <p class="text-muted mb-0">
                Tentukan tingkat kepentingan antar kriteria menggunakan metode AHP.
            </p>

        </div>

        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>


    <!-- =======================================================
         PESAN ERROR
    ======================================================== -->

    <?php if ($error != ''): ?>

        <div class="alert alert-danger">

            <strong>Perhatian!</strong>

            <br>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =======================================================
         PESAN SUKSES
    ======================================================== -->

    <?php if ($pesan != ''): ?>

        <div class="alert alert-success">

            <strong>Berhasil!</strong>

            <br>

            <?= htmlspecialchars($pesan); ?>

        </div>

    <?php endif; ?>


    <!-- =======================================================
         DAFTAR KRITERIA
    ======================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Kriteria AHP
            </h5>

        </div>

        <div class="card-body">

            <ol class="mb-0">

                <li>
                    <strong><?= htmlspecialchars($kode_c1); ?></strong>
                    -
                    <?= htmlspecialchars($nama_c1); ?>
                </li>

                <li>
                    <strong><?= htmlspecialchars($kode_c2); ?></strong>
                    -
                    <?= htmlspecialchars($nama_c2); ?>
                </li>

                <li>
                    <strong><?= htmlspecialchars($kode_c3); ?></strong>
                    -
                    <?= htmlspecialchars($nama_c3); ?>
                </li>

            </ol>

        </div>

    </div>


    <!-- =======================================================
         PETUNJUK
    ======================================================== -->

    <div class="alert alert-info">

        <h5>
            📌 Cara Mengisi
        </h5>

        <p class="mb-2">
            Bandingkan dua kriteria dan tentukan kriteria mana
            yang lebih penting.
        </p>

        <ul class="mb-0">

            <li>
                Jika kriteria kiri lebih penting:
                <strong>3, 5, 7, atau 9</strong>
            </li>

            <li>
                Jika kriteria kanan lebih penting:
                <strong>1/3, 1/5, 1/7, atau 1/9</strong>
            </li>

            <li>
                Jika sama penting:
                <strong>1</strong>
            </li>

        </ul>

    </div>


    <!-- =======================================================
         FORM INPUT
    ======================================================== -->

    <form method="POST" action="input_preferensi_kriteria.php">

        <!-- ===================================================
             PERBANDINGAN 1
        ==================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    Perbandingan 1
                </h5>

            </div>

            <div class="card-body">

                <p class="fw-bold">

                    <span class="badge bg-primary">
                        <?= htmlspecialchars($kode_c1); ?>
                    </span>

                    <?= htmlspecialchars($nama_c1); ?>

                    <strong> VS </strong>

                    <span class="badge bg-secondary">
                        <?= htmlspecialchars($kode_c2); ?>
                    </span>

                    <?= htmlspecialchars($nama_c2); ?>

                </p>

                <label class="form-label fw-bold">
                    Pilih tingkat kepentingan:
                </label>

                <select
                    name="nilai_c1_c2"
                    class="form-select form-select-lg"
                    required
                >

                    <option value="">
                        -- Pilih Nilai AHP --
                    </option>

                    <option value="9"
                        <?= ($nilai_c1_c2 === '9') ? 'selected' : ''; ?>>
                        9 — <?= htmlspecialchars($kode_c1); ?> mutlak lebih penting
                    </option>

                    <option value="7"
                        <?= ($nilai_c1_c2 === '7') ? 'selected' : ''; ?>>
                        7 — <?= htmlspecialchars($kode_c1); ?> sangat lebih penting
                    </option>

                    <option value="5"
                        <?= ($nilai_c1_c2 === '5') ? 'selected' : ''; ?>>
                        5 — <?= htmlspecialchars($kode_c1); ?> lebih penting
                    </option>

                    <option value="3"
                        <?= ($nilai_c1_c2 === '3') ? 'selected' : ''; ?>>
                        3 — <?= htmlspecialchars($kode_c1); ?> sedikit lebih penting
                    </option>

                    <option value="1"
                        <?= ($nilai_c1_c2 === '1') ? 'selected' : ''; ?>>
                        1 — Kedua kriteria sama penting
                    </option>

                    <option value="1/3"
                        <?= ($nilai_c1_c2 === '1/3') ? 'selected' : ''; ?>>
                        1/3 — <?= htmlspecialchars($kode_c2); ?> sedikit lebih penting
                    </option>

                    <option value="1/5"
                        <?= ($nilai_c1_c2 === '1/5') ? 'selected' : ''; ?>>
                        1/5 — <?= htmlspecialchars($kode_c2); ?> lebih penting
                    </option>

                    <option value="1/7"
                        <?= ($nilai_c1_c2 === '1/7') ? 'selected' : ''; ?>>
                        1/7 — <?= htmlspecialchars($kode_c2); ?> sangat lebih penting
                    </option>

                    <option value="1/9"
                        <?= ($nilai_c1_c2 === '1/9') ? 'selected' : ''; ?>>
                        1/9 — <?= htmlspecialchars($kode_c2); ?> mutlak lebih penting
                    </option>

                </select>

            </div>

        </div>


        <!-- ===================================================
             PERBANDINGAN 2
        ==================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    Perbandingan 2
                </h5>

            </div>

            <div class="card-body">

                <p class="fw-bold">

                    <span class="badge bg-primary">
                        <?= htmlspecialchars($kode_c1); ?>
                    </span>

                    <?= htmlspecialchars($nama_c1); ?>

                    <strong> VS </strong>

                    <span class="badge bg-secondary">
                        <?= htmlspecialchars($kode_c3); ?>
                    </span>

                    <?= htmlspecialchars($nama_c3); ?>

                </p>

                <label class="form-label fw-bold">
                    Pilih tingkat kepentingan:
                </label>

                <select
                    name="nilai_c1_c3"
                    class="form-select form-select-lg"
                    required
                >

                    <option value="">
                        -- Pilih Nilai AHP --
                    </option>

                    <option value="9"
                        <?= ($nilai_c1_c3 === '9') ? 'selected' : ''; ?>>
                        9 — <?= htmlspecialchars($kode_c1); ?> mutlak lebih penting
                    </option>

                    <option value="7"
                        <?= ($nilai_c1_c3 === '7') ? 'selected' : ''; ?>>
                        7 — <?= htmlspecialchars($kode_c1_c1); ?> sangat lebih penting
                    </option>

                    <option value="5"
                        <?= ($nilai_c1_c3 === '5') ? 'selected' : ''; ?>>
                        5 — <?= htmlspecialchars($kode_c1); ?> lebih penting
                    </option>

                    <option value="3"
                        <?= ($nilai_c1_c3 === '3') ? 'selected' : ''; ?>>
                        3 — <?= htmlspecialchars($kode_c1); ?> sedikit lebih penting
                    </option>

                    <option value="1"
                        <?= ($nilai_c1_c3 === '1') ? 'selected' : ''; ?>>
                        1 — Kedua kriteria sama penting
                    </option>

                    <option value="1/3"
                        <?= ($nilai_c1_c3 === '1/3') ? 'selected' : ''; ?>>
                        1/3 — <?= htmlspecialchars($kode_c3); ?> sedikit lebih penting
                    </option>

                    <option value="1/5"
                        <?= ($nilai_c1_c3 === '1/5') ? 'selected' : ''; ?>>
                        1/5 — <?= htmlspecialchars($kode_c3); ?> lebih penting
                    </option>

                    <option value="1/7"
                        <?= ($nilai_c1_c3 === '1/7') ? 'selected' : ''; ?>>
                        1/7 — <?= htmlspecialchars($kode_c3); ?> sangat lebih penting
                    </option>

                    <option value="1/9"
                        <?= ($nilai_c1_c3 === '1/9') ? 'selected' : ''; ?>>
                        1/9 — <?= htmlspecialchars($kode_c3); ?> mutlak lebih penting
                    </option>

                </select>

            </div>

        </div>


        <!-- ===================================================
             PERBANDINGAN 3
        ==================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    Perbandingan 3
                </h5>

            </div>

            <div class="card-body">

                <p class="fw-bold">

                    <span class="badge bg-primary">
                        <?= htmlspecialchars($kode_c2); ?>
                    </span>

                    <?= htmlspecialchars($nama_c2); ?>

                    <strong> VS </strong>

                    <span class="badge bg-secondary">
                        <?= htmlspecialchars($kode_c3); ?>
                    </span>

                    <?= htmlspecialchars($nama_c3); ?>

                </p>

                <label class="form-label fw-bold">
                    Pilih tingkat kepentingan:
                </label>

                <select
                    name="nilai_c2_c3"
                    class="form-select form-select-lg"
                    required
                >

                    <option value="">
                        -- Pilih Nilai AHP --
                    </option>

                    <option value="9"
                        <?= ($nilai_c2_c3 === '9') ? 'selected' : ''; ?>>
                        9 — <?= htmlspecialchars($kode_c2); ?> mutlak lebih penting
                    </option>

                    <option value="7"
                        <?= ($nilai_c2_c3 === '7') ? 'selected' : ''; ?>>
                        7 — <?= htmlspecialchars($kode_c2); ?> sangat lebih penting
                    </option>

                    <option value="5"
                        <?= ($nilai_c2_c3 === '5') ? 'selected' : ''; ?>>
                        5 — <?= htmlspecialchars($kode_c2); ?> lebih penting
                    </option>

                    <option value="3"
                        <?= ($nilai_c2_c3 === '3') ? 'selected' : ''; ?>>
                        3 — <?= htmlspecialchars($kode_c2); ?> sedikit lebih penting
                    </option>

                    <option value="1"
                        <?= ($nilai_c2_c3 === '1') ? 'selected' : ''; ?>>
                        1 — Kedua kriteria sama penting
                    </option>

                    <option value="1/3"
                        <?= ($nilai_c2_c3 === '1/3') ? 'selected' : ''; ?>>
                        1/3 — <?= htmlspecialchars($kode_c3); ?> sedikit lebih penting
                    </option>

                    <option value="1/5"
                        <?= ($nilai_c2_c3 === '1/5') ? 'selected' : ''; ?>>
                        1/5 — <?= htmlspecialchars($kode_c3); ?> lebih penting
                    </option>

                    <option value="1/7"
                        <?= ($nilai_c2_c3 === '1/7') ? 'selected' : ''; ?>>
                        1/7 — <?= htmlspecialchars($kode_c3); ?> sangat lebih penting
                    </option>

                    <option value="1/9"
                        <?= ($nilai_c2_c3 === '1/9') ? 'selected' : ''; ?>>
                        1/9 — <?= htmlspecialchars($kode_c3); ?> mutlak lebih penting
                    </option>

                </select>

            </div>

        </div>


        <!-- ===================================================
             TOMBOL
        ==================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <button
                    type="submit"
                    name="hitung"
                    value="1"
                    class="btn btn-primary btn-lg"
                >
                    🔎 Hitung Konsistensi
                </button>

                <a
                    href="admin_dashboard.php"
                    class="btn btn-secondary btn-lg"
                >
                    Kembali
                </a>

            </div>

        </div>

    </form>


    <!-- =======================================================
         HASIL AHP
    ======================================================== -->

    <?php if ($hasil_cr !== null): ?>

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-dark text-white">

                <h5 class="mb-0">
                    Hasil Perhitungan Konsistensi AHP
                </h5>

            </div>

            <div class="card-body">

                <div class="row">

                    <!-- LAMBDA MAX -->

                    <div class="col-md-3">

                        <div class="card text-center mb-3">

                            <div class="card-body">

                                <h6>
                                    λ Max
                                </h6>

                                <h4>
                                    <?= number_format(
                                        $hasil_cr['lambda_max'],
                                        4
                                    ); ?>
                                </h4>

                            </div>

                        </div>

                    </div>


                    <!-- CI -->

                    <div class="col-md-3">

                        <div class="card text-center mb-3">

                            <div class="card-body">

                                <h6>
                                    CI
                                </h6>

                                <h4>
                                    <?= number_format(
                                        $hasil_cr['CI'],
                                        4
                                    ); ?>
                                </h4>

                            </div>

                        </div>

                    </div>


                    <!-- RI -->

                    <div class="col-md-3">

                        <div class="card text-center mb-3">

                            <div class="card-body">

                                <h6>
                                    RI
                                </h6>

                                <h4>
                                    <?= number_format(
                                        $hasil_cr['RI'],
                                        2
                                    ); ?>
                                </h4>

                            </div>

                        </div>

                    </div>


                    <!-- CR -->

                    <div class="col-md-3">

                        <div class="card text-center mb-3">

                            <div class="card-body">

                                <h6>
                                    CR
                                </h6>

                                <h4 class="<?= ($hasil_cr['CR'] <= 0.10)
                                    ? 'text-success'
                                    : 'text-danger'; ?>">

                                    <?= number_format(
                                        $hasil_cr['CR'],
                                        4
                                    ); ?>

                                </h4>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     JIKA KONSISTEN
                ================================================== -->

                <?php if ($hasil_cr['CR'] <= 0.10): ?>

                    <div class="alert alert-success">

                        <h5>
                            ✅ Penilaian Konsisten
                        </h5>

                        Nilai CR =
                        <strong>
                            <?= number_format(
                                $hasil_cr['CR'],
                                4
                            ); ?>
                        </strong>

                        <br>

                        Karena CR ≤ 0,10,
                        maka perbandingan dapat diterima.

                    </div>


                    <!-- BOBOT -->

                    <?php if (isset($hasil_cr['bobot'])): ?>

                        <div class="card mb-4">

                            <div class="card-header bg-info text-white">

                                <strong>
                                    Bobot Prioritas Kriteria
                                </strong>

                            </div>

                            <div class="card-body">

                                <table class="table table-bordered">

                                    <thead>

                                        <tr>

                                            <th>
                                                Kriteria
                                            </th>

                                            <th>
                                                Bobot
                                            </th>

                                            <th>
                                                Persentase
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($nama_c1); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][0],
                                                    4
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][0] * 100,
                                                    2
                                                ); ?>%
                                            </td>

                                        </tr>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($nama_c2); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][1],
                                                    4
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][1] * 100,
                                                    2
                                                ); ?>%
                                            </td>

                                        </tr>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($nama_c3); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][2],
                                                    4
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    $hasil_cr['bobot'][2] * 100,
                                                    2
                                                ); ?>%
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- SIMPAN -->

                    <form method="POST">

                        <input
                            type="hidden"
                            name="nilai_c1_c2"
                            value="<?= htmlspecialchars($nilai_c1_c2); ?>"
                        >

                        <input
                            type="hidden"
                            name="nilai_c1_c3"
                            value="<?= htmlspecialchars($nilai_c1_c3); ?>"
                        >

                        <input
                            type="hidden"
                            name="nilai_c2_c3"
                            value="<?= htmlspecialchars($nilai_c2_c3); ?>"
                        >

                        <button
                            type="submit"
                            name="simpan"
                            value="1"
                            class="btn btn-success btn-lg"
                        >
                            💾 Simpan Bobot & Preferensi
                        </button>

                    </form>

                <?php else: ?>

                    <!-- =================================================
                         JIKA TIDAK KONSISTEN
                    ================================================== -->

                    <div class="alert alert-danger">

                        <h5>
                            ❌ Penilaian Tidak Konsisten
                        </h5>

                        Nilai CR =
                        <strong>
                            <?= number_format(
                                $hasil_cr['CR'],
                                4
                            ); ?>
                        </strong>

                        <br>

                        Karena CR > 0,10,
                        maka perbandingan belum dapat digunakan.

                        <hr>

                        Silakan kembali ke tiga perbandingan
                        di atas dan ubah salah satu atau beberapa
                        nilai AHP.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>