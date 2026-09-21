<?php

session_start();
include 'koneksi.php';


// ============================================================
// 1. CEK LOGIN ADMIN
// ============================================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// ============================================================
// 2. AMBIL DATA KRITERIA
// ============================================================

$query_kriteria = "
    SELECT id_kriteria, nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if (!$result_kriteria) {
    die(
        "Gagal mengambil data kriteria: "
        . mysqli_error($koneksi)
    );
}

$kriteria = [];

while ($row = mysqli_fetch_assoc($result_kriteria)) {
    $kriteria[] = $row;
}


// ============================================================
// 3. PASTIKAN ADA 3 KRITERIA
// ============================================================

if (count($kriteria) != 3) {

    die("
        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
            text-align:center;
        '>

            <h3>Jumlah kriteria tidak sesuai</h3>

            <p>
                Sistem membutuhkan 3 kriteria:
            </p>

            <ol style='text-align:left'>
                <li>Arti Nama</li>
                <li>Tingkat Keunikan</li>
                <li>Nilai Keislaman</li>
            </ol>

            <a href='kelola_kriteria.php'>
                Kembali ke Kelola Kriteria
            </a>

        </div>
    ");

}


// ============================================================
// 4. ID KRITERIA
// ============================================================

$c1 = (int)$kriteria[0]['id_kriteria'];
$c2 = (int)$kriteria[1]['id_kriteria'];
$c3 = (int)$kriteria[2]['id_kriteria'];


// ============================================================
// 5. AMBIL PREFERENSI AHP
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
// 6. NILAI PREFERENSI
// ============================================================

$p12 = 0;
$p13 = 0;
$p23 = 0;


// ============================================================
// 7. BACA PREFERENSI
// ============================================================

while ($row = mysqli_fetch_assoc($result_preferensi)) {

    $k1 = (int)$row['kriteria_1'];
    $k2 = (int)$row['kriteria_2'];
    $nilai = (float)$row['nilai'];

    if ($k1 == $c1 && $k2 == $c2) {
        $p12 = $nilai;
    }

    if ($k1 == $c1 && $k2 == $c3) {
        $p13 = $nilai;
    }

    if ($k1 == $c2 && $k2 == $c3) {
        $p23 = $nilai;
    }
}


// ============================================================
// 8. CEK PREFERENSI
// ============================================================

if (
    $p12 <= 0 ||
    $p13 <= 0 ||
    $p23 <= 0
) {

    die("
        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
            text-align:center;
        '>

            <h3>Preferensi AHP belum lengkap</h3>

            <p>
                Silakan masukkan semua perbandingan
                kriteria terlebih dahulu.
            </p>

            <a href='preferensi_kriteria.php'>
                Input Preferensi
            </a>

        </div>
    ");

}


// ============================================================
// 9. BENTUK MATRIKS AHP
// ============================================================

$matriks = [

    [
        1,
        $p12,
        $p13
    ],

    [
        1 / $p12,
        1,
        $p23
    ],

    [
        1 / $p13,
        1 / $p23,
        1
    ]

];


// ============================================================
// 10. JUMLAH KOLOM
// ============================================================

$jumlah_kolom = [];

for ($j = 0; $j < 3; $j++) {

    $jumlah = 0;

    for ($i = 0; $i < 3; $i++) {
        $jumlah += $matriks[$i][$j];
    }

    $jumlah_kolom[$j] = $jumlah;
}


// ============================================================
// 11. NORMALISASI
// ============================================================

$normalisasi = [];

for ($i = 0; $i < 3; $i++) {

    for ($j = 0; $j < 3; $j++) {

        $normalisasi[$i][$j] =
            $matriks[$i][$j] /
            $jumlah_kolom[$j];

    }
}


// ============================================================
// 12. HITUNG BOBOT PRIORITAS
// ============================================================

$bobot = [];

for ($i = 0; $i < 3; $i++) {

    $jumlah_baris = 0;

    for ($j = 0; $j < 3; $j++) {

        $jumlah_baris +=
            $normalisasi[$i][$j];

    }

    $bobot[$i] =
        $jumlah_baris / 3;
}


// ============================================================
// 13. HITUNG WEIGHTED SUM
// ============================================================

$weighted_sum = [];

for ($i = 0; $i < 3; $i++) {

    $jumlah = 0;

    for ($j = 0; $j < 3; $j++) {

        $jumlah +=
            $matriks[$i][$j] *
            $bobot[$j];

    }

    $weighted_sum[$i] = $jumlah;
}


// ============================================================
// 14. HITUNG LAMBDA MAX
// ============================================================

$lambda = [];

for ($i = 0; $i < 3; $i++) {

    if ($bobot[$i] > 0) {

        $lambda[$i] =
            $weighted_sum[$i] /
            $bobot[$i];

    } else {

        $lambda[$i] = 0;

    }

}

$lambda_max =
    array_sum($lambda) / 3;


// ============================================================
// 15. HITUNG CI
// ============================================================

$n = 3;

$CI =
    ($lambda_max - $n) /
    ($n - 1);


// ============================================================
// 16. RANDOM INDEX
// ============================================================

$RI = 0.58;


// ============================================================
// 17. HITUNG CR
// ============================================================

if ($RI > 0) {

    $CR = $CI / $RI;

} else {

    $CR = 0;

}


// ============================================================
// 18. CEK KONSISTENSI
// ============================================================

$konsisten = ($CR <= 0.10);


// ============================================================
// 19. AMBIL DATA ALTERNATIF + NILAI
// ============================================================

$query_alternatif = "
    SELECT
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin,
        a.tingkat_keunikan,
        a.kat_keislaman,

        MAX(
            CASE
                WHEN n.id_kriteria = $c1
                THEN n.nilai
                ELSE NULL
            END
        ) AS nilai_c1,

        MAX(
            CASE
                WHEN n.id_kriteria = $c2
                THEN n.nilai
                ELSE NULL
            END
        ) AS nilai_c2,

        MAX(
            CASE
                WHEN n.id_kriteria = $c3
                THEN n.nilai
                ELSE NULL
            END
        ) AS nilai_c3

    FROM alternatif a

    LEFT JOIN nilai_alternatif n
        ON a.id_alternatif = n.id_alternatif

    GROUP BY
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin,
        a.tingkat_keunikan,
        a.kat_keislaman

    ORDER BY a.id_alternatif ASC
";


$result_alternatif = mysqli_query(
    $koneksi,
    $query_alternatif
);

if (!$result_alternatif) {

    die(
        "Gagal mengambil data alternatif: "
        . mysqli_error($koneksi)
    );

}


// ============================================================
// 20. HITUNG NILAI AKHIR
// ============================================================

$ranking = [];

while ($row = mysqli_fetch_assoc($result_alternatif)) {

    /*
    ------------------------------------------------------------
    Pastikan semua nilai tersedia
    ------------------------------------------------------------
    */

    if (
        $row['nilai_c1'] === null ||
        $row['nilai_c2'] === null ||
        $row['nilai_c3'] === null
    ) {

        $row['nilai_lengkap'] = false;
        $row['nilai_akhir'] = null;

    } else {

        $nilai_c1 =
            (float)$row['nilai_c1'];

        $nilai_c2 =
            (float)$row['nilai_c2'];

        $nilai_c3 =
            (float)$row['nilai_c3'];


        /*
        --------------------------------------------------------
        Rumus nilai akhir AHP
        --------------------------------------------------------
        */

        $nilai_akhir =

            ($nilai_c1 * $bobot[0]) +

            ($nilai_c2 * $bobot[1]) +

            ($nilai_c3 * $bobot[2]);


        $row['nilai_lengkap'] = true;

        $row['nilai_akhir'] =
            $nilai_akhir;

    }


    $ranking[] = $row;

}


// ============================================================
// 21. URUTKAN RANKING
// ============================================================

usort(
    $ranking,
    function ($a, $b) {

        /*
        Nama yang belum mempunyai
        nilai lengkap diletakkan paling bawah.
        */

        if (
            !$a['nilai_lengkap'] &&
            !$b['nilai_lengkap']
        ) {
            return 0;
        }

        if (!$a['nilai_lengkap']) {
            return 1;
        }

        if (!$b['nilai_lengkap']) {
            return -1;
        }


        /*
        Nilai terbesar menjadi ranking pertama.
        */

        return
            $b['nilai_akhir']
            <=>
            $a['nilai_akhir'];

    }
);


// ============================================================
// 22. JUMLAH DATA
// ============================================================

$total_alternatif =
    count($ranking);

$total_lengkap = 0;

foreach ($ranking as $row) {

    if ($row['nilai_lengkap']) {
        $total_lengkap++;
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

    <title>
        Hasil AHP - SPK Nama Islami
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-4">


    <!-- ======================================================
         HEADER
    ======================================================= -->

    <div
        class="d-flex
               justify-content-between
               align-items-center
               mb-4"
    >

        <div>

            <h3 class="mb-1">
                Hasil Perhitungan AHP
            </h3>

            <p class="text-muted mb-0">
                Bobot kriteria dan ranking nama Islami.
            </p>

        </div>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>



    <!-- ======================================================
         STATUS KONSISTENSI
    ======================================================= -->

    <?php if ($konsisten): ?>

        <div class="alert alert-success shadow-sm">

            <strong>
                ✓ Perbandingan Konsisten
            </strong>

            <br>

            Nilai CR =
            <strong>
                <?= number_format($CR, 4); ?>
            </strong>

            ≤ 0,10.

            <br>

            Perhitungan dapat dilanjutkan
            ke tahap ranking nama.

        </div>

    <?php else: ?>

        <div class="alert alert-danger shadow-sm">

            <strong>
                ✕ Perbandingan Tidak Konsisten
            </strong>

            <br>

            Nilai CR =
            <strong>
                <?= number_format($CR, 4); ?>
            </strong>

            > 0,10.

            <br>

            Silakan perbaiki preferensi kriteria.

            <br><br>

            <a
                href="preferensi_kriteria.php"
                class="btn btn-danger"
            >
                Ubah Preferensi
            </a>

        </div>

    <?php endif; ?>



    <?php if ($konsisten): ?>


    <!-- ======================================================
         BOBOT KRITERIA
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Bobot Kriteria
            </h5>

        </div>


        <div class="card-body">

            <table class="table table-bordered
                          table-striped
                          align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>
                            Kode
                        </th>

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

                    <?php for (
                        $i = 0;
                        $i < 3;
                        $i++
                    ): ?>

                        <tr>

                            <td>
                                C<?= $i + 1; ?>
                            </td>

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $kriteria[$i]['nama_kriteria']
                                    ); ?>

                                </strong>

                            </td>

                            <td>

                                <?= number_format(
                                    $bobot[$i],
                                    4
                                ); ?>

                            </td>

                            <td>

                                <?= number_format(
                                    $bobot[$i] * 100,
                                    2
                                ); ?>%

                            </td>

                        </tr>

                    <?php endfor; ?>

                </tbody>

            </table>

        </div>

    </div>



    <!-- ======================================================
         RUMUS
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Rumus Nilai Akhir
            </h5>

        </div>


        <div class="card-body">

            <p class="mb-2">
                Nilai akhir setiap nama dihitung berdasarkan:
            </p>


            <div class="alert alert-light border">

                <strong>
                    Nilai Akhir =
                </strong>

                (Nilai C1 × Bobot C1)
                +

                (Nilai C2 × Bobot C2)
                +

                (Nilai C3 × Bobot C3)

            </div>


            <small class="text-muted">

                Semakin tinggi nilai akhir,
                semakin tinggi peringkat nama tersebut.

            </small>

        </div>

    </div>



    <!-- ======================================================
         RANKING
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-success text-white">

            <div
                class="d-flex
                       justify-content-between
                       align-items-center"
            >

                <h5 class="mb-0">
                    Ranking Nama Islami
                </h5>

                <span class="badge bg-light text-dark">

                    <?= $total_lengkap; ?>
                    /
                    <?= $total_alternatif; ?>
                    nama memiliki nilai lengkap

                </span>

            </div>

        </div>


        <div class="card-body">


            <?php if ($total_lengkap == 0): ?>


                <div class="alert alert-warning">

                    <strong>
                        Belum ada nilai alternatif.
                    </strong>

                    <br>

                    Silakan masukkan nilai C1,
                    C2, dan C3 untuk nama-nama
                    terlebih dahulu.

                    <br><br>

                    <a
                        href="kelola_alternatif.php"
                        class="btn btn-warning"
                    >
                        Input Nilai Alternatif
                    </a>

                </div>


            <?php else: ?>


                <div class="table-responsive">


                    <table
                        class="table table-bordered
                               table-striped
                               align-middle"
                    >


                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Ranking
                                </th>

                                <th>
                                    Nama
                                </th>

                                <th>
                                    Arti Nama
                                </th>

                                <th>
                                    Jenis Kelamin
                                </th>

                                <th>
                                    C1
                                </th>

                                <th>
                                    C2
                                </th>

                                <th>
                                    C3
                                </th>

                                <th>
                                    Nilai Akhir
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $rank = 1;

                        foreach ($ranking as $row):


                            if (!$row['nilai_lengkap']) {
                                continue;
                            }

                        ?>


                            <tr>


                                <!-- RANKING -->

                                <td>

                                    <?php if ($rank == 1): ?>

                                        <span
                                            class="badge bg-warning text-dark"
                                        >
                                            #1
                                        </span>

                                    <?php elseif ($rank == 2): ?>

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            #2
                                        </span>

                                    <?php elseif ($rank == 3): ?>

                                        <span
                                            class="badge bg-danger"
                                        >
                                            #3
                                        </span>

                                    <?php else: ?>

                                        <strong>
                                            #<?= $rank; ?>
                                        </strong>

                                    <?php endif; ?>

                                </td>



                                <!-- NAMA -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['nama_islami']
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- ARTI -->

                                <td>

                                    <?= htmlspecialchars(
                                        $row['arti_nama']
                                    ); ?>

                                </td>



                                <!-- GENDER -->

                                <td>

                                    <?php

                                    if (
                                        $row['jenis_kelamin']
                                        === 'L'
                                    ) {

                                        echo "Laki-Laki";

                                    } elseif (
                                        $row['jenis_kelamin']
                                        === 'P'
                                    ) {

                                        echo "Perempuan";

                                    } else {

                                        echo "Unisex";

                                    }

                                    ?>

                                </td>



                                <!-- C1 -->

                                <td>

                                    <?= number_format(
                                        (float)$row['nilai_c1'],
                                        2
                                    ); ?>

                                </td>



                                <!-- C2 -->

                                <td>

                                    <?= number_format(
                                        (float)$row['nilai_c2'],
                                        2
                                    ); ?>

                                </td>



                                <!-- C3 -->

                                <td>

                                    <?= number_format(
                                        (float)$row['nilai_c3'],
                                        2
                                    ); ?>

                                </td>



                                <!-- NILAI AKHIR -->

                                <td>

                                    <strong
                                        class="text-success"
                                    >

                                        <?= number_format(
                                            $row['nilai_akhir'],
                                            4
                                        ); ?>

                                    </strong>

                                </td>


                            </tr>


                        <?php

                            $rank++;

                        endforeach;

                        ?>


                        </tbody>


                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>



    <!-- ======================================================
         KETERANGAN
    ======================================================= -->

    <div class="alert alert-info">

        <strong>
            Keterangan:
        </strong>

        <ul class="mb-0 mt-2">

            <li>
                C1 = Arti Nama
            </li>

            <li>
                C2 = Tingkat Keunikan
            </li>

            <li>
                C3 = Nilai Keislaman
            </li>

            <li>
                Jenis kelamin hanya digunakan
                sebagai informasi nama,
                bukan sebagai kriteria AHP.
            </li>

            <li>
                Nilai akhir terbesar menjadi
                ranking tertinggi.
            </li>

        </ul>

    </div>


    <?php endif; ?>



    <!-- ======================================================
         TOMBOL NAVIGASI
    ======================================================= -->

    <div class="mb-4">

        <a
            href="proses_ahp.php"
            class="btn btn-primary"
        >
            ← Kembali ke Proses AHP
        </a>


        <a
            href="kelola_alternatif.php"
            class="btn btn-warning"
        >
            Kelola / Input Nilai Nama
        </a>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Dashboard
        </a>

    </div>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>