<?php
if (!file_exists('koneksi.php')) {
    die("Error Fatal: File koneksi.php tidak ditemukan!");
}
include_once 'koneksi.php';

// 1. AMBIL KRITERIA DARI DB
function ambil_kriteria_ahp($koneksi) {
    $kriteria = [];
    $query = "SELECT * FROM kriteria ORDER BY id_kriteria ASC";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $kriteria[(int)$row['id_kriteria']] = [
                'kode_kriteria' => $row['kode_kriteria'],
                'nama_kriteria' => $row['nama_kriteria']
            ];
        }
    }
    return $kriteria;
}

// 2. HITUNG BOBOT KRITERIA AHP ADMIN (3 Kriteria)
function hitung_bobot_kriteria_ahp($koneksi) {
    $kriteria = ambil_kriteria_ahp($koneksi);
    $n = count($kriteria);

    if ($n === 0) {
        return ['status' => 'error', 'pesan' => 'Data kriteria belum dikonfigurasi di database!'];
    }

    $id_kriteria = array_keys($kriteria);
    $matriks = [];
    foreach ($id_kriteria as $i) {
        foreach ($id_kriteria as $j) {
            $matriks[$i][$j] = ($i == $j) ? 1.0 : 1.0;
        }
    }

    $query_pb = "SELECT * FROM perbandingan_kriteria";
    $result_pb = mysqli_query($koneksi, $query_pb);
    if ($result_pb) {
        while ($row = mysqli_fetch_assoc($result_pb)) {
            $k1 = (int)$row['kriteria_1'];
            $k2 = (int)$row['kriteria_2'];
            $val = (float)$row['nilai_perbandingan'];
            $matriks[$k1][$k2] = $val;
        }
    }

    $jumlah_kolom = [];
    foreach ($id_kriteria as $j) {
        $jumlah_kolom[$j] = 0.0;
        foreach ($id_kriteria as $i) {
            $jumlah_kolom[$j] += $matriks[$i][$j];
        }
    }

    $bobot = [];
    foreach ($id_kriteria as $i) {
        $sum_baris = 0.0;
        foreach ($id_kriteria as $j) {
            $sum_baris += ($matriks[$i][$j] / ($jumlah_kolom[$j] ?: 1));
        }
        $bobot[$i] = $sum_baris / $n;
    }

    $consistency_vector = [];
    foreach ($id_kriteria as $i) {
        $consistency_vector[$i] = 0.0;
        foreach ($id_kriteria as $j) {
            $consistency_vector[$i] += $matriks[$i][$j] * $bobot[$j];
        }
    }

    $lambda_max = 0.0;
    foreach ($id_kriteria as $i) {
        $lambda_max += ($consistency_vector[$i] / ($bobot[$i] ?: 1));
    }
    $lambda_max = $lambda_max / $n;

    $CI = ($n > 1) ? (($lambda_max - $n) / ($n - 1)) : 0;
    $RI = 0.58; // N = 3
    $CR = ($RI > 0) ? ($CI / $RI) : 0;

    return [
        'status' => 'success',
        'kriteria' => $kriteria,
        'bobot' => $bobot,
        'lambda_max' => $lambda_max,
        'ci' => $CI,
        'cr' => $CR,
        'konsisten' => ($CR <= 0.1)
    ];
}

// 3. HITUNG AHP DARI INPUT PREFERENSI USER (Sesuai Flowchart User)
function hitung_ahp_user($koneksi, $gender_pilihan, $matriks_input) {
    $n = 3; 
    $matriks = array_fill(1, $n, array_fill(1, $n, 1.0));

    $matriks[1][2] = (float)$matriks_input['c1_c2'];
    $matriks[2][1] = 1 / $matriks[1][2];

    $matriks[1][3] = (float)$matriks_input['c1_c3'];
    $matriks[3][1] = 1 / $matriks[1][3];

    $matriks[2][3] = (float)$matriks_input['c2_c3'];
    $matriks[3][2] = 1 / $matriks[2][3];

    $jumlah_kolom = array_fill(1, $n, 0.0);
    for ($j = 1; $j <= $n; $j++) {
        for ($i = 1; $i <= $n; $i++) {
            $jumlah_kolom[$j] += $matriks[$i][$j];
        }
    }

    $bobot_kriteria = array_fill(1, $n, 0.0);
    for ($i = 1; $i <= $n; $i++) {
        $jumlah_baris = 0.0;
        for ($j = 1; $j <= $n; $j++) {
            $jumlah_baris += ($matriks[$i][$j] / $jumlah_kolom[$j]);
        }
        $bobot_kriteria[$i] = $jumlah_baris / $n;
    }

    $consistency_vector = array_fill(1, $n, 0.0);
    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            $consistency_vector[$i] += $matriks[$i][$j] * $bobot_kriteria[$j];
        }
    }

    $lambda_max = 0.0;
    for ($i = 1; $i <= $n; $i++) {
        $lambda_max += ($consistency_vector[$i] / $bobot_kriteria[$i]);
    }
    $lambda_max = $lambda_max / $n;

    $CI = ($lambda_max - $n) / ($n - 1);
    $RI = 0.58; 
    $CR = ($RI > 0) ? ($CI / $RI) : 0;

    // Peringatan inkonsistensi (CR > 0.1) sesuai Flowchart User
    if ($CR > 0.1) {
        return [
            'status' => 'error', 
            'pesan' => 'Penilaian preferensi Anda tidak konsisten (CR = '.round($CR, 4).' > 0.1). Harap ulangi pengisian perbandingan kriteria!'
        ];
    }

    $gender_norm = normalisasi_gender($gender_pilihan);
    $query_alt = "SELECT * FROM alternatif WHERE jenis_kelamin = '$gender_norm'";
    $result_alt = mysqli_query($koneksi, $query_alt);
    
    $nilai_tersimpan = ambil_nilai_alternatif($koneksi);
    $hasil_rangking = [];

    while ($alt = mysqli_fetch_assoc($result_alt)) {
        $id_alt = (int)$alt['id_alternatif'];
        $skor_akhir = 0.0;

        for ($c = 1; $c <= $n; $c++) {
            $nilai_performa = $nilai_tersimpan[$id_alt][$c] ?? 1.0;
            $skor_akhir += ($bobot_kriteria[$c] * $nilai_performa);
        }

        $hasil_rangking[] = [
            'nama_islami' => $alt['nama_islami'],
            'arti_nama' => $alt['arti_nama'],
            'skor' => round($skor_akhir, 3)
        ];
    }

    usort($hasil_rangking, function ($a, $b) {
        return $b['skor'] <=> $a['skor'];
    });

    return [
        'status' => 'success',
        'bobot' => $bobot_kriteria,
        'cr' => round($CR, 4),
        'data' => $hasil_rangking
    ];
}

// 4. EKSEKUSI AHP ADMIN (Dipanggil hasil_ahp.php)
function jalankan_ahp($koneksi, $gender_pilihan = '') {
    $hitung_bobot = hitung_bobot_kriteria_ahp($koneksi);
    if ($hitung_bobot['status'] !== 'success') {
        return $hitung_bobot;
    }

    $kriteria = $hitung_bobot['kriteria'];
    $bobot = $hitung_bobot['bobot'];

    $query_alt = "SELECT * FROM alternatif";
    if ($gender_pilihan === 'L' || $gender_pilihan === 'P') {
        $query_alt .= " WHERE jenis_kelamin = '$gender_pilihan'";
    }
    $query_alt .= " ORDER BY id_alternatif ASC";

    $result_alt = mysqli_query($koneksi, $query_alt);
    if (!$result_alt) {
        return ['status' => 'error', 'pesan' => 'Gagal mengambil data alternatif: ' . mysqli_error($koneksi)];
    }

    $nilai_tersimpan = ambil_nilai_alternatif($koneksi);
    $daftar_ranking = [];
    $no_kode = 1;

    while ($row = mysqli_fetch_assoc($result_alt)) {
        $id_alt = (int)$row['id_alternatif'];
        $nama = $row['nama_islami'];
        $arti = $row['arti_nama'];
        $jk_norm = normalisasi_gender($row['jenis_kelamin']);
        $kode = "A" . $no_kode++;

        $total_skor = 0.0;
        $nilai_kriteria_map = [];

        foreach ($kriteria as $id_kr => $kr) {
            $kode_kr = $kr['kode_kriteria'];
            $val_perform = $nilai_tersimpan[$id_alt][$id_kr] ?? 1.0;
            
            $nilai_kriteria_map[$kode_kr] = $val_perform;
            $total_skor += ($val_perform * ($bobot[$id_kr] ?? 0));
        }

        $daftar_ranking[] = [
            'id_alternatif' => $id_alt,
            'kode' => $kode,
            'nama_islami' => $nama,
            'arti_nama' => $arti,
            'jenis_kelamin' => $jk_norm,
            'label_jenis_kelamin' => label_gender($jk_norm),
            'nilai' => $nilai_kriteria_map,
            'skor' => round($total_skor, 4)
        ];
    }

    usort($daftar_ranking, function($a, $b) {
        return $b['skor'] <=> $a['skor'];
    });

    return [
        'status' => 'success',
        'kriteria' => $kriteria,
        'bobot' => $bobot,
        'cr' => $hitung_bobot['cr'],
        'konsisten' => $hitung_bobot['konsisten'],
        'data' => $daftar_ranking
    ];
}

// 5. HELPER NILAI & GENDER
function ambil_nilai_alternatif($koneksi) {
    $data = [];
    $query = "SELECT id_alternatif, id_kriteria, nilai FROM nilai_alternatif";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[(int)$row['id_alternatif']][(int)$row['id_kriteria']] = (float)$row['nilai'];
        }
    }
    return $data;
}

function normalisasi_gender($gender) {
    $g = strtoupper(trim($gender));
    if ($g === 'L' || $g === 'LAKI-LAKI' || $g === 'LAKI') {
        return 'L';
    }
    return 'P';
}

function label_gender($gender) {
    return normalisasi_gender($gender) === 'L' ? 'Laki-laki' : 'Perempuan';
}
?>