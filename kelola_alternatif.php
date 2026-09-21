<?php
require_once 'koneksi.php';         // Koneksi Database[cite: 1]
require_once 'includes/header.php'; // Header CSS & Assets[cite: 1]

// Query data alternatif dari MySQL
$query = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
?>

<main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow" x-data="{ showModal: false, isEdit: false }">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900">Kelola Data Alternatif Nama</h2>
      <p class="text-xs text-slate-500">Manajemen master data alternatif sesuai struktur basis data `alternatif`.</p>
    </div>
    <button @click="showModal = true; isEdit = false" class="bg-emerald hover:bg-emerald-900 text-white font-semibold px-4 py-2.5 rounded-xl text-xs sm:text-sm transition shadow-md">
      <i class="fa-solid fa-plus mr-1.5"></i> Tambah Alternatif
    </button>
  </div>

  <!-- Table Container -->
  <div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-slate-100">
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead class="bg-slate-100 text-slate-700 uppercase font-semibold">
          <tr>
            <th class="p-3.5">ID</th>
            <th class="p-3.5">Nama Islami</th>
            <th class="p-3.5">Arti Nama</th>
            <th class="p-3.5">Keunikan (Enum)</th>
            <th class="p-3.5">Sumber Nama</th>
            <th class="p-3.5">Kat. Keislaman</th>
            <th class="p-3.5">Gender</th>
            <th class="p-3.5 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php while ($row = mysqli_fetch_assoc($query)): ?>
            <tr class="hover:bg-slate-50">
              <td class="p-3.5 font-mono text-slate-500"><?= $row['id_alternatif'] ?></td>
              <td class="p-3.5 font-bold text-slate-900"><?= $row['nama_islami'] ?></td>
              <td class="p-3.5 text-slate-600 max-w-xs truncate"><?= $row['arti_nama'] ?></td>
              <td class="p-3.5">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-teal/10 text-teal">
                  <?= $row['tingkat_keunikan'] ?>
                </span>
              </td>
              <td class="p-3.5"><?= $row['sumber_nama'] ?></td>
              <td class="p-3.5 text-slate-600"><?= $row['kat_keislaman'] ?></td>
              <td class="p-3.5">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald/10 text-emerald">
                  <?= $row['jenis_kelamin'] ?>
                </span>
              </td>
              <td class="p-3.5 text-center space-x-2">
                <button @click="showModal = true; isEdit = true" class="text-teal hover:text-teal-600"><i class="fa-solid fa-pen-to-square"></i></button>
                <a href="hapus_alternatif.php?id=<?= $row['id_alternatif'] ?>" onclick="return confirm('Hapus data ini?')" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash"></i></a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Popup CRUD -->
  <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" style="display: none;">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
      <div class="flex justify-between items-center border-b pb-3">
        <h3 class="font-bold text-slate-900 text-sm" x-text="isEdit ? 'Edit Alternatif Nama' : 'Tambah Alternatif Nama Baru'"></h3>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
      </div>
      
      <form action="simpan_alternatif.php" method="POST" class="space-y-3 text-xs">
        <div>
          <label class="block font-semibold text-slate-700 mb-1">Nama Islami</label>
          <input type="text" name="nama_islami" required class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none">
        </div>
        <div>
          <label class="block font-semibold text-slate-700 mb-1">Arti Nama</label>
          <textarea name="arti_nama" rows="2" required class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Tingkat Keunikan (Enum)</label>
            <select name="tingkat_keunikan" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none bg-white">
              <option value="Sangat Unik">Sangat Unik</option>
              <option value="Unik">Unik</option>
              <option value="Populer">Populer</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none bg-white">
              <option value="L">Laki-laki (L)</option>
              <option value="P">Perempuan (P)</option>
              <option value="Unisex">Unisex</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Sumber Nama</label>
            <input type="text" name="sumber_nama" placeholder="cth: Al-Qur'an / Hadis" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Kategori Keislaman</label>
            <input type="text" name="kat_keislaman" placeholder="cth: Istilah Al-Qur'an" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none">
          </div>
        </div>
        <div>
          <label class="block font-semibold text-slate-700 mb-1">Referensi Detail</label>
          <input type="text" name="referensi_detail" placeholder="cth: QS. An-Nahl Ayat 16" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-teal outline-none">
        </div>

        <div class="flex justify-end space-x-2 pt-3">
          <button type="button" @click="showModal = false" class="bg-slate-200 px-4 py-2 rounded-xl font-semibold text-slate-700">Batal</button>
          <button type="submit" class="bg-emerald text-white px-5 py-2 rounded-xl font-semibold shadow-md">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>
</main>