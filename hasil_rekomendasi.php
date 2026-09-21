<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['jenis_kelamin'])) {
    header("Location: pilih_jenis_kelamin.php");
    exit;
}

if (!isset($_SESSION['ahp'])) {
    header("Location: input_preferensi.php");
    exit;
}

$ahp = $_SESSION['ahp'];
$gender = $_SESSION['jenis_kelamin'];

/*
|--------------------------------------------------------------------------
| AMBIL DATA KRITERIA DARI DATABASE
|--------------------------------------------------------------------------
| Nama kriteria TIDAK lagi ditulis secara hardcode.
| Kode C1, C2, C3 digunakan sebagai identitas tetap.
|--------------------------------------------------------------------------
*/

$kriteria = [];

$query_kriteria = "
    SELECT 
        id_kriteria,
        kode_kriteria,
        nama_kriteria
    FROM kriteria
    WHERE kode_kriteria IN ('C1', 'C2', 'C3')
    ORDER BY 
        CASE kode_kriteria
            WHEN 'C1' THEN 1
            WHEN 'C2' THEN 2
            WHEN 'C3' THEN 3
        END
";

$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if (!$result_kriteria) {
    die(
        "Gagal mengambil data kriteria: "
        . mysqli_error($koneksi)
    );
}

while ($row = mysqli_fetch_assoc($result_kriteria)) {
    $kriteria[] = $row;
}

/*
|--------------------------------------------------------------------------
| VALIDASI JUMLAH KRITERIA
|--------------------------------------------------------------------------
*/

if (count($kriteria) !== 3) {
    die("
        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
            border:1px solid #ddd;
            border-radius:10px;
        '>
            <h3>Data Kriteria Tidak Lengkap</h3>

            <p>
                Sistem membutuhkan tiga kriteria dengan kode
                <strong>C1, C2, dan C3</strong>.
            </p>

            <a href='kelola_kriteria.php'>
                Kembali ke Kelola Kriteria
            </a>
        </div>
    ");
}

/*
|--------------------------------------------------------------------------
| IDENTITAS KRITERIA
|--------------------------------------------------------------------------
*/

$id_c1 = null;
$id_c2 = null;
$id_c3 = null;

$nama_c1 = '';
$nama_c2 = '';
$nama_c3 = '';

foreach ($kriteria as $k) {

    $kode = strtoupper(trim($k['kode_kriteria']));

    if ($kode === 'C1') {
        $id_c1 = (int)$k['id_kriteria'];
        $nama_c1 = $k['nama_kriteria'];
    }

    elseif ($kode === 'C2') {
        $id_c2 = (int)$k['id_kriteria'];
        $nama_c2 = $k['nama_kriteria'];
    }

    elseif ($kode === 'C3') {
        $id_c3 = (int)$k['id_kriteria'];
        $nama_c3 = $k['nama_kriteria'];
    }
}

/*
|--------------------------------------------------------------------------
| VALIDASI ID KRITERIA
|--------------------------------------------------------------------------
*/

if (
    $id_c1 === null ||
    $id_c2 === null ||
    $id_c3 === null
) {
    die("
        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
            border:1px solid #ddd;
            border-radius:10px;
        '>
            <h3>Kode Kriteria Tidak Lengkap</h3>

            <p>
                Pastikan database memiliki kriteria:
                C1, C2, dan C3.
            </p>

            <a href='kelola_kriteria.php'>
                Kembali ke Kelola Kriteria
            </a>
        </div>
    ");
}

/*
|--------------------------------------------------------------------------
| DATA ALTERNATIF
|--------------------------------------------------------------------------
*/

$alternatif = [];

/*
|--------------------------------------------------------------------------
| AMBIL NILAI ALTERNATIF
|--------------------------------------------------------------------------
| ID kriteria diambil dari database, bukan nama kriteria.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT 
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin,
        a.kat_keislaman,

        MAX(
            CASE 
                WHEN n.id_kriteria = ?
                THEN n.nilai
            END
        ) AS c1,

        MAX(
            CASE 
                WHEN n.id_kriteria = ?
                THEN n.nilai
            END
        ) AS c2,

        MAX(
            CASE 
                WHEN n.id_kriteria = ?
                THEN n.nilai
            END
        ) AS c3

    FROM alternatif a

    LEFT JOIN nilai_alternatif n
        ON n.id_alternatif = a.id_alternatif

    WHERE a.jenis_kelamin IN (?, 'Unisex')

    GROUP BY
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin,
        a.kat_keislaman

    ORDER BY a.id_alternatif
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die(
        "Query gagal dipersiapkan: "
        . mysqli_error($koneksi)
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "iiis",
    $id_c1,
    $id_c2,
    $id_c3,
    $gender
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

while ($r = mysqli_fetch_assoc($res)) {

    $vals = [
        (float)($r['c1'] ?? 0),
        (float)($r['c2'] ?? 0),
        (float)($r['c3'] ?? 0)
    ];

    /*
    |--------------------------------------------------------------------------
    | LEWATI ALTERNATIF YANG BELUM MEMILIKI NILAI
    |--------------------------------------------------------------------------
    */

    if (max($vals) <= 0) {
        continue;
    }

    $alternatif[] = $r + [
        'vals' => $vals
    ];
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| PERHITUNGAN SKOR AHP
|--------------------------------------------------------------------------
*/

foreach ($alternatif as &$r) {

    $score = 0;

    $norm = [];

    $weighted = [];

    for ($i = 0; $i < 3; $i++) {

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI
        |--------------------------------------------------------------------------
        | Skala penilaian maksimal = 9
        |--------------------------------------------------------------------------
        */

        $norm[$i] = $r['vals'][$i] / 9;

        /*
        |--------------------------------------------------------------------------
        | NILAI TERBOBOT
        |--------------------------------------------------------------------------
        */

        $weighted[$i] =
            $norm[$i] *
            $ahp['bobot'][$i];

        /*
        |--------------------------------------------------------------------------
        | SKOR AKHIR
        |--------------------------------------------------------------------------
        */

        $score += $weighted[$i];
    }

    $r['score'] = $score;

    $r['norm'] = $norm;

    $r['weighted'] = $weighted;
}

unset($r);

/*
|--------------------------------------------------------------------------
| URUTKAN SKOR TERTINGGI
|--------------------------------------------------------------------------
*/

usort(
    $alternatif,
    function ($a, $b) {
        return $b['score'] <=> $a['score'];
    }
);

/*
|--------------------------------------------------------------------------
| TOP 5
|--------------------------------------------------------------------------
*/

$top5 = array_slice(
    $alternatif,
    0,
    5
);

/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$total_alt = count($alternatif);

$avg_score = $total_alt > 0
    ? array_sum(
        array_column(
            $alternatif,
            'score'
        )
    ) / $total_alt
    : 0;

$highest_score = $total_alt > 0
    ? $alternatif[0]['score']
    : 0;

/*
|--------------------------------------------------------------------------
| LABEL KRITERIA
|--------------------------------------------------------------------------
*/

$labels = [
    $nama_c1,
    $nama_c2,
    $nama_c3
];

$icons = [
    '📖',
    '⭐',
    '☪️'
];

?>

<!doctype html>

<html lang="id">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Hasil Rekomendasi Nama Islami
</title>

<style>

*{
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    margin:0;
    background:#f4f7f5;
    font-family:Arial, sans-serif;
    color:#183028;
}

.wrap{
    max-width:1100px;
    margin:35px auto;
    padding:20px;
}

/* =========================
   HEADER
========================= */

.head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

.head h1{
    color:#0f5132;
    margin-bottom:8px;
}

/* =========================
   BUTTON
========================= */

.btn{
    display:inline-block;
    text-decoration:none;
    padding:11px 16px;
    border-radius:9px;
    font-weight:700;
    border:0;
    cursor:pointer;
    font-size:14px;
}

.green{
    background:#0f5132;
    color:#fff;
}

.light{
    background:#e8efeb;
    color:#183028;
}

/* =========================
   CARD
========================= */

.info,
.card{
    background:#fff;
    border-radius:16px;
    padding:22px;
    box-shadow:0 6px 20px rgba(0,0,0,.06);
    margin-top:20px;
}

.info{
    border-left:5px solid #0f5132;
}

/* =========================
   STATISTIK
========================= */

.grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.metric{
    text-align:center;
    background:#f6faf8;
    border-radius:12px;
    padding:15px;
}

.metric b{
    display:block;
    font-size:22px;
    color:#0f5132;
    margin-top:6px;
}

/* =========================
   TABLE
========================= */

.tablewrap{
    overflow:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,
td{
    padding:12px;
    border-bottom:1px solid #e3e9e5;
    text-align:left;
    vertical-align:middle;
}

th{
    background:#0f5132;
    color:#fff;
}

.rank{
    font-weight:800;
}

.score{
    font-weight:800;
    color:#0f5132;
}

.small{
    font-size:12px;
    color:#66736d;
}

/* =========================
   BOBOT
========================= */

.weights{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.weight{
    padding:15px;
    border:1px solid #dfe9e3;
    border-radius:12px;
}

.bar{
    height:8px;
    background:#dce9e1;
    border-radius:10px;
    margin-top:8px;
}

.bar span{
    display:block;
    height:100%;
    background:#0f5132;
    border-radius:10px;
}

/* =========================
   BREAKDOWN
========================= */

.breakdown{
    font-size:11px;
    color:#66736d;
    margin-top:5px;
}

/* =========================
   CATEGORY
========================= */

.category-badge{
    display:inline-block;
    padding:3px 8px;
    border-radius:12px;
    font-size:11px;
    font-weight:600;
}

.cat-asmaul{
    background:#d4edda;
    color:#155724;
}

.cat-sahabat{
    background:#fff3cd;
    color:#856404;
}

.cat-arab{
    background:#d1ecf1;
    color:#0c5460;
}

.cat-lokal{
    background:#f8d7da;
    color:#721c24;
}

/* =========================
   INFO TOP 5
========================= */

.result-info{
    background:#f6faf8;
    border-radius:10px;
    padding:12px 15px;
    margin-bottom:15px;
    color:#66736d;
    font-size:13px;
}

.result-info b{
    color:#0f5132;
}

/* =========================
   LABEL KRITERIA
========================= */

.criteria-name{
    color:#0f5132;
    font-weight:700;
}

/* =========================
   MOBILE
========================= */

@media(max-width:700px){

    .wrap{
        margin:15px auto;
        padding:12px;
    }

    .head{
        display:block;
    }

    .head .btn{
        margin-top:15px;
    }

    .grid,
    .weights{
        display:block;
    }

    .metric,
    .weight{
        margin-bottom:10px;
    }

    th,
    td{
        padding:9px;
        font-size:12px;
    }

    .head h1{
        font-size:24px;
    }

}

</style>

</head>

<body>

<div class="wrap">

<!-- ======================================================
     HEADER
====================================================== -->

<div class="head">

    <div>

        <h1>
            Hasil Rekomendasi Nama Islami
        </h1>

        <p class="small">

            Jenis kelamin:

            <b>

                <?=(
                    $gender === 'L'
                    ? 'Laki-laki'
                    : 'Perempuan'
                )?>

            </b>

        </p>

    </div>

    <a
        class="btn green"
        href="input_preferensi.php"
    >
        Ubah Prioritas
    </a>

</div>


<!-- ======================================================
     INFORMASI HASIL
====================================================== -->

<div class="info">

    <b>Hasil AHP:</b>

    Sistem menghitung bobot berdasarkan pilihan Anda,
    kemudian menerapkannya pada nilai setiap alternatif.
    Lima nama dengan skor tertinggi ditampilkan sebagai
    rekomendasi utama sesuai dengan prioritas yang Anda pilih.

</div>


<!-- ======================================================
     BOBOT PRIORITAS
====================================================== -->

<div class="card">

    <h2>
        Bobot Prioritas Kriteria
    </h2>

    <div class="weights">

        <?php foreach(
            $ahp['bobot'] as $i => $b
        ): ?>

            <div class="weight">

                <b>

                    <?=isset($icons[$i])
                        ? $icons[$i]
                        : ''?>

                    <?=isset($kriteria[$i])
                        ? htmlspecialchars(
                            $kriteria[$i]['kode_kriteria']
                        )
                        : 'C'.($i + 1)?>

                    —

                    <?=isset($labels[$i])
                        ? htmlspecialchars(
                            $labels[$i]
                        )
                        : 'Kriteria '.($i + 1)?>

                </b>

                <strong
                    style="
                        display:block;
                        font-size:24px;
                        color:#0f5132;
                        margin-top:8px;
                    "
                >

                    <?=number_format(
                        $b * 100,
                        1
                    )?>%

                </strong>

                <div class="bar">

                    <span
                        style="
                            width:<?=min(
                                100,
                                $b * 100
                            )?>%
                        "
                    ></span>

                </div>

                <p
                    class="small"
                    style="
                        margin-top:8px
                    "
                >

                    <?php if($i == 0): ?>

                        Makna nama yang baik

                    <?php elseif($i == 1): ?>

                        Keunikan nama

                    <?php else: ?>

                        Keterkaitan Islam

                    <?php endif; ?>

                </p>

            </div>

        <?php endforeach; ?>

    </div>


    <p
        class="small"
        style="margin-top:15px"
    >

        CR =

        <?=number_format(
            $ahp['cr'],
            4
        )?>

        —

        <?php if($ahp['konsisten']): ?>

            <span
                style="
                    color:#0f5132;
                    font-weight:bold;
                "
            >

                ✓ Konsisten (≤ 0,10)

            </span>

        <?php else: ?>

            <span
                style="
                    color:#b45309;
                    font-weight:bold;
                "
            >

                ⚠ Belum konsisten (> 0,10)

            </span>

        <?php endif; ?>

    </p>

</div>


<!-- ======================================================
     REKOMENDASI 5 NAMA
====================================================== -->

<div
    class="card"
    id="bagianRekomendasi"
>

    <h2>
        🏆 5 Rekomendasi Nama Teratas
    </h2>

    <div class="result-info">

        Menampilkan
        <b>5 nama terbaik</b>
        berdasarkan hasil perhitungan AHP.

        <br>

        Nama diurutkan berdasarkan skor AHP
        dari yang tertinggi hingga terendah.

    </div>


    <!-- ==================================================
         TOP 5
    ================================================== -->

    <div
        class="tablewrap"
        id="tabelTop5"
    >

        <table>

            <thead>

                <tr>

                    <th>
                        Rank
                    </th>

                    <th>
                        Nama
                    </th>

                    <th>
                        Arti
                    </th>

                    <th>
                        Kategori Islam
                    </th>

                    <th>
                        Skor
                    </th>

                    <th>
                        Detail
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if(!$top5): ?>

                <tr>

                    <td colspan="6">

                        Data nilai alternatif
                        belum tersedia.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach(
                    $top5 as $i => $r
                ): ?>

                    <?php

                    $cat_class = 'cat-arab';

                    $cat_label =
                        $r['kat_keislaman']
                        ?? 'Arab';

                    if(
                        strpos(
                            $cat_label,
                            'Asmaul'
                        ) !== false
                    ){

                        $cat_class =
                            'cat-asmaul';

                    }

                    elseif(
                        strpos(
                            $cat_label,
                            'Sahabat'
                        ) !== false
                        ||
                        strpos(
                            $cat_label,
                            'Tokoh'
                        ) !== false
                    ){

                        $cat_class =
                            'cat-sahabat';

                    }

                    elseif(
                        strpos(
                            $cat_label,
                            'Lokal'
                        ) !== false
                    ){

                        $cat_class =
                            'cat-lokal';

                    }

                    ?>

                    <tr>

                        <td class="rank">

                            <?=(
                                $i + 1
                            )?>

                        </td>

                        <td>

                            <b
                                style="
                                    font-size:16px
                                "
                            >

                                <?=htmlspecialchars(
                                    $r['nama_islami']
                                )?>

                            </b>

                        </td>

                        <td>

                            <?=htmlspecialchars(
                                $r['arti_nama']
                            )?>

                        </td>

                        <td>

                            <span
                                class="
                                    category-badge
                                    <?=$cat_class?>
                                "
                            >

                                <?=htmlspecialchars(
                                    $cat_label
                                )?>

                            </span>

                        </td>

                        <td
                            class="score"
                            style="
                                font-size:18px
                            "
                        >

                            <?=number_format(
                                $r['score'],
                                4
                            )?>

                        </td>

                        <td class="breakdown">

                            C1:
                            <?=number_format(
                                $r['weighted'][0],
                                3
                            )?>

                            |

                            C2:
                            <?=number_format(
                                $r['weighted'][1],
                                3
                            )?>

                            |

                            C3:
                            <?=number_format(
                                $r['weighted'][2],
                                3
                            )?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ======================================================
     STATISTIK HASIL
====================================================== -->

<div
    class="card"
    id="statistikHasil"
>

    <h2>
        📊 Statistik Hasil
    </h2>

    <div class="grid">

        <div class="metric">

            <span class="small">
                Total Alternatif Dinilai
            </span>

            <b>
                <?=$total_alt?>
            </b>

        </div>


        <div class="metric">

            <span class="small">
                Skor Tertinggi
            </span>

            <b>

                <?=number_format(
                    $highest_score,
                    4
                )?>

            </b>

        </div>


        <div class="metric">

            <span class="small">
                Rata-rata Skor
            </span>

            <b>

                <?=number_format(
                    $avg_score,
                    4
                )?>

            </b>

        </div>

    </div>

</div>


<!-- ======================================================
     PENJELASAN HASIL
====================================================== -->

<div class="card">

    <h2>
        💡 Penjelasan Hasil
    </h2>

    <p
        class="small"
        style="
            font-size:13px;
            line-height:1.6
        "
    >

        Sistem menggunakan metode
        <b>
            AHP
            (Analytical Hierarchy Process)
        </b>
        untuk menghitung rekomendasi:

    </p>


    <ul
        class="small"
        style="
            font-size:13px;
            line-height:1.8
        "
    >

        <li>

            <b>Normalisasi:</b>

            Nilai setiap kriteria dibagi dengan
            skala maksimal (9) untuk mendapatkan
            nilai relatif 0–1.

        </li>


        <li>

            <b>Bobot:</b>

            Setiap kriteria dikalikan dengan
            bobot prioritas yang Anda pilih.

        </li>


        <li>

            <b>Skor Akhir:</b>

            Jumlah dari semua nilai terbobot
            menjadi skor total alternatif.

        </li>


        <li>

            <b>Ranking:</b>

            Alternatif diurutkan dari skor
            tertinggi ke skor terendah.

        </li>

    </ul>


    <p
        class="small"
        style="
            margin-top:15px;
            font-size:12px
        "
    >

        <b>Contoh:</b>

        Jika Anda memilih
        <i>
            <?=htmlspecialchars(
                $nama_c3
            )?>
        </i>
        sebagai prioritas tertinggi
        (misal 60%), maka nama dengan kategori
        <i>
            Asmaul Husna / Nabi
        </i>
        (nilai = 9) akan mendapat skor lebih tinggi
        dibandingkan nama dengan kategori
        <i>
            Nama Lokal Positif
        </i>
        (nilai = 5).

    </p>


    <a
        class="btn light"
        href="user_dashboard.php"
        style="margin-top:15px"
    >

        ← Kembali ke Beranda

    </a>

</div>

</div>

</body>

</html>