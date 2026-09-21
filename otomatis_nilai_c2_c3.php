<?php

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
// CARI ID KRITERIA BERDASARKAN NAMA
// ============================================================

$query_kriteria = "
    SELECT id_kriteria, nama_kriteria
    FROM kriteria
    WHERE nama_kriteria IN ('Arti Nama', 'Tingkat Keunikan', 'Nilai Keislaman')
";

$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if (!$result_kriteria) {
    die("Gagal mengambil data kriteria: " . mysqli_error($koneksi));
}


$id_c1 = null;
$id_c2 = null;
$id_c3 = null;

while ($row = mysqli_fetch_assoc($result_kriteria)) {

    $nama = strtolower(trim($row['nama_kriteria']));

    if ($nama === 'arti nama') {
        $id_c1 = (int) $row['id_kriteria'];
    }

    elseif ($nama === 'tingkat keunikan') {
        $id_c2 = (int) $row['id_kriteria'];
    }

    elseif ($nama === 'nilai keislaman') {
        $id_c3 = (int) $row['id_kriteria'];
    }
}


// ============================================================
// CEK KRITERIA
// ============================================================

if ($id_c1 === null || $id_c2 === null || $id_c3 === null) {

    die("
        <div style='
            font-family: Arial;
            max-width: 700px;
            margin: 80px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
        '>

            <h3>Kriteria belum lengkap</h3>

            <p>
                Sistem membutuhkan 3 kriteria berikut:
            </p>

            <ul>
                <li>Arti Nama</li>
                <li>Tingkat Keunikan</li>
                <li>Nilai Keislaman</li>
            </ul>

            <p>
                Pastikan ketiga nama kriteria tersebut
                tersedia di tabel <strong>kriteria</strong>.
            </p>

            <a href='kelola_kriteria.php'>
                Kembali ke Kelola Kriteria
            </a>

        </div>
    ");

}


// ============================================================
// AMBIL SEMUA DATA ALTERNATIF
// ============================================================

$query_alternatif = "
    SELECT
        id_alternatif,
        nama_islami,
        tingkat_keunikan,
        kat_keislaman
    FROM alternatif
    ORDER BY id_alternatif ASC
";

$result_alternatif = mysqli_query($koneksi, $query_alternatif);

if (!$result_alternatif) {
    die(
        "Gagal mengambil data alternatif: "
        . mysqli_error($koneksi)
    );
}


// ============================================================
// FUNGSI SIMPAN NILAI
// ============================================================

function simpanNilai(
    $koneksi,
    $id_alternatif,
    $id_kriteria,
    $nilai
) {

    // --------------------------------------------------------
    // CEK APAKAH DATA SUDAH ADA
    // --------------------------------------------------------

    $cek = mysqli_prepare(
        $koneksi,
        "
        SELECT id_nilai
        FROM nilai_alternatif
        WHERE id_alternatif = ?
        AND id_kriteria = ?
        LIMIT 1
        "
    );

    mysqli_stmt_bind_param(
        $cek,
        "ii",
        $id_alternatif,
        $id_kriteria
    );

    mysqli_stmt_execute($cek);

    $hasil_cek = mysqli_stmt_get_result($cek);

    // --------------------------------------------------------
    // JIKA SUDAH ADA → UPDATE
    // --------------------------------------------------------

    if (mysqli_num_rows($hasil_cek) > 0) {

        $data = mysqli_fetch_assoc($hasil_cek);

        $id_nilai = (int) $data['id_nilai'];

        mysqli_stmt_close($cek);

        $update = mysqli_prepare(
            $koneksi,
            "
            UPDATE nilai_alternatif
            SET nilai = ?
            WHERE id_nilai = ?
            "
        );

        mysqli_stmt_bind_param(
            $update,
            "di",
            $nilai,
            $id_nilai
        );

        $berhasil = mysqli_stmt_execute($update);

        mysqli_stmt_close($update);

        return $berhasil;
    }


    // --------------------------------------------------------
    // JIKA BELUM ADA → INSERT
    // --------------------------------------------------------

    mysqli_stmt_close($cek);

    $insert = mysqli_prepare(
        $koneksi,
        "
        INSERT INTO nilai_alternatif
        (
            id_alternatif,
            id_kriteria,
            nilai
        )
        VALUES (?, ?, ?)
        "
    );

    mysqli_stmt_bind_param(
        $insert,
        "iid",
        $id_alternatif,
        $id_kriteria,
        $nilai
    );

    $berhasil = mysqli_stmt_execute($insert);

    mysqli_stmt_close($insert);

    return $berhasil;
}


// ============================================================
// MULAI TRANSAKSI
// ============================================================

mysqli_begin_transaction($koneksi);


// ============================================================
// COUNTER
// ============================================================

$total = 0;
$berhasil = 0;
$gagal = 0;


// ============================================================
// PROSES SEMUA ALTERNATIF
// ============================================================

while ($row = mysqli_fetch_assoc($result_alternatif)) {

    $total++;

    $id_alternatif = (int) $row['id_alternatif'];

    $tingkat_keunikan = trim($row['tingkat_keunikan']);
    $kat_keislaman = trim($row['kat_keislaman']);


    // ========================================================
    // NILAI C2 - TINGKAT KEUNIKAN
    // ========================================================

    switch ($tingkat_keunikan) {

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


    // ========================================================
    // NILAI C3 - NILAI KEISLAMAN
    // ========================================================

    switch ($kat_keislaman) {

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


    // ========================================================
    // SIMPAN C2
    // ========================================================

    $simpan_c2 = simpanNilai(
        $koneksi,
        $id_alternatif,
        $id_c2,
        $nilai_c2
    );


    // ========================================================
    // SIMPAN C3
    // ========================================================

    $simpan_c3 = simpanNilai(
        $koneksi,
        $id_alternatif,
        $id_c3,
        $nilai_c3
    );


    // ========================================================
    // CEK HASIL
    // ========================================================

    if ($simpan_c2 && $simpan_c3) {

        $berhasil++;

    } else {

        $gagal++;

    }
}


// ============================================================
// COMMIT / ROLLBACK
// ============================================================

if ($gagal == 0) {

    mysqli_commit($koneksi);

    $status = "berhasil";

} else {

    mysqli_rollback($koneksi);

    $status = "gagal";
}


// ============================================================
// TAMPILKAN HASIL
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
        Otomatisasi Nilai C2 dan C3
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/admin.css">
</head>


<?php include __DIR__ . "/includes/admin_header.php"; ?>


<div class="container py-5">


    <?php if ($status === "berhasil"): ?>

        <div class="card shadow-sm">

            <div class="card-header bg-success text-white">

                <h4 class="mb-0">
                    Berhasil
                </h4>

            </div>


            <div class="card-body">

                <p>
                    Nilai C2 dan C3 berhasil
                    diproses secara otomatis.
                </p>


                <table class="table table-bordered">

                    <tr>

                        <th>
                            Total Alternatif
                        </th>

                        <td>
                            <?= $total; ?>
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Berhasil Diproses
                        </th>

                        <td>
                            <?= $berhasil; ?>
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Gagal
                        </th>

                        <td>
                            <?= $gagal; ?>
                        </td>

                    </tr>

                </table>


                <h5>
                    Aturan C2
                </h5>

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


                <h5>
                    Aturan C3
                </h5>

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


                <div class="alert alert-warning">

                    <strong>Penting:</strong>

                    <br>

                    Kode ini hanya mengisi
                    <strong>C2 dan C3</strong>.

                    <br>

                    Nilai <strong>C1 Arti Nama</strong>
                    tidak diubah.

                </div>


                <a
                    href="hasil_ahp.php"
                    class="btn btn-success"
                >
                    Lihat Hasil AHP
                </a>


                <a
                    href="admin_dashboard.php"
                    class="btn btn-secondary"
                >
                    Dashboard
                </a>

            </div>

        </div>


    <?php else: ?>


        <div class="alert alert-danger">

            <h4>
                Proses gagal
            </h4>

            <p>
                Terjadi kesalahan ketika menyimpan
                nilai alternatif.
            </p>

        </div>


        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Kembali
        </a>


    <?php endif; ?>


</div>


<?php include __DIR__ . "/includes/admin_footer.php"; ?>

</html>
