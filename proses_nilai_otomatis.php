<?php

// ============================================================
// TAMPILKAN ERROR PHP
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

require_once 'koneksi.php';


// ============================================================
// CEK KONEKSI
// ============================================================

if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal.");
}


// ============================================================
// CEK LOGIN
// ============================================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Anda belum login sebagai admin.");
}


// ============================================================
// AMBIL ID KRITERIA
// ============================================================

$sql_kriteria = "
    SELECT id_kriteria, nama_kriteria
    FROM kriteria
    ORDER BY id_kriteria ASC
";

$result_kriteria = mysqli_query($koneksi, $sql_kriteria);

if (!$result_kriteria) {
    die(
        "Query kriteria gagal: "
        . mysqli_error($koneksi)
    );
}


$kriteria = [];

while ($row = mysqli_fetch_assoc($result_kriteria)) {
    $kriteria[] = $row;
}


// ============================================================
// PASTIKAN 3 KRITERIA
// ============================================================

if (count($kriteria) != 3) {

    die(
        "Jumlah kriteria harus 3. "
        . "Saat ini: "
        . count($kriteria)
    );

}


$c1 = (int) $kriteria[0]['id_kriteria'];
$c2 = (int) $kriteria[1]['id_kriteria'];
$c3 = (int) $kriteria[2]['id_kriteria'];


// ============================================================
// TAMPILKAN KRITERIA
// ============================================================

echo "<h3>Proses Nilai Otomatis</h3>";

echo "<p>";
echo "C1 = " . htmlspecialchars($kriteria[0]['nama_kriteria']);
echo "<br>";
echo "C2 = " . htmlspecialchars($kriteria[1]['nama_kriteria']);
echo "<br>";
echo "C3 = " . htmlspecialchars($kriteria[2]['nama_kriteria']);
echo "</p>";


// ============================================================
// AMBIL SEMUA ALTERNATIF
// ============================================================

$sql_alternatif = "
    SELECT
        id_alternatif,
        nama_islami,
        arti_nama,
        tingkat_keunikan,
        kat_keislaman,
        jenis_kelamin
    FROM alternatif
    ORDER BY id_alternatif ASC
";

$result_alternatif = mysqli_query(
    $koneksi,
    $sql_alternatif
);

if (!$result_alternatif) {

    die(
        "Query alternatif gagal: "
        . mysqli_error($koneksi)
    );

}


// ============================================================
// HITUNG JUMLAH DATA
// ============================================================

$total = mysqli_num_rows($result_alternatif);

echo "<p>";
echo "Jumlah nama ditemukan: <strong>$total</strong>";
echo "</p>";


// ============================================================
// FUNGSI NILAI C1
// ARTI NAMA
// ============================================================

function nilaiArtiNama($arti)
{

    $arti = trim($arti);

    $jumlah_kata = str_word_count(
        preg_replace('/[^a-zA-Z0-9 ]/', ' ', $arti)
    );


    if ($arti === '') {
        return 1;
    }


    if ($jumlah_kata >= 6) {
        return 9;
    }

    if ($jumlah_kata >= 4) {
        return 8;
    }

    if ($jumlah_kata >= 3) {
        return 7;
    }

    if ($jumlah_kata >= 2) {
        return 6;
    }

    return 5;
}


// ============================================================
// FUNGSI NILAI C2
// TINGKAT KEUNIKAN
// ============================================================

function nilaiKeunikan($keunikan)
{

    switch ($keunikan) {

        case 'Sangat Unik':
            return 9;

        case 'Sedang':
            return 5;

        case 'Umum':
            return 1;

        default:
            return 1;

    }

}


// ============================================================
// FUNGSI NILAI C3
// NILAI KEISLAMAN
// ============================================================

function nilaiKeislaman($kat)
{

    switch ($kat) {

        case 'Asmaul Husna / Nabi':
            return 9;

        case 'Sahabat / Tokoh Mulia':
            return 8;

        case 'Bahasa Arab Bermakna Baik':
            return 7;

        case 'Nama Lokal Positif':
            return 5;

        default:
            return 3;

    }

}


// ============================================================
// FUNGSI NILAI JENIS KELAMIN
// ============================================================

function nilaiJenisKelamin($jenis)
{

    // Untuk saat ini jenis kelamin tidak
    // digunakan sebagai kriteria terpisah.
    //
    // C3 tetap berasal dari kategori keislaman.

    return 0;

}


// ============================================================
// PROSES SETIAP NAMA
// ============================================================

$berhasil = 0;
$gagal = 0;


while ($row = mysqli_fetch_assoc($result_alternatif)) {

    $id = (int) $row['id_alternatif'];


    // --------------------------------------------------------
    // C1 = ARTI NAMA
    // --------------------------------------------------------

    $nilai_c1 = nilaiArtiNama(
        $row['arti_nama']
    );


    // --------------------------------------------------------
    // C2 = TINGKAT KEUNIKAN
    // --------------------------------------------------------

    $nilai_c2 = nilaiKeunikan(
        $row['tingkat_keunikan']
    );


    // --------------------------------------------------------
    // C3 = NILAI KEISLAMAN
    // --------------------------------------------------------

    $nilai_c3 = nilaiKeislaman(
        $row['kat_keislaman']
    );


    // --------------------------------------------------------
    // HAPUS NILAI LAMA
    // --------------------------------------------------------

    $hapus = mysqli_query(
        $koneksi,
        "
        DELETE FROM nilai_alternatif
        WHERE id_alternatif = $id
        "
    );


    if (!$hapus) {

        echo "<p style='color:red'>";
        echo "Gagal menghapus nilai ID $id: ";
        echo htmlspecialchars(mysqli_error($koneksi));
        echo "</p>";

        $gagal++;

        continue;
    }


    // --------------------------------------------------------
    // SIMPAN C1
    // --------------------------------------------------------

    $q1 = mysqli_query(
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
            $id,
            $c1,
            $nilai_c1
        )
        "
    );


    // --------------------------------------------------------
    // SIMPAN C2
    // --------------------------------------------------------

    $q2 = mysqli_query(
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
            $id,
            $c2,
            $nilai_c2
        )
        "
    );


    // --------------------------------------------------------
    // SIMPAN C3
    // --------------------------------------------------------

    $q3 = mysqli_query(
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
            $id,
            $c3,
            $nilai_c3
        )
        "
    );


    if (!$q1 || !$q2 || !$q3) {

        echo "<p style='color:red'>";
        echo "Gagal menyimpan nilai untuk ";
        echo htmlspecialchars($row['nama_islami']);
        echo ": ";
        echo htmlspecialchars(mysqli_error($koneksi));
        echo "</p>";

        $gagal++;

        continue;
    }


    $berhasil++;

}


// ============================================================
// HASIL
// ============================================================

echo "<hr>";

echo "<h3 style='color:green'>";
echo "Proses selesai";
echo "</h3>";

echo "<p>";
echo "Berhasil: <strong>$berhasil</strong>";
echo "<br>";
echo "Gagal: <strong>$gagal</strong>";
echo "</p>";


// ============================================================
// TOMBOL
// ============================================================

echo '
<br>

<a
    href="hasil_ahp.php"
    style="
        display:inline-block;
        padding:10px 18px;
        background:#198754;
        color:white;
        text-decoration:none;
        border-radius:5px;
    "
>
    Lihat Hasil AHP
</a>

';


?>