<?php require_once 'includes/header.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-8 w-full" x-data="{ c1_c2: 3, c1_c3: 5 }">
  <div class="glass-card rounded-2xl p-6 shadow-sm">
    <h2 class="text-xl font-bold text-slate-900 mb-2">Pembobotan Kriteria AHP</h2>
    <p class="text-sm text-slate-500 mb-6">Tentukan tingkat kepentingan relatif antar kriteria nama.</p>

    <!-- Pair 1 -->
    <div class="p-4 bg-slate-50 rounded-xl mb-4 border border-slate-100">
      <div class="flex justify-between font-semibold mb-2">
        <span class="text-emerald"><i class="fa-solid fa-book-quran mr-2"></i>Makna & Arti (C1)</span>
        <span class="text-teal-600"><i class="fa-solid fa-music mr-2"></i>Keindahan Pelafalan (C2)</span>
      </div>
      <input type="range" min="1" max="9" x-model="c1_c2" class="w-full accent-teal cursor-pointer">
      <div class="flex justify-between text-xs text-slate-500 mt-2">
        <span>Sama Penting (1)</span>
        <span class="font-bold text-gold" x-text="`Nilai Saaty: ${c1_c2}`"></span>
        <span>Mutlak Lebih Penting (9)</span>
      </div>
    </div>
    
    <button class="mt-4 w-full bg-emerald text-white font-semibold py-3 rounded-xl hover:bg-emerald-900 transition">
      Hitung Konsistensi & Lanjutkan <i class="fa-solid fa-arrow-right ml-2"></i>
    </button>
  </div>
</main>