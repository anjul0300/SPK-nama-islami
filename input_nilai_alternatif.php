<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once 'koneksi.php';


// ============================================================
// CEK LOGIN ADMIN
// ============================================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// ============================================================
// AMBIL ID ALTERNATIF
// ============================================================

$id_alternatif = isset($_GET['id_alternatif'])
    ? (int) $_GET['id_alternatif']
    : 0;


// ============================================================
// JIKA BELUM MEMILIH NAMA
// TAMPILKAN DAFTAR NAMA
// ============================================================

if ($id_alternatif <= 0) {

    $query_daftar = "
        SELECT
            id_alternatif,
            nama_islami,
            arti_nama,
            jenis_kelamin,
            tingkat_keunikan,
            kat_keislaman
        FROM alternatif
        ORDER BY id_alternatif ASC
    ";

    $result_daftar = mysqli_query($koneksi, $query_daftar);

    if (!$result_daftar) {
        die(
            "Gagal mengambil daftar nama: "
            . mysqli_error($koneksi)
        );
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

        <title>Input Nilai Alternatif</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <link rel="stylesheet" href="includes/admin.css">
</head>

    <?php include __DIR__ . "/includes/admin_header.php"; ?>

    <div class="container py-4">

        <!-- HEADER -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3 class="fw-bold">
                    Input Nilai Alternatif
                </h3>

                <p class="text-muted mb-0">
                    Pilih nama Islami yang ingin diberikan nilai AHP.
                </p>

            </div>

            <a
                href="admin_dashboard.php"
                class="btn btn-secondary"
            >
                Dashboard
            </a>

        </div>


        <!-- INFORMASI KRITERIA -->

        <div class="alert alert-info">

            <strong>3 Kriteria AHP:</strong>

            <ol class="mb-0 mt-2">

                <li>
                    C1 - Arti Nama
                </li>

                <li>
                    C2 - Tingkat Keunikan
                </li>

                <li>
                    C3 - Nilai Keislaman
                </li>

            </ol>

        </div>


        <!-- DAFTAR NAMA -->

        <div class="card shadow-sm">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    Daftar Nama Islami
                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th width="60">
                                    No
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
                                    Tingkat Keunikan
                                </th>

                                <th>
                                    Nilai Keislaman
                                </th>

                                <th width="130">
                                    Aksi
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $no = 1;

                        while ($nama = mysqli_fetch_assoc($result_daftar)):

                        ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $nama['nama_islami']
                                        ); ?>
                                    </strong>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $nama['arti_nama']
                                    ); ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $nama['jenis_kelamin']
                                    ); ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $nama['tingkat_keunikan']
                                    ); ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $nama['kat_keislaman']
                                    ); ?>

                                </td>

                                <td>

                                    <a
                                        href="input_nilai_alternatif.php?id_alternatif=<?= (int)$nama['id_alternatif']; ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Input Nilai
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <?php include __DIR__ . "/includes/admin_footer.php"; ?>

    </html>

    <?php

    exit();
}


// ============================================================
// AMBIL DATA ALTERNATIF
// ============================================================

$query_alternatif = "
    SELECT
        id_alternatif,
        nama_islami,
        arti_nama,
        jenis_kelamin,
        tingkat_keunikan,
        kat_keislaman
    FROM alternatif
    WHERE id_alternatif = $id_alternatif
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


$alternatif = mysqli_fetch_assoc(
    $result_alternatif
);


if (!$alternatif) {

    die("

        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
            text-align:center;
        '>

            <h3>
                Data Nama Tidak Ditemukan
            </h3>

            <p>
                ID Alternatif:
                <strong>$id_alternatif</strong>
                tidak ditemukan.
            </p>

            <a href='input_nilai_alternatif.php'>
                Kembali ke Daftar Nama
            </a>

        </div>

    ");

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

$result_kriteria = mysqli_query(
    $koneksi,
    $query_kriteria
);


if (!$result_kriteria) {

    die(
        "Gagal mengambil kriteria: "
        . mysqli_error($koneksi)
    );

}


$kriteria = [];

while (
    $row = mysqli_fetch_assoc($result_kriteria)
) {

    $kriteria[] = $row;

}


// ============================================================
// HARUS TEPAT 3 KRITERIA
// ============================================================

if (count($kriteria) != 3) {

    die("

        <div style='
            font-family:Arial;
            max-width:700px;
            margin:80px auto;
            padding:30px;
        '>

            <h3>
                Kriteria Belum Sesuai
            </h3>

            <p>
                Sistem membutuhkan tepat
                <strong>3 kriteria AHP.</strong>
            </p>

            <p>
                Jumlah kriteria saat ini:
                <strong>" . count($kriteria) . "</strong>
            </p>

            <p>
                Kriteria yang digunakan:
            </p>

            <ol>
                <li>Arti Nama</li>
                <li>Tingkat Keunikan</li>
                <li>Nilai Keislaman</li>
            </ol>

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
// NILAI DEFAULT
// ============================================================

$nilai_c1 = '';
$kategori_c1 = '';


// ============================================================
// HITUNG C2 - TINGKAT KEUNIKAN
//
// Sangat Unik = 9
// Sedang      = 5
// Umum        = 1
// ============================================================

switch (trim($alternatif['tingkat_keunikan'])) {

    case 'Sangat Unik':
        $nilai_c2 = 9;
        break;

    case 'Sedang':
        $nilai_c2 = 5;
        break;

    case 'Umum':
        $nilai_c2 = 1;
        break;

    default:
        $nilai_c2 = 1;
        break;
}


// ============================================================
// HITUNG C3 - NILAI KEISLAMAN
//
// Asmaul Husna / Nabi       = 9
// Sahabat / Tokoh Mulia     = 8
// Bahasa Arab Bermakna Baik = 7
// Nama Lokal Positif        = 5
// ============================================================

switch (trim($alternatif['kat_keislaman'])) {

    case 'Asmaul Husna / Nabi':
        $nilai_c3 = 9;
        break;

    case 'Sahabat / Tokoh Mulia':
        $nilai_c3 = 8;
        break;

    case 'Bahasa Arab Bermakna Baik':
        $nilai_c3 = 7;
        break;

    case 'Nama Lokal Positif':
        $nilai_c3 = 5;
        break;

    default:
        $nilai_c3 = 3;
        break;
}


// ============================================================
// AMBIL NILAI C1 YANG SUDAH TERSIMPAN
// ============================================================

$query_nilai_lama = "
    SELECT
        id_kriteria,
        nilai
    FROM nilai_alternatif
    WHERE id_alternatif = $id_alternatif
";

$result_nilai_lama = mysqli_query(
    $koneksi,
    $query_nilai_lama
);


if ($result_nilai_lama) {

    while (
        $row = mysqli_fetch_assoc($result_nilai_lama)
    ) {

        if (
            (int)$row['id_kriteria'] === $c1
        ) {

            $nilai_c1 = $row['nilai'];

        }

    }

}


// ============================================================
// PESAN ERROR / SUKSES
// ============================================================

$error = '';


// ============================================================
// SIMPAN NILAI
// ============================================================

if (isset($_POST['simpan'])) {

    $kategori_c1 = isset($_POST['kategori_c1']) ? trim($_POST['kategori_c1']) : '';
    $peta_c1 = [
        'sangat_baik' => 9,
        'baik' => 7,
        'cukup' => 5,
        'kurang' => 3,
        'tidak_sesuai' => 1
    ];
    $nilai_c1 = $peta_c1[$kategori_c1] ?? 0;

    if ($nilai_c1 < 1 || $nilai_c1 > 9) {
        $error = "Silakan pilih kategori penilaian C1 - Arti Nama.";
    } else {


        // ====================================================
        // MULAI TRANSAKSI
        // ====================================================

        mysqli_begin_transaction($koneksi);


        try {


            // =================================================
            // HAPUS NILAI LAMA UNTUK ALTERNATIF INI
            // =================================================

            $hapus = mysqli_query(
                $koneksi,
                "
                DELETE FROM nilai_alternatif
                WHERE id_alternatif = $id_alternatif
                "
            );


            if (!$hapus) {

                throw new Exception(
                    "Gagal menghapus nilai lama: "
                    . mysqli_error($koneksi)
                );

            }


            // =================================================
            // SIMPAN C1 - ARTI NAMA
            // =================================================

            $simpan_c1 = mysqli_query(
                $koneksi,
                "
                INSERT INTO nilai_alternatif
                (
                    id_alternatif,
                    id_kriteria,
                    nilai
                )
                VALUES
                (
                    $id_alternatif,
                    $c1,
                    $nilai_c1
                )
                "
            );


            if (!$simpan_c1) {

                throw new Exception(
                    "Gagal menyimpan C1: "
                    . mysqli_error($koneksi)
                );

            }


            // =================================================
            // SIMPAN C2 - TINGKAT KEUNIKAN
            // =================================================

            $simpan_c2 = mysqli_query(
                $koneksi,
                "
                INSERT INTO nilai_alternatif
                (
                    id_alternatif,
                    id_kriteria,
                    nilai
                )
                VALUES
                (
                    $id_alternatif,
                    $c2,
                    $nilai_c2
                )
                "
            );


            if (!$simpan_c2) {

                throw new Exception(
                    "Gagal menyimpan C2: "
                    . mysqli_error($koneksi)
                );

            }


            // =================================================
            // SIMPAN C3 - NILAI KEISLAMAN
            // =================================================

            $simpan_c3 = mysqli_query(
                $koneksi,
                "
                INSERT INTO nilai_alternatif
                (
                    id_alternatif,
                    id_kriteria,
                    nilai
                )
                VALUES
                (
                    $id_alternatif,
                    $c3,
                    $nilai_c3
                )
                "
            );


            if (!$simpan_c3) {

                throw new Exception(
                    "Gagal menyimpan C3: "
                    . mysqli_error($koneksi)
                );

            }


            // =================================================
            // JIKA SEMUA BERHASIL
            // =================================================

            mysqli_commit($koneksi);


            header(
                "Location: input_nilai_alternatif.php"
            );

            exit();


        } catch (Exception $e) {


            // =================================================
            // BATALKAN SEMUA PERUBAHAN
            // =================================================

            mysqli_rollback($koneksi);


            $error = $e->getMessage();

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

    <title>
        Input Nilai Alternatif
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-4">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">
                Input Nilai Alternatif
            </h3>

            <p class="text-muted mb-0">
                Masukkan nilai berdasarkan 3 kriteria AHP.
            </p>

        </div>


        <a
            href="input_nilai_alternatif.php"
            class="btn btn-secondary"
        >
            Daftar Nama
        </a>

    </div>


    <!-- =====================================================
         INFORMASI SISTEM
    ====================================================== -->

    <div class="alert alert-primary">

        <strong>
            Kriteria AHP yang digunakan:
        </strong>

        <ol class="mb-0 mt-2">

            <li>
                <strong>C1 - Arti Nama</strong>
                → dinilai oleh admin
            </li>

            <li>
                <strong>C2 - Tingkat Keunikan</strong>
                → dihitung otomatis
            </li>

            <li>
                <strong>C3 - Nilai Keislaman</strong>
                → dihitung otomatis
            </li>

        </ol>

    </div>


    <!-- =====================================================
         DATA NAMA
    ====================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Data Nama Islami
            </h5>

        </div>


        <div class="card-body">

            <table class="table table-bordered mb-0">

                <tr>

                    <th width="220">
                        Nama Islami
                    </th>

                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $alternatif['nama_islami']
                            ); ?>
                        </strong>

                    </td>

                </tr>


                <tr>

                    <th>
                        Arti Nama
                    </th>

                    <td>

                        <?= htmlspecialchars(
                            $alternatif['arti_nama']
                        ); ?>

                    </td>

                </tr>


                <tr>

                    <th>
                        Jenis Kelamin
                    </th>

                    <td>

                        <?= htmlspecialchars(
                            $alternatif['jenis_kelamin']
                        ); ?>

                        <small class="text-muted">
                            (informasi, bukan kriteria AHP)
                        </small>

                    </td>

                </tr>


                <tr>

                    <th>
                        Tingkat Keunikan
                    </th>

                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $alternatif['tingkat_keunikan']
                            ); ?>
                        </strong>

                    </td>

                </tr>


                <tr>

                    <th>
                        Nilai Keislaman
                    </th>

                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $alternatif['kat_keislaman']
                            ); ?>
                        </strong>

                    </td>

                </tr>

            </table>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <?php if ($error != ''): ?>

        <div class="alert alert-danger">

            <strong>
                Gagal!
            </strong>

            <br>

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <div class="alert alert-primary shadow-sm mb-4">
        <strong>Dasar penilaian alternatif:</strong>
        arti nama dan informasi jenis kelamin diambil dari data sumber nama yang dimasukkan admin.
        Nilai 1–9 di bawah <strong>bukan Skala Saaty</strong>; nilai tersebut adalah skor alternatif yang dibuat dari rubrik penelitian.
        Dengan cara ini angka tidak diberikan secara acak.
    </div>

    <!-- =====================================================
         ATURAN PENILAIAN
    ====================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-info text-white">

            <h5 class="mb-0">
                Aturan Penilaian
            </h5>

        </div>


        <div class="card-body">

            <div class="row">


                <!-- C1 -->

                <div class="col-md-4">

                    <h6 class="fw-bold">
                        C1 - Arti Nama
                    </h6>

                    <ul>

                        <li>9 = Sangat baik</li>
                        <li>7 = Baik</li>
                        <li>5 = Cukup</li>
                        <li>3 = Kurang</li>
                        <li>1 = Tidak sesuai</li>

                    </ul>

                </div>


                <!-- C2 -->

                <div class="col-md-4">

                    <h6 class="fw-bold">
                        C2 - Tingkat Keunikan
                    </h6>

                    <ul>

                        <li>
                            Sangat Unik = <strong>9</strong>
                        </li>

                        <li>
                            Sedang = <strong>5</strong>
                        </li>

                        <li>
                            Umum = <strong>1</strong>
                        </li>

                    </ul>

                </div>


                <!-- C3 -->

                <div class="col-md-4">

                    <h6 class="fw-bold">
                        C3 - Nilai Keislaman
                    </h6>

                    <ul>

                        <li>
                            Asmaul Husna / Nabi = <strong>9</strong>
                        </li>

                        <li>
                            Sahabat / Tokoh Mulia = <strong>8</strong>
                        </li>

                        <li>
                            Bahasa Arab Bermakna Baik = <strong>7</strong>
                        </li>

                        <li>
                            Nama Lokal Positif = <strong>5</strong>
                        </li>

                    </ul>

                </div>


            </div>

            <div class="mt-3 small text-muted">
                <strong>Catatan sumber:</strong> data nama/arti harus berasal dari buku atau referensi yang dicantumkan pada penelitian. Rubrik hanya menerjemahkan karakteristik data tersebut menjadi skor alternatif secara konsisten.
            </div>

        </div>

    </div>


    <!-- =====================================================
         FORM NILAI
    ====================================================== -->

    <form method="POST">


        <div class="card shadow-sm">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    Nilai Kriteria AHP
                </h5>

            </div>


            <div class="card-body">


                <!-- =================================================
                     C1
                ================================================== -->

                <div class="mb-4">

                    <label class="form-label fw-bold">

                        C1 -
                        <?= htmlspecialchars(
                            $kriteria[0]['nama_kriteria']
                        ); ?>

                    </label>


                    <select name="kategori_c1" class="form-select" required>
                        <option value="">-- Pilih kategori berdasarkan arti nama --</option>
                        <option value="sangat_baik" <?= $kategori_c1 === 'sangat_baik' ? 'selected' : ''; ?>>Sangat baik — makna sangat positif dan kuat</option>
                        <option value="baik" <?= $kategori_c1 === 'baik' ? 'selected' : ''; ?>>Baik — makna positif dan jelas</option>
                        <option value="cukup" <?= $kategori_c1 === 'cukup' ? 'selected' : ''; ?>>Cukup — makna positif tetapi umum</option>
                        <option value="kurang" <?= $kategori_c1 === 'kurang' ? 'selected' : ''; ?>>Kurang — makna positifnya terbatas</option>
                        <option value="tidak_sesuai" <?= $kategori_c1 === 'tidak_sesuai' ? 'selected' : ''; ?>>Tidak sesuai — makna kurang baik/tidak sesuai</option>
                    </select>

                    <div class="form-text">
                        Admin tidak perlu menebak angka. Sistem mengubah kategori menjadi skor internal 9, 7, 5, 3, atau 1 sesuai rubrik penelitian.
                    </div>

                </div>


                <!-- =================================================
                     C2
                ================================================== -->

                <div class="mb-4">

                    <label class="form-label fw-bold">

                        C2 -
                        <?= htmlspecialchars(
                            $kriteria[1]['nama_kriteria']
                        ); ?>

                    </label>


                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($nilai_c2); ?>"
                        readonly
                    >


                    <div class="form-text text-success">

                        ✓ Nilai ditentukan otomatis berdasarkan
                        tingkat keunikan nama.

                    </div>

                </div>


                <!-- =================================================
                     C3
                ================================================== -->

                <div class="mb-4">

                    <label class="form-label fw-bold">

                        C3 -
                        <?= htmlspecialchars(
                            $kriteria[2]['nama_kriteria']
                        ); ?>

                    </label>


                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($nilai_c3); ?>"
                        readonly
                    >


                    <div class="form-text text-success">

                        ✓ Nilai ditentukan otomatis berdasarkan
                        kategori nilai keislaman.

                    </div>

                </div>


                <!-- =================================================
                     RINGKASAN
                ================================================== -->

                <div class="alert alert-warning">

                    <strong>
                        Ringkasan Nilai:
                    </strong>

                    <hr>

                    C1 - Arti Nama =
                    <strong>

                        <?= $nilai_c1 !== ''
                            ? htmlspecialchars($nilai_c1)
                            : '-'; ?>

                    </strong>

                    <br>

                    C2 - Tingkat Keunikan =
                    <strong>
                        <?= htmlspecialchars($nilai_c2); ?>
                    </strong>

                    <br>

                    C3 - Nilai Keislaman =
                    <strong>
                        <?= htmlspecialchars($nilai_c3); ?>
                    </strong>

                </div>


                <!-- =================================================
                     TOMBOL
                ================================================== -->

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-success"
                >

                    Simpan Nilai

                </button>


                <a
                    href="input_nilai_alternatif.php"
                    class="btn btn-secondary"
                >

                    Kembali

                </a>


            </div>

        </div>


    </form>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>
