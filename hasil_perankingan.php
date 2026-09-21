<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if (!$result_kriteria) {
    die(
        "Gagal mengambil kriteria: "
        . mysqli_error($koneksi)
    );
}


$kriteria = [];

while ($row = mysqli_fetch_assoc($result_kriteria)) {
    $kriteria[] = $row;
}


// ============================================================
// CEK JUMLAH KRITERIA
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
// AMBIL PREFERENSI KRITERIA
// ============================================================

$query_preferensi = "
    SELECT
        kriteria_1,
        kriteria_2,
        nilai
    FROM preferensi_kriteria
";

$result_preferensi = mysqli_query(
    $koneksi,
    $query_preferensi
);

if (!$result_preferensi) {

    die(
        "Gagal mengambil preferensi: "
        . mysqli_error($koneksi)
    );

}


// ============================================================
// BENTUK PREFERENSI
// ============================================================

$pref = [];

while ($row = mysqli_fetch_assoc($result_preferensi)) {

    $k1 = (int) $row['kriteria_1'];
    $k2 = (int) $row['kriteria_2'];
    $nilai = (float) $row['nilai'];

    if ($nilai <= 0) {
        continue;
    }

    $pref[$k1][$k2] = $nilai;

    // Nilai kebalikan
    $pref[$k2][$k1] = 1 / $nilai;
}


// ============================================================
// MATRIKS PERBANDINGAN
// ============================================================

$matriks = [

    $c1 => [
        $c1 => 1,
        $c2 => 1,
        $c3 => 1
    ],

    $c2 => [
        $c1 => 1,
        $c2 => 1,
        $c3 => 1
    ],

    $c3 => [
        $c1 => 1,
        $c2 => 1,
        $c3 => 1
    ]

];


// ============================================================
// MASUKKAN PREFERENSI
// ============================================================

foreach ($matriks as $baris => $kolom_data) {

    foreach ($kolom_data as $kolom => $nilai) {

        if (
            isset($pref[$baris][$kolom]) &&
            $pref[$baris][$kolom] > 0
        ) {

            $matriks[$baris][$kolom]
                = $pref[$baris][$kolom];

        }

    }

}


// ============================================================
// JUMLAH KOLOM
// ============================================================

$jumlah_kolom_1 =
    $matriks[$c1][$c1] +
    $matriks[$c2][$c1] +
    $matriks[$c3][$c1];

$jumlah_kolom_2 =
    $matriks[$c1][$c2] +
    $matriks[$c2][$c2] +
    $matriks[$c3][$c2];

$jumlah_kolom_3 =
    $matriks[$c1][$c3] +
    $matriks[$c2][$c3] +
    $matriks[$c3][$c3];


// ============================================================
// NORMALISASI
// ============================================================

$normalisasi = [];


$normalisasi[$c1][$c1] =
    $matriks[$c1][$c1] / $jumlah_kolom_1;

$normalisasi[$c2][$c1] =
    $matriks[$c2][$c1] / $jumlah_kolom_1;

$normalisasi[$c3][$c1] =
    $matriks[$c3][$c1] / $jumlah_kolom_1;


$normalisasi[$c1][$c2] =
    $matriks[$c1][$c2] / $jumlah_kolom_2;

$normalisasi[$c2][$c2] =
    $matriks[$c2][$c2] / $jumlah_kolom_2;

$normalisasi[$c3][$c2] =
    $matriks[$c3][$c2] / $jumlah_kolom_2;


$normalisasi[$c1][$c3] =
    $matriks[$c1][$c3] / $jumlah_kolom_3;

$normalisasi[$c2][$c3] =
    $matriks[$c2][$c3] / $jumlah_kolom_3;

$normalisasi[$c3][$c3] =
    $matriks[$c3][$c3] / $jumlah_kolom_3;


// ============================================================
// BOBOT KRITERIA
// ============================================================

$bobot_c1 =
    (
        $normalisasi[$c1][$c1] +
        $normalisasi[$c1][$c2] +
        $normalisasi[$c1][$c3]
    ) / 3;


$bobot_c2 =
    (
        $normalisasi[$c2][$c1] +
        $normalisasi[$c2][$c2] +
        $normalisasi[$c2][$c3]
    ) / 3;


$bobot_c3 =
    (
        $normalisasi[$c3][$c1] +
        $normalisasi[$c3][$c2] +
        $normalisasi[$c3][$c3]
    ) / 3;


// ============================================================
// CEK KONSISTENSI
// ============================================================

$ws_c1 =
    ($matriks[$c1][$c1] * $bobot_c1) +
    ($matriks[$c1][$c2] * $bobot_c2) +
    ($matriks[$c1][$c3] * $bobot_c3);

$ws_c2 =
    ($matriks[$c2][$c1] * $bobot_c1) +
    ($matriks[$c2][$c2] * $bobot_c2) +
    ($matriks[$c2][$c3] * $bobot_c3);

$ws_c3 =
    ($matriks[$c3][$c1] * $bobot_c1) +
    ($matriks[$c3][$c2] * $bobot_c2) +
    ($matriks[$c3][$c3] * $bobot_c3);


$lambda_max =
    (
        ($ws_c1 / $bobot_c1) +
        ($ws_c2 / $bobot_c2) +
        ($ws_c3 / $bobot_c3)
    ) / 3;


$CI =
    ($lambda_max - 3) / 2;


$RI = 0.58;


$CR = $CI / $RI;


// ============================================================
// AMBIL DATA ALTERNATIF + NILAI
// ============================================================

$query_alternatif = "
    SELECT
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin
    FROM alternatif a
    ORDER BY a.id_alternatif ASC
";

$result_alternatif = mysqli_query(
    $koneksi,
    $query_alternatif
);

if (!$result_alternatif) {

    die(
        "Gagal mengambil alternatif: "
        . mysqli_error($koneksi)
    );

}


// ============================================================
// SIAPKAN ARRAY HASIL
// ============================================================

$hasil = [];


// ============================================================
// PROSES SETIAP ALTERNATIF
// ============================================================

while ($alternatif = mysqli_fetch_assoc($result_alternatif)) {

    $id_alternatif =
        (int) $alternatif['id_alternatif'];


    $nilai_c1 = 0;
    $nilai_c2 = 0;
    $nilai_c3 = 0;


    // --------------------------------------------------------
    // AMBIL NILAI ALTERNATIF
    // --------------------------------------------------------

    $query_nilai = "
        SELECT
            id_kriteria,
            nilai
        FROM nilai_alternatif
        WHERE id_alternatif = $id_alternatif
    ";


    $result_nilai = mysqli_query(
        $koneksi,
        $query_nilai
    );


    if (!$result_nilai) {

        die(
            "Gagal mengambil nilai alternatif: "
            . mysqli_error($koneksi)
        );

    }


    while ($nilai = mysqli_fetch_assoc($result_nilai)) {

        $id_kriteria =
            (int) $nilai['id_kriteria'];

        $nilai = (float) $nilai['nilai'];


        if ($id_kriteria === $c1) {

            $nilai_c1 = $nilai;

        }

        elseif ($id_kriteria === $c2) {

            $nilai_c2 = $nilai;

        }

        elseif ($id_kriteria === $c3) {

            $nilai_c3 = $nilai;

        }

    }


    // --------------------------------------------------------
    // HITUNG NILAI AKHIR
    // --------------------------------------------------------

    $nilai_akhir =
        ($nilai_c1 * $bobot_c1) +
        ($nilai_c2 * $bobot_c2) +
        ($nilai_c3 * $bobot_c3);


    // --------------------------------------------------------
    // SIMPAN HASIL
    // --------------------------------------------------------

    $hasil[] = [

        'id_alternatif' =>
            $id_alternatif,

        'nama_islami' =>
            $alternatif['nama_islami'],

        'arti_nama' =>
            $alternatif['arti_nama'],

        'jenis_kelamin' =>
            $alternatif['jenis_kelamin'],

        'nilai_c1' =>
            $nilai_c1,

        'nilai_c2' =>
            $nilai_c2,

        'nilai_c3' =>
            $nilai_c3,

        'nilai_akhir' =>
            $nilai_akhir

    ];

}


// ============================================================
// URUTKAN NILAI TERTINGGI
// ============================================================

usort(
    $hasil,
    function ($a, $b) {

        return $b['nilai_akhir']
            <=> $a['nilai_akhir'];

    }
);


// ============================================================
// TAMPILAN
// ============================================================

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Hasil Perankingan - SPK Nama Islami
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-4">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="d-flex justify-content-between
        align-items-center mb-4"
    >

        <div>

            <h3>
                Hasil Perankingan Nama Islami
            </h3>

            <p class="text-muted mb-0">

                Hasil perhitungan nilai akhir
                berdasarkan metode AHP.

            </p>

        </div>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>



    <!-- =====================================================
         STATUS CR
    ====================================================== -->

    <?php if ($CR <= 0.10): ?>

        <div class="alert alert-success">

            <strong>
                Perbandingan kriteria konsisten.
            </strong>

            Nilai CR =
            <?= number_format($CR, 4); ?>

        </div>

    <?php else: ?>

        <div class="alert alert-danger">

            <strong>
                Perbandingan kriteria tidak konsisten.
            </strong>

            Nilai CR =
            <?= number_format($CR, 4); ?>

            <br>

            Silakan perbaiki preferensi kriteria.

        </div>

    <?php endif; ?>



    <!-- =====================================================
         BOBOT
    ====================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Bobot Kriteria
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

                    <tr>

                        <td>
                            <?= htmlspecialchars($nama_c1); ?>
                        </td>

                        <td>
                            <?= number_format(
                                $bobot_c1,
                                4
                            ); ?>
                        </td>

                        <td>
                            <?= number_format(
                                $bobot_c1 * 100,
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
                                $bobot_c2,
                                4
                            ); ?>
                        </td>

                        <td>
                            <?= number_format(
                                $bobot_c2 * 100,
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
                                $bobot_c3,
                                4
                            ); ?>
                        </td>

                        <td>
                            <?= number_format(
                                $bobot_c3 * 100,
                                2
                            ); ?>%
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>



    <!-- =====================================================
         HASIL RANKING
    ====================================================== -->

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white">

            <h5 class="mb-0">
                Ranking Nama Islami
            </h5>

        </div>


        <div class="card-body">


            <?php if (count($hasil) == 0): ?>

                <div class="alert alert-warning">

                    Belum ada data alternatif.

                </div>

            <?php else: ?>


                <div class="table-responsive">

                    <table
                        class="table table-bordered
                        table-striped
                        align-middle text-center"
                    >

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Ranking
                                </th>

                                <th>
                                    Nama Islami
                                </th>

                                <th>
                                    Arti Nama
                                </th>

                                <th>
                                    Jenis Kelamin
                                </th>

                                <th>
                                    <?= htmlspecialchars($nama_c1); ?>
                                </th>

                                <th>
                                    <?= htmlspecialchars($nama_c2); ?>
                                </th>

                                <th>
                                    <?= htmlspecialchars($nama_c3); ?>
                                </th>

                                <th>
                                    Nilai Akhir
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $ranking = 1;

                        foreach ($hasil as $data):

                        ?>


                            <tr>


                                <!-- RANKING -->

                                <td>

                                    <?php if ($ranking == 1): ?>

                                        <span
                                            class="badge bg-warning text-dark"
                                        >
                                            #1
                                        </span>

                                    <?php elseif ($ranking == 2): ?>

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            #2
                                        </span>

                                    <?php elseif ($ranking == 3): ?>

                                        <span
                                            class="badge bg-danger"
                                        >
                                            #3
                                        </span>

                                    <?php else: ?>

                                        <strong>
                                            #<?= $ranking; ?>
                                        </strong>

                                    <?php endif; ?>

                                </td>



                                <!-- NAMA -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $data['nama_islami']
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- ARTI -->

                                <td>

                                    <?= htmlspecialchars(
                                        $data['arti_nama']
                                    ); ?>

                                </td>



                                <!-- JENIS KELAMIN -->

                                <td>

                                    <?= htmlspecialchars(
                                        $data['jenis_kelamin']
                                    ); ?>

                                </td>



                                <!-- NILAI C1 -->

                                <td>

                                    <?= number_format(
                                        $data['nilai_c1'],
                                        2
                                    ); ?>

                                </td>



                                <!-- NILAI C2 -->

                                <td>

                                    <?= number_format(
                                        $data['nilai_c2'],
                                        2
                                    ); ?>

                                </td>



                                <!-- NILAI C3 -->

                                <td>

                                    <?= number_format(
                                        $data['nilai_c3'],
                                        2
                                    ); ?>

                                </td>



                                <!-- NILAI AKHIR -->

                                <td>

                                    <strong>

                                        <?= number_format(
                                            $data['nilai_akhir'],
                                            4
                                        ); ?>

                                    </strong>

                                </td>


                            </tr>


                        <?php

                        $ranking++;

                        endforeach;

                        ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>



    <!-- =====================================================
         KETERANGAN
    ====================================================== -->

    <div class="alert alert-info mt-4">

        <strong>
            Keterangan:
        </strong>

        Semakin tinggi nilai akhir,
        semakin tinggi peringkat nama Islami
        berdasarkan bobot kriteria AHP.

    </div>



    <!-- =====================================================
         TOMBOL
    ====================================================== -->

    <div class="mt-4 mb-5">

        <a
            href="perhitungan_ahp.php"
            class="btn btn-primary"
        >
            Perhitungan AHP
        </a>


        <a
            href="input_preferensi.php"
            class="btn btn-warning"
        >
            Input Preferensi
        </a>


        <a
            href="kelola_alternatif.php"
            class="btn btn-secondary"
        >
            Kelola Nama
        </a>

    </div>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>