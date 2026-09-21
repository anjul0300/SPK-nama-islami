<?php
include_once 'koneksi.php';
include_once 'auth.php';
require_role('admin');

if (isset($_POST['populate'])) {
    $data = [
        1 => [1 => 0.95, 2 => 1.0, 3 => 0.98],
        2 => [1 => 0.93, 2 => 1.0, 3 => 0.99],
        3 => [1 => 0.99, 2 => 1.0, 3 => 1.0],
        4 => [1 => 0.91, 2 => 1.0, 3 => 0.97],
        5 => [1 => 0.88, 2 => 1.0, 3 => 0.95],
        // ... dst untuk alternatif 6-15
    ];

    foreach ($data as $id_alt => $kriteria_values) {
        foreach ($kriteria_values as $id_krit => $nilai) {
            $query = "INSERT INTO nilai_alternatif (id_alternatif, id_kriteria, nilai) 
                      VALUES ('$id_alt', '$id_krit', '$nilai')
                      ON DUPLICATE KEY UPDATE nilai = '$nilai'";
            
            if (!mysqli_query($koneksi, $query)) {
                echo "Error: " . mysqli_error($koneksi) . "<br>";
            }
        }
    }
    echo "<div class='alert alert-success'>Data nilai alternatif berhasil dipopulasi!</div>";
}
?>

<form method="POST">
    <button type="submit" name="populate" class="btn btn-primary">
        Populate Nilai Alternatif
    </button>
</form>