<?php

session_start();
include 'koneksi.php';

/*
|--------------------------------------------------------------------------
| DEBUG ERROR
|--------------------------------------------------------------------------
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| FUNGSI ESCAPE
|--------------------------------------------------------------------------
*/
function e($text)
{
    return htmlspecialchars(
        (string)$text,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL KRITERIA
|--------------------------------------------------------------------------
*/

$sql_kriteria = "
    SELECT
        id_kriteria,
        nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result_kriteria = mysqli_query(
    $koneksi,
    $sql_kriteria
);

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


/*
|--------------------------------------------------------------------------
| HARUS 3 KRITERIA
|--------------------------------------------------------------------------
*/

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
                <strong>
                    " . count($kriteria) . "
                </strong>
            </p>

            <a href='kelola_kriteria.php'>
                Kembali ke Kelola Kriteria
            </a>

        </div>
    ");

}


/*
|--------------------------------------------------------------------------
| ID KRITERIA
|--------------------------------------------------------------------------
*/

$id_c1 = (int)$kriteria[0]['id_kriteria'];
$id_c2 = (int)$kriteria[1]['id_kriteria'];
$id_c3 = (int)$kriteria[2]['id_kriteria'];


/*
|--------------------------------------------------------------------------
| NAMA KRITERIA
|--------------------------------------------------------------------------
*/

$nama_c1 = $kriteria[0]['nama_kriteria'];
$nama_c2 = $kriteria[1]['nama_kriteria'];
$nama_c3 = $kriteria[2]['nama_kriteria'];


/*
|--------------------------------------------------------------------------
| MATRIX AWAL 3 X 3
|--------------------------------------------------------------------------
*/

$matrix = [

    [1, 1, 1],

    [1, 1, 1],

    [1, 1, 1]

];


/*
|--------------------------------------------------------------------------
| AMBIL PREFERENSI
|--------------------------------------------------------------------------
|
| SESUAI DATABASE:
|
| kriteria_1
| kriteria_2
| nilai
|
*/

$sql_preferensi = "
    SELECT
        kriteria_1,
        kriteria_2,
        nilai
    FROM preferensi_kriteria
    ORDER BY id_preferensi ASC
";

$result_preferensi = mysqli_query(
    $koneksi,
    $sql_preferensi
);

if (!$result_preferensi) {

    die(
        "Gagal mengambil preferensi: "
        . mysqli_error($koneksi)
    );

}


/*
|--------------------------------------------------------------------------
| FUNGSI POSISI KRITERIA
|--------------------------------------------------------------------------
*/

function posisiKriteria(
    $id,
    $id_c1,
    $id_c2,
    $id_c3
) {

    if ($id == $id_c1) {

        return 0;

    }

    if ($id == $id_c2) {

        return 1;

    }

    if ($id == $id_c3) {

        return 2;

    }

    return -1;

}


/*
|--------------------------------------------------------------------------
| MASUKKAN PREFERENSI KE MATRIX
|--------------------------------------------------------------------------
*/

while (
    $row = mysqli_fetch_assoc(
        $result_preferensi
    )
) {

    $k1 = (int)$row['kriteria_1'];

    $k2 = (int)$row['kriteria_2'];

    $nilai = (float)$row['nilai'];


    /*
    | Nilai harus lebih dari 0
    */
    if ($nilai <= 0) {

        continue;

    }


    $baris = posisiKriteria(
        $k1,
        $id_c1,
        $id_c2,
        $id_c3
    );


    $kolom = posisiKriteria(
        $k2,
        $id_c1,
        $id_c2,
        $id_c3
    );


    /*
    | Jika ID tidak ditemukan
    */
    if (
        $baris == -1 ||
        $kolom == -1
    ) {

        continue;

    }


    /*
    | Nilai perbandingan
    */
    $matrix[$baris][$kolom] = $nilai;


    /*
    | Nilai kebalikan
    */
    $matrix[$kolom][$baris] =
        1 / $nilai;

}


/*
|--------------------------------------------------------------------------
| DIAGONAL UTAMA
|--------------------------------------------------------------------------
*/

$matrix[0][0] = 1;
$matrix[1][1] = 1;
$matrix[2][2] = 1;


/*
|--------------------------------------------------------------------------
| JUMLAH KOLOM
|--------------------------------------------------------------------------
*/

$jumlah_kolom = [

    0,
    0,
    0

];


for ($j = 0; $j < 3; $j++) {

    for ($i = 0; $i < 3; $i++) {

        $jumlah_kolom[$j] +=
            $matrix[$i][$j];

    }

}


/*
|--------------------------------------------------------------------------
| NORMALISASI
|--------------------------------------------------------------------------
*/

$normalisasi = [

    [0, 0, 0],

    [0, 0, 0],

    [0, 0, 0]

];


for ($i = 0; $i < 3; $i++) {

    for ($j = 0; $j < 3; $j++) {

        if ($jumlah_kolom[$j] > 0) {

            $normalisasi[$i][$j] =
                $matrix[$i][$j]
                /
                $jumlah_kolom[$j];

        }

    }

}


/*
|--------------------------------------------------------------------------
| BOBOT PRIORITAS
|--------------------------------------------------------------------------
*/

$bobot = [

    0,
    0,
    0

];


for ($i = 0; $i < 3; $i++) {

    $bobot[$i] =

        (
            $normalisasi[$i][0]
            +
            $normalisasi[$i][1]
            +
            $normalisasi[$i][2]
        )
        /
        3;

}


/*
|--------------------------------------------------------------------------
| CEK BOBOT
|--------------------------------------------------------------------------
*/

$total_bobot =
    $bobot[0]
    +
    $bobot[1]
    +
    $bobot[2];


/*
|--------------------------------------------------------------------------
| WEIGHTED SUM
|--------------------------------------------------------------------------
*/

$weighted_sum = [

    0,
    0,
    0

];


for ($i = 0; $i < 3; $i++) {

    for ($j = 0; $j < 3; $j++) {

        $weighted_sum[$i] +=

            $matrix[$i][$j]
            *
            $bobot[$j];

    }

}


/*
|--------------------------------------------------------------------------
| CONSISTENCY VECTOR
|--------------------------------------------------------------------------
*/

$consistency_vector = [

    0,
    0,
    0

];


for ($i = 0; $i < 3; $i++) {

    if ($bobot[$i] > 0) {

        $consistency_vector[$i] =

            $weighted_sum[$i]
            /
            $bobot[$i];

    }

}


/*
|--------------------------------------------------------------------------
| LAMBDA MAX
|--------------------------------------------------------------------------
*/

$lambda_max =

    (
        $consistency_vector[0]
        +
        $consistency_vector[1]
        +
        $consistency_vector[2]
    )
    /
    3;


/*
|--------------------------------------------------------------------------
| CI
|--------------------------------------------------------------------------
*/

$n = 3;

$CI =

    (
        $lambda_max
        -
        $n
    )
    /
    (
        $n - 1
    );


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

$CR =

    $CI
    /
    $RI;


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

if ($CR <= 0.10) {

    $status = "KONSISTEN";

} else {

    $status = "TIDAK KONSISTEN";

}


/*
|--------------------------------------------------------------------------
| AMBIL DATA ALTERNATIF + NILAI
|--------------------------------------------------------------------------
*/

$sql_alternatif = "
    SELECT
        a.id_alternatif,
        a.nama_islami,
        a.arti_nama,
        a.jenis_kelamin,
        na.id_kriteria,
        na.nilai
    FROM alternatif a
    LEFT JOIN nilai_alternatif na
        ON a.id_alternatif =
           na.id_alternatif
    ORDER BY
        a.id_alternatif ASC
";


$result_alternatif = mysqli_query(
    $koneksi,
    $sql_alternatif
);


if (!$result_alternatif) {

    die(
        "Gagal mengambil data alternatif: "
        . mysqli_error($koneksi)
    );

}


/*
|--------------------------------------------------------------------------
| SUSUN DATA ALTERNATIF
|--------------------------------------------------------------------------
*/

$alternatif = [];


while (
    $row = mysqli_fetch_assoc(
        $result_alternatif
    )
) {

    $id = (int)$row['id_alternatif'];


    /*
    | Buat data alternatif
    */
    if (!isset($alternatif[$id])) {

        $alternatif[$id] = [

            'nama_islami' =>
                $row['nama_islami'],

            'arti_nama' =>
                $row['arti_nama'],

            'jenis_kelamin' =>
                $row['jenis_kelamin'],

            'nilai' => [

                $id_c1 => 0,

                $id_c2 => 0,

                $id_c3 => 0

            ]

        ];

    }


    /*
    | Masukkan nilai kriteria
    */
    if (
        $row['id_kriteria'] !== null
        &&
        $row['nilai'] !== null
    ) {

        $id_kriteria =
            (int)$row['id_kriteria'];

        $alternatif[$id]['nilai']
            [$id_kriteria] =
                (float)$row['nilai'];

    }

}


/*
|--------------------------------------------------------------------------
| NILAI MAKSIMUM
|--------------------------------------------------------------------------
*/

$max_c1 = 0;
$max_c2 = 0;
$max_c3 = 0;


foreach (
    $alternatif
    as $data
) {

    $max_c1 = max(

        $max_c1,

        $data['nilai'][$id_c1]

    );


    $max_c2 = max(

        $max_c2,

        $data['nilai'][$id_c2]

    );


    $max_c3 = max(

        $max_c3,

        $data['nilai'][$id_c3]

    );

}


/*
|--------------------------------------------------------------------------
| NORMALISASI ALTERNATIF + NILAI AKHIR
|--------------------------------------------------------------------------
*/

foreach (
    $alternatif
    as $id => &$data
) {

    /*
    | C1
    */
    $normal_c1 =

        $max_c1 > 0

        ?

        $data['nilai'][$id_c1]
        /
        $max_c1

        :

        0;


    /*
    | C2
    */
    $normal_c2 =

        $max_c2 > 0

        ?

        $data['nilai'][$id_c2]
        /
        $max_c2

        :

        0;


    /*
    | C3
    */
    $normal_c3 =

        $max_c3 > 0

        ?

        $data['nilai'][$id_c3]
        /
        $max_c3

        :

        0;


    /*
    | Simpan normalisasi
    */
    $data['normalisasi'] = [

        $id_c1 =>
            $normal_c1,

        $id_c2 =>
            $normal_c2,

        $id_c3 =>
            $normal_c3

    ];


    /*
    | NILAI AKHIR
    */
    $data['nilai_akhir'] =

        (
            $normal_c1
            *
            $bobot[0]
        )

        +

        (
            $normal_c2
            *
            $bobot[1]
        )

        +

        (
            $normal_c3
            *
            $bobot[2]
        );

}


unset($data);


/*
|--------------------------------------------------------------------------
| URUTKAN RANKING
|--------------------------------------------------------------------------
*/

uasort(

    $alternatif,

    function ($a, $b) {

        if (
            $a['nilai_akhir']
            ==
            $b['nilai_akhir']
        ) {

            return 0;

        }

        return

            (
                $a['nilai_akhir']
                <
                $b['nilai_akhir']
            )

            ? 1
            : -1;

    }

);

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
        Perhitungan AHP
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-4">


    <!-- HEADER -->

    <div
        class="d-flex justify-content-between
        align-items-center mb-4"
    >

        <div>

            <h3>
                Perhitungan AHP
            </h3>

            <p class="text-muted mb-0">

                Perhitungan bobot kriteria
                dan ranking nama Islami.

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
         1. MATRIKS PERBANDINGAN
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div
            class="card-header bg-primary
            text-white"
        >

            <h5 class="mb-0">

                1. Matriks Perbandingan Kriteria

            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered
                    text-center align-middle"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>Kriteria</th>

                            <th>
                                <?= e($nama_c1); ?>
                            </th>

                            <th>
                                <?= e($nama_c2); ?>
                            </th>

                            <th>
                                <?= e($nama_c3); ?>
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <th>
                                <?= e($nama_c1); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $matrix[0][0],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[0][1],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[0][2],
                                    3
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th>
                                <?= e($nama_c2); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $matrix[1][0],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[1][1],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[1][2],
                                    3
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th>
                                <?= e($nama_c3); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $matrix[2][0],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[2][1],
                                    3
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $matrix[2][2],
                                    3
                                ); ?>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- ======================================================
         2. NORMALISASI + BOBOT
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-info">

            <h5 class="mb-0">

                2. Normalisasi Matriks dan Bobot

            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered
                    text-center align-middle"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>Kriteria</th>

                            <th>
                                <?= e($nama_c1); ?>
                            </th>

                            <th>
                                <?= e($nama_c2); ?>
                            </th>

                            <th>
                                <?= e($nama_c3); ?>
                            </th>

                            <th>Bobot</th>

                            <th>Persentase</th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <th>
                                <?= e($nama_c1); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $normalisasi[0][0],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[0][1],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[0][2],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= number_format(
                                        $bobot[0],
                                        4
                                    ); ?>
                                </strong>
                            </td>

                            <td>
                                <?= number_format(
                                    $bobot[0] * 100,
                                    2
                                ); ?>%
                            </td>

                        </tr>


                        <tr>

                            <th>
                                <?= e($nama_c2); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $normalisasi[1][0],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[1][1],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[1][2],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= number_format(
                                        $bobot[1],
                                        4
                                    ); ?>
                                </strong>
                            </td>

                            <td>
                                <?= number_format(
                                    $bobot[1] * 100,
                                    2
                                ); ?>%
                            </td>

                        </tr>


                        <tr>

                            <th>
                                <?= e($nama_c3); ?>
                            </th>

                            <td>
                                <?= number_format(
                                    $normalisasi[2][0],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[2][1],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $normalisasi[2][2],
                                    4
                                ); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= number_format(
                                        $bobot[2],
                                        4
                                    ); ?>
                                </strong>
                            </td>

                            <td>
                                <?= number_format(
                                    $bobot[2] * 100,
                                    2
                                ); ?>%
                            </td>

                        </tr>


                        <tr class="table-light">

                            <th>
                                TOTAL
                            </th>

                            <td colspan="4"></td>

                            <td>

                                <strong>
                                    <?= number_format(
                                        $total_bobot * 100,
                                        2
                                    ); ?>%
                                </strong>

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- ======================================================
         3. UJI KONSISTENSI
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div
            class="card-header bg-warning"
        >

            <h5 class="mb-0">

                3. Uji Konsistensi AHP

            </h5>

        </div>


        <div class="card-body">


            <table class="table table-bordered">

                <tr>

                    <th width="300">
                        Lambda Max (λ Max)
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

                        <strong>

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
                                class="badge bg-success"
                            >
                                KONSISTEN
                            </span>

                        <?php else: ?>

                            <span
                                class="badge bg-danger"
                            >
                                TIDAK KONSISTEN
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            </table>


            <?php if ($CR <= 0.10): ?>

                <div class="alert alert-success">

                    Perbandingan kriteria
                    <strong>konsisten</strong>
                    karena CR ≤ 0,10.

                </div>

            <?php else: ?>

                <div class="alert alert-danger">

                    Perbandingan kriteria
                    <strong>tidak konsisten</strong>.
                    Silakan perbaiki preferensi kriteria.

                </div>

            <?php endif; ?>


        </div>

    </div>


    <!-- ======================================================
         4. RANKING
    ======================================================= -->

    <div class="card shadow-sm mb-4">

        <div
            class="card-header bg-success
            text-white"
        >

            <h5 class="mb-0">

                4. Hasil Ranking Nama Islami

            </h5>

        </div>


        <div class="card-body">


            <?php if (empty($alternatif)): ?>

                <div class="alert alert-warning">

                    Belum ada data alternatif.

                </div>

            <?php else: ?>


                <div class="table-responsive">

                    <table
                        class="table table-bordered
                        table-striped
                        text-center
                        align-middle"
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
                                    Nilai
                                    <?= e($nama_c1); ?>
                                </th>

                                <th>
                                    Nilai
                                    <?= e($nama_c2); ?>
                                </th>

                                <th>
                                    Nilai
                                    <?= e($nama_c3); ?>
                                </th>

                                <th>
                                    Nilai Akhir
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $ranking = 1;

                        foreach (
                            $alternatif
                            as $data
                        ):

                        ?>


                            <tr>


                                <td>

                                    <strong>
                                        <?= $ranking++; ?>
                                    </strong>

                                </td>


                                <td>

                                    <strong>

                                        <?= e(
                                            $data[
                                                'nama_islami'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= e(
                                        $data[
                                            'arti_nama'
                                        ]
                                    ); ?>

                                </td>


                                <td>

                                    <?= e(
                                        $data[
                                            'jenis_kelamin'
                                        ]
                                    ); ?>

                                </td>


                                <td>

                                    <?= number_format(
                                        $data['nilai']
                                            [$id_c1],
                                        2
                                    ); ?>

                                </td>


                                <td>

                                    <?= number_format(
                                        $data['nilai']
                                            [$id_c2],
                                        2
                                    ); ?>

                                </td>


                                <td>

                                    <?= number_format(
                                        $data['nilai']
                                            [$id_c3],
                                        2
                                    ); ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= number_format(
                                            $data[
                                                'nilai_akhir'
                                            ],
                                            4
                                        ); ?>

                                    </strong>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>


    <a
        href="admin_dashboard.php"
        class="btn btn-secondary"
    >

        Kembali ke Dashboard

    </a>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>