<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = $_POST['jenis_kelamin'] ?? '';
    if (in_array($gender, ['L','P'], true)) {
        $_SESSION['jenis_kelamin'] = $gender;
        header("Location: input_preferensi.php");
        exit;
    }
    $error = "Silakan pilih jenis kelamin terlebih dahulu.";
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pilih Jenis Kelamin</title>
<style>
body{margin:0;background:#f4f7f5;font-family:Arial;color:#183028}.wrap{max-width:760px;margin:60px auto;padding:20px}.card{background:#fff;border-radius:18px;padding:35px;box-shadow:0 8px 25px rgba(0,0,0,.08)}h1{text-align:center;color:#0f5132}.muted{text-align:center;color:#66736d;line-height:1.6}.choices{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:30px 0}.choice{border:2px solid #dbe7e1;border-radius:15px;padding:28px;text-align:center;cursor:pointer}.choice:hover{border-color:#0f5132;background:#f4fbf7}.choice input{display:none}.choice.selected{border-color:#0f5132;background:#eaf6ef}.icon{font-size:38px}.choice b{display:block;margin-top:10px;font-size:18px}.btn{width:100%;border:0;padding:14px;border-radius:10px;background:#0f5132;color:#fff;font-weight:700;font-size:16px;cursor:pointer}.back{text-align:center;margin-top:15px}.back a{color:#0f5132}.error{background:#fdecec;color:#a33;padding:12px;border-radius:10px;margin-bottom:15px}
@media(max-width:600px){.choices{grid-template-columns:1fr}}
</style>
<script>
function choose(el){
 document.querySelectorAll('.choice').forEach(x=>x.classList.remove('selected'));
 el.classList.add('selected'); el.querySelector('input').checked=true;
}
</script>
</head>
<body>
<div class="wrap"><div class="card">
<h1>Langkah 1 — Pilih Jenis Kelamin</h1>
<p class="muted">Pilih jenis kelamin yang akan digunakan untuk menyaring nama Islami.</p>
<?php if(!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post">
<div class="choices">
<label class="choice" onclick="choose(this)"><input type="radio" name="jenis_kelamin" value="L"><div class="icon">👦</div><b>Laki-laki</b><span>Nama untuk bayi laki-laki</span></label>
<label class="choice" onclick="choose(this)"><input type="radio" name="jenis_kelamin" value="P"><div class="icon">👧</div><b>Perempuan</b><span>Nama untuk bayi perempuan</span></label>
</div>
<button class="btn" type="submit">Lanjut ke Penentuan Prioritas →</button>
</form>
<div class="back"><a href="user_dashboard.php">← Kembali ke Beranda</a></div>
</div></div>
</body>
</html>