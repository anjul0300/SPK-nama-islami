<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SPK Pemilihan Nama Islami (AHP)</title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts: Plus Jakarta Sans & Amiri -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Alpine.js CDN -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Tailwind Theme Engine Setup -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            emerald: { DEFAULT: '#0F5132', 800: '#0F5132', 900: '#0a3822' },
            teal: { DEFAULT: '#0D9488', 600: '#0D9488' },
            gold: { DEFAULT: '#D97706', 500: '#D97706' },
            offwhite: '#F8FAFC'
          },
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
            arabic: ['Amiri', 'serif']
          }
        }
      }
    }
  </script>

  <style>
    .glass-header {
      background: rgba(15, 81, 50, 0.92);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.88);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.7);
    }
  </style>
</head>
<body class="bg-offwhite font-sans text-slate-800 antialiased min-h-screen flex flex-col">

  <!-- Shared Top Navigation Bar -->
  <header class="glass-header text-white sticky top-0 z-50 border-b border-emerald-700/40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      <a href="index.php" class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-2xl bg-gold/20 flex items-center justify-center text-gold text-xl border border-gold/30 shadow-inner">
          <i class="fa-solid fa-star-and-crescent"></i>
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight">Islamic <span class="text-gold font-normal">Names</h1>
          <p class="text-[11px] text-emerald-200 tracking-wide">SPK Pemilihan Nama Islami (AHP)</p>
        </div>
      </a>
      
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="index.php" class="hover:text-gold transition flex items-center gap-2">
          <i class="fa-solid fa-house text-xs"></i> Beranda
        </a>
        <a href="input_preferensi.php" class="hover:text-gold transition flex items-center gap-2">
          <i class="fa-solid fa-sliders text-xs"></i> Hitung AHP
        </a>
        <a href="hasil_rekomendasi.php" class="hover:text-gold transition flex items-center gap-2">
          <i class="fa-solid fa-trophy text-xs"></i> Hasil Rekomendasi
        </a>
        <a href="login.php" class="bg-gold hover:bg-gold-500 text-white px-4 py-2 rounded-xl transition font-semibold shadow-md">
          <i class="fa-solid fa-right-to-bracket mr-1"></i> Admin Login
        </a>
      </nav>
    </div>
  </header>