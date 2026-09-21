```php
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['jenis_kelamin'])) {
    header("Location: pilih_jenis_kelamin.php");
    exit;
}

$kriteria = [];
$q = mysqli_query(
    $koneksi,
    "SELECT id_kriteria,kode_kriteria,nama_kriteria
     FROM kriteria
     WHERE id_kriteria IN (1,2,3)
     ORDER BY id_kriteria"
);

while ($r = mysqli_fetch_assoc($q)) {
    $kriteria[] = $r;
}

if (count($kriteria) !== 3) {
    die("Database harus memiliki 3 kriteria: C1, C2, C3.");
}

$pesan = $_SESSION['pref_error'] ?? '';
unset($_SESSION['pref_error']);
?>

<!doctype html>
<html lang="id">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Tentukan Prioritas Kriteria</title>

<style>

body{
    margin:0;
    background:#f4f7f5;
    font-family:Arial;
    color:#183028;
}

.wrap{
    max-width:800px;
    margin:40px auto;
    padding:20px;
}

.head{
    text-align:center;
}

.head h1{
    color:#0f5132;
}

.head p{
    color:#66736d;
}

.info{
    background:#fff7df;
    border-left:5px solid #d97706;
    padding:16px;
    border-radius:10px;
    line-height:1.6;
    margin-bottom:20px;
}

.pair{
    background:#fff;
    border-radius:16px;
    padding:24px;
    margin:18px 0;
    box-shadow:0 6px 20px rgba(0,0,0,.06);
}

.pair-title{
    display:flex;
    justify-content:space-between;
    gap:15px;
    align-items:center;
    text-align:center;
}

.criteria{
    flex:1;
}

.criteria b{
    font-size:18px;
}

.criteria small{
    color:#66736d;
}

.vs{
    font-weight:800;
    color:#d97706;
    font-size:18px;
}

.question{
    margin-top:22px;
    margin-bottom:8px;
    font-weight:bold;
    color:#183028;
}

select{
    width:100%;
    padding:14px 16px;
    border:2px solid #dbe7e1;
    border-radius:12px;
    background:#fff;
    font-size:15px;
    color:#183028;
    cursor:pointer;
    outline:none;
}

select:focus{
    border-color:#0f5132;
}

select:hover{
    border-color:#0f5132;
}

.gender{
    display:inline-block;
    background:#eaf6ef;
    color:#0f5132;
    padding:5px 10px;
    border-radius:20px;
    font-weight:700;
}

.note{
    font-size:12px;
    color:#66736d;
    margin-top:18px;
    line-height:1.5;
}

.actions{
    display:flex;
    justify-content:space-between;
    gap:15px;
    margin-top:25px;
}

.btn{
    border:0;
    border-radius:10px;
    padding:14px 22px;
    font-weight:700;
    text-decoration:none;
    cursor:pointer;
}

.back{
    background:#e9efec;
    color:#183028;
}

.next{
    background:#0f5132;
    color:#fff;
}

.next:hover{
    background:#0b3d26;
}

@media(max-width:700px){

    .pair-title{
        display:block;
    }

    .vs{
        margin:12px 0;
    }

    .actions{
        flex-direction:column;
    }

    .btn{
        text-align:center;
    }
}

</style>

</head>

<body>

<div class="wrap">

    <div class="head">

        <h1>Langkah 2 — Tentukan Prioritas</h1>

        <p>
            Anda tidak perlu memahami angka AHP.
            Pilih tingkat kepentingan yang paling sesuai dengan pendapat Anda.
        </p>

        <p>
            Jenis kelamin:
            <span class="gender">
                <?=($_SESSION['jenis_kelamin']==='L'
                    ? 'Laki-laki'
                    : 'Perempuan')?>
            </span>
        </p>

    </div>


    <div class="info">

        <b>Petunjuk:</b>

        Untuk setiap pasangan kriteria, pilih salah satu tingkat
        kepentingan berdasarkan pendapat Anda.

        <br><br>

        <b>Contoh:</b>
        Jika menurut Anda Arti Nama lebih penting daripada
        Tingkat Keunikan, pilih salah satu pilihan
        yang berada pada sisi Arti Nama.

    </div>


<form method="post" action="proses_ahp.php">

<?php

$pairs = [

    ['12',1,'Arti Nama',2,'Tingkat Keunikan'],

    ['13',1,'Arti Nama',3,'Nilai Keislaman'],

    ['23',2,'Tingkat Keunikan',3,'Nilai Keislaman']

];

foreach($pairs as $p):

?>

<div class="pair">

    <div class="pair-title">

        <div class="criteria">

            <b><?=$p[2]?></b>

            <br>

            <small>C<?=$p[1]?></small>

        </div>


        <div class="vs">
            VS
        </div>


        <div class="criteria">

            <b><?=$p[4]?></b>

            <br>

            <small>C<?=$p[3]?></small>

        </div>

    </div>


    <div class="question">

        Menurut Anda, kriteria mana yang lebih penting?

    </div>


    <select name="p<?=$p[0]?>" required>

        <option value="" selected disabled>
            -- Pilih tingkat kepentingan --
        </option>

        <option value="1">
            Sama penting
        </option>

        <option value="3">
            <?=$p[2]?> sedikit lebih penting
        </option>

        <option value="5">
            <?=$p[2]?> jelas lebih penting
        </option>

        <option value="7">
            <?=$p[2]?> sangat lebih penting
        </option>

        <option value="9">
            <?=$p[2]?> mutlak lebih penting
        </option>

        <option value="0.3333333333">
            <?=$p[4]?> sedikit lebih penting
        </option>

        <option value="0.2">
            <?=$p[4]?> jelas lebih penting
        </option>

        <option value="0.1428571429">
            <?=$p[4]?> sangat lebih penting
        </option>

        <option value="0.1111111111">
            <?=$p[4]?> mutlak lebih penting
        </option>

    </select>

</div>

<?php endforeach; ?>


<div class="note">

    <b>Catatan:</b>
    Angka AHP digunakan oleh sistem di belakang layar.
    Pengguna cukup memilih tingkat kepentingan berdasarkan
    kata-kata yang tersedia.

</div>


<div class="actions">

    <a class="btn back" href="pilih_jenis_kelamin.php">
        ← Kembali
    </a>

    <button class="btn next" type="submit">
        Proses AHP →
    </button>

</div>


</form>

</div>

</body>
</html>
```
