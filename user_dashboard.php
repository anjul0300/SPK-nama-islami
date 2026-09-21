<?php
session_start();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SPK Nama Islami</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f4f7f5;color:#183028}
.header{background:#0f5132;color:#fff;padding:18px 6%;display:flex;justify-content:space-between;align-items:center}
.brand{font-weight:700;font-size:20px}.brand span{color:#f0ad4e;font-weight:400}
.nav a{color:#fff;text-decoration:none;margin-left:20px;font-size:14px}.nav a:hover{color:#f0ad4e}
.hero{background:linear-gradient(135deg,#0f5132,#0a3822);color:#fff;padding:70px 20px;text-align:center}
.hero h1{font-size:42px;margin:0 auto 15px;max-width:800px}.hero p{max-width:700px;margin:auto;line-height:1.7;color:#e6f2eb}
.btn{display:inline-block;text-decoration:none;padding:13px 22px;border-radius:10px;font-weight:700}
.btn-gold{background:#d97706;color:#fff}.btn-gold:hover{background:#b45309}
.container{max-width:1050px;margin:0 auto;padding:35px 20px}
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:-28px}
.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 8px 25px rgba(0,0,0,.07)}
.step{text-align:center}.num{width:42px;height:42px;border-radius:50%;background:#e6f4ed;color:#0f5132;display:flex;align-items:center;justify-content:center;margin:auto;font-weight:800}
.step h3{font-size:15px;margin:12px 0 6px}.step p{font-size:13px;color:#68776f;line-height:1.5}
.criteria{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:30px}
.criteria h3{margin:8px 0}.criteria p{color:#66736d;line-height:1.6;font-size:14px}
footer{text-align:center;color:#77837d;padding:30px}
@media(max-width:800px){.steps,.criteria{grid-template-columns:1fr 1fr}.hero h1{font-size:32px}.nav{display:none}}
@media(max-width:520px){.steps,.criteria{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="header">
<div class="brand">Islamic <span>Name Finder</span></div>
<nav class="nav">
<a href="user_dashboard.php">Beranda</a>
<a href="pilih_jenis_kelamin.php">Mulai Rekomendasi</a>
<a href="login.php">Admin</a>
</nav>
</header>

<section class="hero">
<h1>Temukan Nama Islami yang Sesuai</h1>
<p>Sistem membantu memilih nama berdasarkan <b>Arti Nama</b>, <b>Tingkat Keunikan</b>, dan <b>Nilai Keislaman</b> menggunakan metode AHP.</p>
<div style="margin-top:28px"><a class="btn btn-gold" href="pilih_jenis_kelamin.php">Mulai Pemilihan Nama →</a></div>
</section>

<main class="container">
<div class="steps">
<div class="card step"><div class="num">1</div><h3>Pilih Jenis Kelamin</h3><p>Pilih laki-laki atau perempuan agar daftar nama sesuai.</p></div>
<div class="card step"><div class="num">2</div><h3>Tentukan Prioritas</h3><p>Bandingkan tiga kriteria dengan pilihan kata yang mudah dipahami.</p></div>
<div class="card step"><div class="num">3</div><h3>Proses AHP</h3><p>Sistem membentuk matriks, bobot, dan uji konsistensi secara otomatis.</p></div>
<div class="card step"><div class="num">4</div><h3>Hasil Rekomendasi</h3><p>Nama diurutkan dari skor tertinggi sampai terendah.</p></div>
</div>

<h2 style="margin-top:55px;text-align:center">Tiga Kriteria Penilaian</h2>
<div class="criteria">
<div class="card"><b>C1</b><h3>Arti Nama</h3><p>Menilai kualitas dan makna positif yang terkandung dalam nama.</p></div>
<div class="card"><b>C2</b><h3>Tingkat Keunikan</h3><p>Menilai tingkat keunikan nama dibandingkan dengan alternatif lain.</p></div>
<div class="card"><b>C3</b><h3>Nilai Keislaman</h3><p>Menilai keterkaitan nama dengan nilai dan makna keislaman.</p></div>
</div>
</main>
<footer>SPK Pemilihan Nama-Nama Islami · Metode Analytical Hierarchy Process (AHP)</footer>
</body>
</html>