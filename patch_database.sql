USE spk_nama_islami;

-- Pastikan tiga kriteria penelitian.
DELETE FROM kriteria WHERE id_kriteria NOT IN (1,2,3);

INSERT INTO kriteria (id_kriteria,kode_kriteria,nama_kriteria) VALUES
(1,'C1','Arti Nama'),
(2,'C2','Tingkat Keunikan'),
(3,'C3','Nilai Keislaman')
ON DUPLICATE KEY UPDATE
kode_kriteria=VALUES(kode_kriteria),
nama_kriteria=VALUES(nama_kriteria);

-- Preferensi pengguna tidak perlu disimpan permanen sebagai satu nilai global.
-- Proses user disimpan sementara di session PHP agar preferensi tiap user tidak bercampur.
