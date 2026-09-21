<?php

session_start();
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

$query_kriteria = "
    SELECT
        id_kriteria,
        kode_kriteria,
        nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if (!$result_kriteria) {
    die("Gagal mengambil kriteria: " . mysqli_error($koneksi));
}

$kriteria = [];

while ($row = mysqli_fetch_assoc($result_kriteria)) {
    $kriteria[] = $row;
}


// ============================================================
// HARUS 3 KRITERIA
// ============================================================

if (count($kriteria) != 3) {
    die("
        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
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

$c1 = (int)$kriteria[0]['id_kriteria'];
$c2 = (int)$kriteria[1]['id_kriteria'];
$c3 = (int)$kriteria[2]['id_kriteria'];


// ============================================================
// NAMA DAN KODE KRITERIA
// ============================================================

$kode_c1 = $kriteria[0]['kode_kriteria'];
$kode_c2 = $kriteria[1]['kode_kriteria'];
$kode_c3 = $kriteria[2]['kode_kriteria'];

$nama_c1 = $kriteria[0]['nama_kriteria'];
$nama_c2 = $kriteria[1]['nama_kriteria'];
$nama_c3 = $kriteria[2]['nama_kriteria'];


// ============================================================
// BENTUK MATRIKS AWAL
// ============================================================

$matriks = [];

foreach ($kriteria as $baris) {

    $id_baris = (int)$baris['id_kriteria'];

    foreach ($kriteria as $kolom) {

        $id_kolom = (int)$kolom['id_kriteria'];

        if ($id_baris == $id_kolom) {
            $matriks[$id_baris][$id_kolom] = 1;
        } else {
            $matriks[$id_baris][$id_kolom] = 0;
        }
    }
}


// ============================================================
// AMBIL PREFERENSI
// ============================================================

$query_preferensi = "
    SELECT
        kriteria_1,
        kriteria_2,
        nilai
    FROM preferensi_kriteria
    ORDER BY id_preferensi ASC
";

$result_preferensi = mysqli_query($koneksi, $query_preferensi);

if (!$result_preferensi) {
    die(
        "Gagal mengambil preferensi: "
        . mysqli_error($koneksi)
    );
}


// ============================================================
// MASUKKAN PREFERENSI KE MATRIKS
// ============================================================

while ($row = mysqli_fetch_assoc($result_preferensi)) {

    $k1 = (int)$row['kriteria_1'];
    $k2 = (int)$row['kriteria_2'];
    $nilai = (float)$row['nilai'];

    if ($nilai <= 0) {
        continue;
    }

    // Nilai utama
    $matriks[$k1][$k2] = $nilai;

    // Nilai kebalikan otomatis
    $matriks[$k2][$k1] = 1 / $nilai;
}


// ============================================================
// CEK PREFERENSI LENGKAP
// ============================================================

$pasangan_lengkap = true;

if (
    $matriks[$c1][$c2] <= 0 ||
    $matriks[$c1][$c3] <= 0 ||
    $matriks[$c2][$c3] <= 0
) {
    $pasangan_lengkap = false;
}


// ============================================================
// VARIABEL
// ============================================================

$jumlah_kolom = [];
$normalisasi = [];
$bobot = [];

$lambda_max = 0;
$CI = 0;
$RI = 0.58;
$CR = 0;

$saran = [];


// ============================================================
// PERHITUNGAN AHP
// ============================================================

if ($pasangan_lengkap) {

    // ========================================================
    // 1. JUMLAH KOLOM
    // ========================================================

    foreach ($kriteria as $kolom) {

        $id_kolom = (int)$kolom['id_kriteria'];

        $jumlah = 0;

        foreach ($kriteria as $baris) {

            $id_baris = (int)$baris['id_kriteria'];

            $jumlah +=
                $matriks[$id_baris][$id_kolom];
        }

        $jumlah_kolom[$id_kolom] = $jumlah;
    }


    // ========================================================
    // 2. NORMALISASI
    // ========================================================

    foreach ($kriteria as $baris) {

        $id_baris = (int)$baris['id_kriteria'];

        foreach ($kriteria as $kolom) {

            $id_kolom = (int)$kolom['id_kriteria'];

            $normalisasi[$id_baris][$id_kolom] =
                $matriks[$id_baris][$id_kolom]
                /
                $jumlah_kolom[$id_kolom];
        }
    }


    // ========================================================
    // 3. BOBOT PRIORITAS
    // ========================================================

    foreach ($kriteria as $baris) {

        $id_baris = (int)$baris['id_kriteria'];

        $total = 0;

        foreach ($kriteria as $kolom) {

            $id_kolom = (int)$kolom['id_kriteria'];

            $total +=
                $normalisasi[$id_baris][$id_kolom];
        }

        $bobot[$id_baris] =
            $total / 3;
    }


    // ========================================================
    // 4. HITUNG LAMBDA MAX
    // ========================================================

    $total_lambda = 0;

    foreach ($kriteria as $baris) {

        $id_baris =
            (int)$baris['id_kriteria'];

        $hasil = 0;

        foreach ($kriteria as $kolom) {

            $id_kolom =
                (int)$kolom['id_kriteria'];

            $hasil +=
                $matriks[$id_baris][$id_kolom]
                *
                $bobot[$id_kolom];
        }

        $lambda_i =
            $hasil / $bobot[$id_baris];

        $total_lambda += $lambda_i;
    }

    $lambda_max =
        $total_lambda / 3;


    // ========================================================
    // 5. CI
    // ========================================================

    $CI =
        ($lambda_max - 3)
        /
        2;


    // ========================================================
    // 6. RI
    // ========================================================

    $RI = 0.58;


    // ========================================================
    // 7. CR
    // ========================================================

    $CR = $CI / $RI;


    // ========================================================
    // 8. ANALISIS KETIDAKKONSISTENAN
    // ========================================================
    //
    // Untuk tiga kriteria:
    //
    // C1/C3 seharusnya berhubungan dengan:
    // (C1/C2) x (C2/C3)
    //
    // Sistem membandingkan nilai aktual dengan
    // nilai yang secara logis diharapkan.
    // ========================================================

    $nilai_c1_c2 = $matriks[$c1][$c2];
    $nilai_c1_c3 = $matriks[$c1][$c3];
    $nilai_c2_c3 = $matriks[$c2][$c3];

    $prediksi_c1_c3 =
        $nilai_c1_c2 * $nilai_c2_c3;

    $prediksi_c1_c2 =
        $nilai_c1_c3 / $nilai_c2_c3;

    $prediksi_c2_c3 =
        $nilai_c1_c3 / $nilai_c1_c2;


    // Selisih relatif
    $selisih_c1_c3 =
        abs(
            log($nilai_c1_c3 / $prediksi_c1_c3)
        );

    $selisih_c1_c2 =
        abs(
            log($nilai_c1_c2 / $prediksi_c1_c2)
        );

    $selisih_c2_c3 =
        abs(
            log($nilai_c2_c3 / $prediksi_c2_c3)
        );


    // ========================================================
    // TENTUKAN PASANGAN PALING BERMASALAH
    // ========================================================

    $selisih = [
        'C1-C2' => $selisih_c1_c2,
        'C1-C3' => $selisih_c1_c3,
        'C2-C3' => $selisih_c2_c3
    ];

    arsort($selisih);

    $pasangan_masalah =
        array_key_first($selisih);


    // ========================================================
    // BUAT SARAN
    // ========================================================

    if ($CR > 0.10) {

        if ($pasangan_masalah == 'C1-C2') {

            $saran[] =
                "Periksa kembali perbandingan "
                . "$kode_c1 ($nama_c1) dengan "
                . "$kode_c2 ($nama_c2).";

            $saran[] =
                "Nilai yang sedang digunakan adalah "
                . number_format($nilai_c1_c2, 4)
                . ".";

            $saran[] =
                "Hubungan ini terlihat kurang sesuai "
                . "dengan perbandingan terhadap $kode_c3.";

        } elseif ($pasangan_masalah == 'C1-C3') {

            $saran[] =
                "Periksa kembali perbandingan "
                . "$kode_c1 ($nama_c1) dengan "
                . "$kode_c3 ($nama_c3).";

            $saran[] =
                "Nilai yang sedang digunakan adalah "
                . number_format($nilai_c1_c3, 4)
                . ".";

            $saran[] =
                "Pasangan ini memiliki pengaruh terbesar "
                . "terhadap ketidakkonsistenan.";

        } else {

            $saran[] =
                "Periksa kembali perbandingan "
                . "$kode_c2 ($nama_c2) dengan "
                . "$kode_c3 ($nama_c3).";

            $saran[] =
                "Nilai yang sedang digunakan adalah "
                . number_format($nilai_c2_c3, 4)
                . ".";

            $saran[] =
                "Hubungan ini terlihat kurang sesuai "
                . "dengan perbandingan C1 terhadap kedua "
                . "kriteria tersebut.";
        }


        $saran[] =
            "Coba gunakan tingkat kepentingan yang "
            . "lebih dekat dengan hubungan kedua kriteria "
            . "berdasarkan kebutuhan sistem.";
    }
}


// ============================================================
// STATUS
// ============================================================

if (!$pasangan_lengkap) {

    $status = "BELUM LENGKAP";

} elseif ($CR <= 0.10) {

    $status = "KONSISTEN";

} else {

    $status = "TIDAK KONSISTEN";
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

    <title>Matriks AHP</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-4">


    <!-- HEADER -->

    <div class="d-flex justify-content-between
                align-items-center mb-4">

        <div>

            <h3>
                Matriks Perbandingan AHP
            </h3>

            <p class="text-muted mb-0">

                Matriks dan hasil konsistensi dihitung
                otomatis oleh sistem.

            </p>

        </div>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>


    <!-- KRITERIA -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Kriteria
            </h5>

        </div>


        <div class="card-body">

            <ol class="mb-0">

                <?php foreach ($kriteria as $k): ?>

                    <li>

                        <strong>
                            <?= htmlspecialchars(
                                $k['kode_kriteria']
                            ); ?>
                        </strong>

                        -

                        <?= htmlspecialchars(
                            $k['nama_kriteria']
                        ); ?>

                    </li>

                <?php endforeach; ?>

            </ol>

        </div>

    </div>


    <?php if (!$pasangan_lengkap): ?>


        <!-- BELUM LENGKAP -->

        <div class="alert alert-warning shadow-sm">

            <h5>
                ⚠ Preferensi Belum Lengkap
            </h5>

            <p>
                Ketiga pasangan kriteria harus
                memiliki nilai sebelum AHP dapat dihitung.
            </p>

            <ul>

                <li>
                    <?= htmlspecialchars($kode_c1); ?>
                    dibandingkan dengan
                    <?= htmlspecialchars($kode_c2); ?>
                </li>

                <li>
                    <?= htmlspecialchars($kode_c1); ?>
                    dibandingkan dengan
                    <?= htmlspecialchars($kode_c3); ?>
                </li>

                <li>
                    <?= htmlspecialchars($kode_c2); ?>
                    dibandingkan dengan
                    <?= htmlspecialchars($kode_c3); ?>
                </li>

            </ul>


            <a
                href="input_preferensi_kriteria.php"
                class="btn btn-warning"
            >
                Input Preferensi
            </a>

        </div>


    <?php else: ?>


        <!-- ==================================================
             MATRIKS
        =================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    1. Matriks Perbandingan Berpasangan
                </h5>

            </div>


            <div class="card-body">

                <p class="text-muted">

                    Nilai kebalikan dihitung otomatis oleh sistem.

                </p>


                <div class="table-responsive">

                    <table
                        class="table table-bordered
                               table-hover
                               text-center
                               align-middle"
                    >

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Kriteria
                                </th>

                                <?php foreach ($kriteria as $k): ?>

                                    <th>

                                        <?= htmlspecialchars(
                                            $k['kode_kriteria']
                                        ); ?>

                                    </th>

                                <?php endforeach; ?>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($kriteria as $baris): ?>

                            <?php
                            $id_baris =
                                (int)$baris['id_kriteria'];
                            ?>

                            <tr>

                                <th class="table-secondary">

                                    <?= htmlspecialchars(
                                        $baris['kode_kriteria']
                                    ); ?>

                                </th>


                                <?php foreach ($kriteria as $kolom): ?>

                                    <?php

                                    $id_kolom =
                                        (int)$kolom['id_kriteria'];

                                    $nilai =
                                        $matriks
                                        [$id_baris]
                                        [$id_kolom];

                                    ?>

                                    <td>

                                        <?= number_format(
                                            $nilai,
                                            4
                                        ); ?>

                                    </td>

                                <?php endforeach; ?>

                            </tr>

                        <?php endforeach; ?>


                        <tr class="table-warning">

                            <th>
                                Jumlah
                            </th>

                            <?php foreach ($kriteria as $k): ?>

                                <?php
                                $id =
                                    (int)$k['id_kriteria'];
                                ?>

                                <td>

                                    <strong>

                                        <?= number_format(
                                            $jumlah_kolom[$id],
                                            4
                                        ); ?>

                                    </strong>

                                </td>

                            <?php endforeach; ?>

                        </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- ==================================================
             NORMALISASI
        =================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-info text-white">

                <h5 class="mb-0">
                    2. Normalisasi dan Bobot Prioritas
                </h5>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table
                        class="table table-bordered
                               text-center
                               align-middle"
                    >

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Kriteria
                                </th>

                                <?php foreach ($kriteria as $k): ?>

                                    <th>

                                        <?= htmlspecialchars(
                                            $k['kode_kriteria']
                                        ); ?>

                                    </th>

                                <?php endforeach; ?>

                                <th>
                                    Bobot
                                </th>

                                <th>
                                    Persentase
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($kriteria as $baris): ?>

                            <?php
                            $id =
                                (int)$baris['id_kriteria'];
                            ?>

                            <tr>

                                <th class="table-secondary">

                                    <?= htmlspecialchars(
                                        $baris['kode_kriteria']
                                    ); ?>

                                </th>


                                <?php foreach ($kriteria as $kolom): ?>

                                    <?php
                                    $id_kolom =
                                        (int)$kolom['id_kriteria'];
                                    ?>

                                    <td>

                                        <?= number_format(
                                            $normalisasi
                                            [$id]
                                            [$id_kolom],
                                            4
                                        ); ?>

                                    </td>

                                <?php endforeach; ?>


                                <td class="table-success">

                                    <strong>

                                        <?= number_format(
                                            $bobot[$id],
                                            4
                                        ); ?>

                                    </strong>

                                </td>


                                <td class="table-success">

                                    <strong>

                                        <?= number_format(
                                            $bobot[$id] * 100,
                                            2
                                        ); ?>%

                                    </strong>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- ==================================================
             UJI KONSISTENSI
        =================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-dark text-white">

                <h5 class="mb-0">
                    3. Uji Konsistensi AHP
                </h5>

            </div>


            <div class="card-body">

                <table class="table table-bordered">

                    <tr>

                        <th width="350">
                            λ Max
                        </th>

                        <td>

                            <?= number_format(
                                $lambda_max,
                                4
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>
                            Consistency Index (CI)
                        </th>

                        <td>

                            <?= number_format(
                                $CI,
                                4
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>
                            Random Index (RI)
                        </th>

                        <td>
                            0.58
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Consistency Ratio (CR)
                        </th>

                        <td>

                            <strong class="fs-5">

                                <?= number_format(
                                    $CR,
                                    4
                                ); ?>

                            </strong>

                        </td>

                    </tr>


                    <tr>

                        <th>
                            Status
                        </th>

                        <td>

                            <?php if ($CR <= 0.10): ?>

                                <span
                                    class="badge
                                           bg-success
                                           fs-6"
                                >

                                    ✓ KONSISTEN

                                </span>


                                <div
                                    class="alert
                                           alert-success
                                           mt-3
                                           mb-0"
                                >

                                    <strong>
                                        Preferensi sudah konsisten.
                                    </strong>

                                    <br>

                                    Nilai CR ≤ 0,10 sehingga
                                    bobot kriteria dapat
                                    digunakan.

                                </div>


                            <?php else: ?>

                                <span
                                    class="badge
                                           bg-danger
                                           fs-6"
                                >

                                    ✗ TIDAK KONSISTEN

                                </span>


                                <div
                                    class="alert
                                           alert-danger
                                           mt-3"
                                >

                                    <strong>
                                        Preferensi belum konsisten.
                                    </strong>

                                    <br>

                                    CR =
                                    <strong>
                                        <?= number_format(
                                            $CR,
                                            4
                                        ); ?>
                                    </strong>

                                    <br>

                                    Batas konsistensi =
                                    <strong>
                                        0,10
                                    </strong>

                                </div>


                                <!-- SARAN OTOMATIS -->

                                <div
                                    class="alert
                                           alert-warning"
                                >

                                    <h5>
                                        💡 Saran Perbaikan
                                    </h5>


                                    <p>

                                        Jangan mengubah semua
                                        nilai sekaligus.

                                        Sistem menyarankan
                                        memeriksa pasangan berikut:

                                    </p>


                                    <ul>

                                        <?php foreach (
                                            $saran
                                            as $item
                                        ): ?>

                                            <li>

                                                <?= htmlspecialchars(
                                                    $item
                                                ); ?>

                                            </li>

                                        <?php endforeach; ?>

                                    </ul>


                                    <p class="mb-0">

                                        Setelah mengubah
                                        preferensi, buka kembali
                                        halaman matriks untuk
                                        melihat nilai CR terbaru.

                                    </p>

                                </div>


                                <a
                                    href="input_preferensi_kriteria.php"
                                    class="btn btn-warning"
                                >

                                    ← Perbaiki Preferensi

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                </table>

            </div>

        </div>


        <!-- ==================================================
             BOBOT
        =================================================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-warning">

                <h5 class="mb-0">
                    4. Bobot Prioritas Kriteria
                </h5>

            </div>


            <div class="card-body">

                <table class="table table-bordered">

                    <thead class="table-dark">

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

                    <?php foreach ($kriteria as $k): ?>

                        <?php
                        $id =
                            (int)$k['id_kriteria'];
                        ?>

                        <tr>

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $k['kode_kriteria']
                                    ); ?>

                                </strong>

                                -

                                <?= htmlspecialchars(
                                    $k['nama_kriteria']
                                ); ?>

                            </td>


                            <td>

                                <?= number_format(
                                    $bobot[$id],
                                    4
                                ); ?>

                            </td>


                            <td>

                                <?= number_format(
                                    $bobot[$id] * 100,
                                    2
                                ); ?>%

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- BUTTON -->

        <a
            href="input_preferensi_kriteria.php"
            class="btn btn-warning"
        >
            ← Ubah Preferensi
        </a>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>


    <?php endif; ?>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>
