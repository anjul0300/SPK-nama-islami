<?php
// includes/ahp.php

/**
 * Menghitung Eigenvector (Bobot Prioritas) dari Matriks Pairwise Comparison
 */
function hitungEigenvector($matriks) {
    $n = count($matriks);
    $jumlahKolom = array_fill(0, $n, 0);

    // 1. Hitung jumlah total tiap kolom
    for ($j = 0; $j < $n; $j++) {
        for ($i = 0; $i < $n; $i++) {
            $jumlahKolom[$j] += $matriks[$i][$j];
        }
    }

    // 2. Normalisasi matriks & hitung rata-rata baris (Eigenvector)
    $eigenvector = array_fill(0, $n, 0);
    for ($i = 0; $i < $n; $i++) {
        $totalBaris = 0;
        for ($j = 0; $j < $n; $j++) {
            $totalBaris += $matriks[$i][$j] / $jumlahKolom[$j];
        }
        $eigenvector[$i] = $totalBaris / $n;
    }

    return $eigenvector;
}

/**
 * Menghitung Consistency Ratio (CR) AHP
 * RI (Random Index) untuk Ordo 1-5
 */
function hitungConsistencyRatio($matriks, $eigenvector) {
    $n = count($matriks);
    if ($n <= 2) return 0; // Ordo 1 & 2 selalu konsisten

    $riDict = [1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12];
    
    // Hitung Lambda Max
    $lambdaMax = 0;
    for ($j = 0; $j < $n; $j++) {
        $jumlahKolom = 0;
        for ($i = 0; $i < $n; $i++) {
            $jumlahKolom += $matriks[$i][$j];
        }
        $lambdaMax += $jumlahKolom * $eigenvector[$j];
    }

    // Hitung Consistency Index (CI)
    $ci = ($lambdaMax - $n) / ($n - 1);
    
    // Hitung Consistency Ratio (CR)
    $ri = isset($riDict[$n]) ? $riDict[$n] : 1.12;
    return $ci / $ri;
}
?>