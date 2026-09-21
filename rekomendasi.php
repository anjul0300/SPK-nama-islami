```php
<?php
/**
 * ============================================================
 * REKOMENDASI NAMA ISLAMI
 * Metode : AHP
 *
 * KRITERIA:
 * C1 = Arti Nama
 * C2 = Tingkat Keunikan
 * C3 = Nilai Islami
 *
 * DATABASE:
 * spk_nama_islami
 *
 * TABEL:
 * alternatif
 * kriteria
 * nilai_alternatif
 * bobot_kriteria
 * ============================================================
 */

session_start();

/* ============================================================
   1. KONEKSI DATABASE
   ============================================================ */

require_once 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal.");
}


/* ============================================================
   2. AMBIL JENIS KELAMIN
   ============================================================ */

/*
 * Sistem menerima gender dari:
 *
 * rekomendasi.php?jenis_kelamin=P
 *
 * atau
 *
 * rekomendasi.php?jenis_kelamin=L
 *
 * atau
 *
 * rekomendasi.php?jenis_kelamin=Unisex
 *
 * Jika sebelumnya menggunakan POST,
 * POST juga tetap diterima.
 */

$jenis_kelamin = '';

if (isset($_GET['jenis_kelamin'])) {
    $jenis_kelamin = trim($_GET['jenis_kelamin']);
}

if (isset($_POST['jenis_kelamin'])) {
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
}


/*
 * Normalisasi nilai gender.
 */

if ($jenis_kelamin === 'Perempuan') {
    $jenis_kelamin = 'P';
}

if ($jenis_kelamin === 'Laki-laki') {
    $jenis_kelamin = 'L';
}

if ($jenis_kelamin === 'Laki-laki (L)') {
    $jenis_kelamin = 'L';
}

if ($jenis_kelamin === 'Perempuan (P)') {
    $jenis_kelamin = 'P';
}


/* ============================================================
   3. VALIDASI GENDER
   ============================================================ */

if (
    $jenis_kelamin !== 'L' &&
    $jenis_kelamin !== 'P' &&
    $jenis_kelamin !== 'Unisex'
) {
    ?>

    <!DOCTYPE html>
    <html lang="id">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>Jenis Kelamin Belum Dipilih</title>

        <style>

            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: #f1f5f9;
            }

            .box {
                width: 90%;
                max-width: 600px;
                margin: 100px auto;
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.08);
                text-align: center;
            }

            h2 {
                color: #0f172a;
            }

            p {
                color: #64748b;
                line-height: 1.6;
            }

            .btn {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 20px;
                background: #198754;
                color: white;
                text-decoration: none;
                border-radius: 8px;
            }

        </style>

    </head>

    <body>

        <div class="box">

            <h2>
                Jenis kelamin belum dipilih
            </h2>

            <p>
                Silakan kembali ke halaman rekomendasi
                dan pilih jenis kelamin terlebih dahulu.
            </p>

            <a href="index.php" class="btn">
                Kembali ke Beranda
            </a>

        </div>

    </body>

    </html>

    <?php
    exit;
}


/* ============================================================
   4. AMBIL BOBOT KRITERIA
   ============================================================ */

$sql_bobot = "
    SELECT
        k.id_kriteria,
        k.nama_kriteria,
        bk.bobot
    FROM kriteria k
    INNER JOIN bobot_kriteria bk
        ON k.id_kriteria = bk.id_kriteria
    WHERE k.id_kriteria IN (1,2,3)
    ORDER BY k.id_kriteria ASC
";

$result_bobot = mysqli_query($koneksi, $sql_bobot);

if (!$result_bobot) {
    die(
        "Gagal mengambil bobot kriteria: "
        . mysqli_error($koneksi)
    );
}


$bobot_kriteria = [];

while ($row = mysqli_fetch_assoc($result_bobot)) {

    $id = (int)$row['id_kriteria'];

    $bobot_kriteria[$id] = [
        'nama'  => $row['nama_kriteria'],
        'bobot' => (float)$row['bobot']
    ];
}


/* ============================================================
   5. AMBIL ALTERNATIF SESUAI JENIS KELAMIN
   ============================================================ */

/*
 * BAGIAN PALING PENTING.
 *
 * Jika user memilih P:
 *
 * P       -> nama perempuan
 * Unisex  -> nama yang dapat digunakan umum
 *
 * Jika user memilih L:
 *
 * L       -> nama laki-laki
 * Unisex  -> nama yang dapat digunakan umum
 *
 * Dengan demikian nama laki-laki tidak akan muncul
 * ketika user memilih perempuan.
 */

if ($jenis_kelamin === 'P') {

    $sql_alternatif = "
        SELECT *
        FROM alternatif
        WHERE jenis_kelamin IN ('P', 'Perempuan', 'Unisex')
        ORDER BY id_alternatif ASC
    ";

} elseif ($jenis_kelamin === 'L') {

    $sql_alternatif = "
        SELECT *
        FROM alternatif
        WHERE jenis_kelamin IN ('L', 'Laki-laki', 'Unisex')
        ORDER BY id_alternatif ASC
    ";

} else {

    $sql_alternatif = "
        SELECT *
        FROM alternatif
        WHERE jenis_kelamin = 'Unisex'
        ORDER BY id_alternatif ASC
    ";
}


$result_alternatif =
    mysqli_query($koneksi, $sql_alternatif);


if (!$result_alternatif) {

    die(
        "Gagal mengambil alternatif: "
        . mysqli_error($koneksi)
    );

}


$alternatif = [];


while ($row = mysqli_fetch_assoc($result_alternatif)) {

    $id = (int)$row['id_alternatif'];

    $alternatif[$id] = $row;
}


/* ============================================================
   6. CEK DATA ALTERNATIF
   ============================================================ */

if (count($alternatif) == 0) {

    ?>

    <!DOCTYPE html>

    <html lang="id">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>Data Tidak Ditemukan</title>

        <style>

            body {
                font-family: Arial, sans-serif;
                background: #f1f5f9;
            }

            .box {
                width: 90%;
                max-width: 600px;
                margin: 100px auto;
                background: white;
                padding: 30px;
                border-radius: 15px;
                text-align: center;
            }

            .btn {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 20px;
                background: #198754;
                color: white;
                text-decoration: none;
                border-radius: 8px;
            }

        </style>

    </head>

    <body>

        <div class="box">

            <h2>
                Data nama tidak ditemukan
            </h2>

            <p>
                Belum terdapat nama yang sesuai dengan
                jenis kelamin yang dipilih.
            </p>

            <a href="index.php" class="btn">
                Kembali
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;
}


/* ============================================================
   7. AMBIL NILAI ALTERNATIF
   ============================================================ */

$sql_nilai = "
    SELECT
        id_alternatif,
        id_kriteria,
        nilai
    FROM nilai_alternatif
    WHERE id_kriteria IN (1,2,3)
";

$result_nilai =
    mysqli_query($koneksi, $sql_nilai);


if (!$result_nilai) {

    die(
        "Gagal mengambil nilai alternatif: "
        . mysqli_error($koneksi)
    );

}


$nilai = [];


while ($row = mysqli_fetch_assoc($result_nilai)) {

    $id_alternatif =
        (int)$row['id_alternatif'];

    $id_kriteria =
        (int)$row['id_kriteria'];

    $nilai[$id_alternatif][$id_kriteria] =
        (float)$row['nilai'];
}


/* ============================================================
   8. KRITERIA YANG DIGUNAKAN
   ============================================================ */

$kriteria_id = [1,2,3];


/* ============================================================
   9. NILAI MAKSIMUM
   ============================================================ */

$nilai_maksimum = [];


foreach ($kriteria_id as $id_kriteria) {

    $nilai_maksimum[$id_kriteria] = 0;

    foreach ($alternatif as $id_alternatif => $data) {

        $nilai_sekarang = 0;

        if (
            isset(
                $nilai[$id_alternatif][$id_kriteria]
            )
        ) {

            $nilai_sekarang =
                $nilai[$id_alternatif][$id_kriteria];

        }


        if (
            $nilai_sekarang >
            $nilai_maksimum[$id_kriteria]
        ) {

            $nilai_maksimum[$id_kriteria] =
                $nilai_sekarang;

        }

    }
}


/* ============================================================
   10. HITUNG NILAI NORMALISASI DAN SKOR
   ============================================================ */

$hasil = [];


foreach ($alternatif as $id_alternatif => $data) {

    $normalisasi = [];

    $nilai_asli = [];

    $skor_akhir = 0;


    foreach ($kriteria_id as $id_kriteria) {

        /*
         * Nilai asli
         */

        $nilai_sekarang = 0;


        if (
            isset(
                $nilai[$id_alternatif][$id_kriteria]
            )
        ) {

            $nilai_sekarang =
                $nilai[$id_alternatif][$id_kriteria];

        }


        $nilai_asli[$id_kriteria] =
            $nilai_sekarang;


        /*
         * Normalisasi
         */

        if (
            $nilai_maksimum[$id_kriteria] > 0
        ) {

            $normal =
                $nilai_sekarang /
                $nilai_maksimum[$id_kriteria];

        } else {

            $normal = 0;

        }


        $normalisasi[$id_kriteria] =
            $normal;


        /*
         * Bobot AHP
         */

        $bobot = 0;


        if (
            isset(
                $bobot_kriteria[$id_kriteria]
            )
        ) {

            $bobot =
                $bobot_kriteria[$id_kriteria]['bobot'];

        }


        /*
         * Skor
         */

        $skor_akhir +=
            $normal * $bobot;

    }


    $hasil[] = [

        'id' =>
            $id_alternatif,

        'data' =>
            $data,

        'nilai' =>
            $nilai_asli,

        'normalisasi' =>
            $normalisasi,

        'skor' =>
            $skor_akhir

    ];
}


/* ============================================================
   11. SORTING
   ============================================================ */

usort(
    $hasil,
    function ($a, $b) {

        return
            $b['skor'] <=> $a['skor'];

    }
);


/* ============================================================
   12. FUNGSI AMBIL DATA
   ============================================================ */

function namaAlternatif($data)
{

    return
        isset($data['nama_islami'])
        ? $data['nama_islami']
        : '-';

}


function artiNama($data)
{

    return
        isset($data['arti_nama'])
        ? $data['arti_nama']
        : '-';

}


function jenisKelamin($data)
{

    return
        isset($data['jenis_kelamin'])
        ? $data['jenis_kelamin']
        : '-';

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
        Hasil Rekomendasi Nama Islami
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f6f9;

            color: #1f2937;

        }


        .container {

            width: 94%;

            max-width: 1250px;

            margin: 35px auto;

        }


        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .header h1 {

            margin: 0;

            font-size: 28px;

        }


        .header p {

            color: #64748b;

            margin-top: 8px;

        }


        .btn {

            display: inline-block;

            text-decoration: none;

            color: white;

            background: #198754;

            padding: 11px 18px;

            border-radius: 8px;

            font-size: 14px;

        }


        .btn:hover {

            background: #157347;

        }


        .card {

            background: white;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 2px 8px
                rgba(0,0,0,0.08);

            overflow: hidden;

        }


        .card-header {

            padding: 15px 18px;

            background: #198754;

            color: white;

            font-size: 18px;

            font-weight: bold;

        }


        .bobot-container {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            padding: 20px;

        }


        .bobot-box {

            border: 1px solid #dee2e6;

            border-radius: 10px;

            padding: 20px;

            text-align: center;

        }


        .kode {

            font-weight: bold;

            color: #64748b;

        }


        .bobot-nama {

            margin: 8px 0;

            font-size: 14px;

        }


        .bobot-nilai {

            font-size: 25px;

            font-weight: bold;

            color: #198754;

        }


        .info {

            background: #cff4fc;

            border: 1px solid #b6effb;

            color: #055160;

            padding: 15px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        .gender {

            display: inline-block;

            background: #e8f5e9;

            color: #198754;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .table-wrapper {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background: #212529;

            color: white;

            padding: 13px;

            font-size: 13px;

            white-space: nowrap;

        }


        td {

            padding: 13px;

            border-bottom: 1px solid #dee2e6;

            font-size: 13px;

            vertical-align: middle;

        }


        tbody tr:hover {

            background: #f8f9fa;

        }


        .center {

            text-align: center;

        }


        .nama {

            font-weight: bold;

            font-size: 15px;

        }


        .arti {

            max-width: 350px;

            line-height: 1.5;

        }


        .ranking {

            width: 36px;

            height: 36px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background: #e9ecef;

            font-weight: bold;

        }


        .rank-1 {

            background: #ffc107;

        }


        .rank-2 {

            background: #adb5bd;

            color: white;

        }


        .rank-3 {

            background: #cd7f32;

            color: white;

        }


        .score {

            font-weight: bold;

            color: #087f5b;

            margin-bottom: 6px;

        }


        .progress {

            width: 100%;

            height: 8px;

            background: #e9ecef;

            border-radius: 10px;

            overflow: hidden;

        }


        .progress-bar {

            height: 100%;

            background: #198754;

        }


        .footer {

            text-align: center;

            color: #64748b;

            padding: 25px;

            font-size: 13px;

        }


        @media (max-width: 768px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }


            .bobot-container {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>
                Hasil Rekomendasi Nama Islami
            </h1>

            <p>
                Hasil perhitungan menggunakan
                metode Analytical Hierarchy Process (AHP).
            </p>

        </div>


        <a
            href="index.php"
            class="btn"
        >
            Kembali ke Beranda
        </a>

    </div>


    <!-- INFORMASI GENDER -->

    <div class="info">

        <strong>
            Jenis Kelamin:
        </strong>

        <?php

        if ($jenis_kelamin === 'P') {

            echo " Perempuan";

        } elseif ($jenis_kelamin === 'L') {

            echo " Laki-laki";

        } else {

            echo " Unisex";

        }

        ?>

        <br>

        <small>

            Sistem hanya menampilkan nama yang sesuai
            dengan jenis kelamin yang dipilih.

        </small>

    </div>


    <!-- BOBOT -->

    <div class="card">

        <div class="card-header">

            Bobot Kriteria AHP

        </div>


        <div class="bobot-container">


            <?php foreach ($kriteria_id as $id): ?>


                <div class="bobot-box">


                    <div class="kode">

                        C<?= $id ?>

                    </div>


                    <div class="bobot-nama">

                        <?= htmlspecialchars(
                            $bobot_kriteria[$id]['nama']
                            ?? '-'
                        ) ?>

                    </div>


                    <div class="bobot-nilai">

                        <?= number_format(
                            $bobot_kriteria[$id]['bobot']
                            ?? 0,
                            4
                        ) ?>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>

    </div>


    <!-- HASIL -->

    <div class="card">


        <div class="card-header">

            Ranking Nama Islami

        </div>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th class="center">
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

                        <th class="center">
                            C1
                        </th>

                        <th class="center">
                            C2
                        </th>

                        <th class="center">
                            C3
                        </th>

                        <th>
                            Skor AHP
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $ranking = 1;

                foreach ($hasil as $item):

                    $data = $item['data'];

                    $nama =
                        namaAlternatif($data);

                    $arti =
                        artiNama($data);

                    $gender =
                        jenisKelamin($data);

                    $skor =
                        $item['skor'];

                ?>


                    <tr>


                        <!-- RANKING -->

                        <td class="center">

                            <span
                                class="
                                ranking
                                <?=

                                $ranking == 1
                                ? 'rank-1'

                                : (
                                    $ranking == 2
                                    ? 'rank-2'

                                    : (
                                        $ranking == 3
                                        ? 'rank-3'
                                        : ''
                                    )
                                )

                                ?>"
                            >

                                <?= $ranking ?>

                            </span>

                        </td>


                        <!-- NAMA -->

                        <td>

                            <span class="nama">

                                <?= htmlspecialchars(
                                    $nama
                                ) ?>

                            </span>

                        </td>


                        <!-- ARTI -->

                        <td class="arti">

                            <?= htmlspecialchars(
                                $arti
                            ) ?>

                        </td>


                        <!-- GENDER -->

                        <td>

                            <span class="gender">

                                <?= htmlspecialchars(
                                    $gender
                                ) ?>

                            </span>

                        </td>


                        <!-- C1 -->

                        <td class="center">

                            <?= number_format(
                                $item['nilai'][1]
                                ?? 0,
                                2
                            ) ?>

                        </td>


                        <!-- C2 -->

                        <td class="center">

                            <?= number_format(
                                $item['nilai'][2]
                                ?? 0,
                                2
                            ) ?>

                        </td>


                        <!-- C3 -->

                        <td class="center">

                            <?= number_format(
                                $item['nilai'][3]
                                ?? 0,
                                2
                            ) ?>

                        </td>


                        <!-- SKOR -->

                        <td style="min-width:150px;">


                            <div class="score">

                                <?= number_format(
                                    $skor,
                                    4
                                ) ?>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar"
                                    style="
                                    width:
                                    <?= min(
                                        $skor * 100,
                                        100
                                    ) ?>%;
                                    "
                                ></div>

                            </div>


                            <small>

                                <?= number_format(
                                    $skor * 100,
                                    2
                                ) ?>%

                            </small>


                        </td>


                    </tr>


                <?php

                    $ranking++;

                endforeach;

                ?>


                </tbody>

            </table>

        </div>

    </div>


    <!-- FOOTER -->

    <div class="footer">

        Sistem Pendukung Keputusan
        Pemilihan Nama-Nama Islami

        <br>

        Metode Analytical Hierarchy Process (AHP)

    </div>


</div>


</body>

</html>
```
