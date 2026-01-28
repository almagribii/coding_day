<?php
require_once __DIR__ . '/../../config/auth.php';

// Redirect ke dashboard jika sudah login
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'PANITIA') {
        header('Location: /coding-day-app/panitia');
    } elseif ($role === 'PESERTA') {
        header('Location: /coding-day-app/peserta');
    } elseif ($role === 'JURI') {
        header('Location: /coding-day-app/juri');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coding Day 2026 - Kompetisi Pemrograman</title>
    <!-- INDEX VIEW MARKER v1 -->
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* --- CUSTOM STYLE CODING DAY --- */
        :root {
            --primary-bg: #0d1117; /* Warna Gelap ala GitHub Dark Mode */
            --accent-green: #2ea043; /* Hijau Sukses */
            --accent-blue: #58a6ff; /* Biru Link */
            --text-white: #c9d1d9;
            --card-bg: #161b22;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-white);
            overflow-x: hidden;
        }

        /* Font khusus koding */
        .font-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Navbar */
        .navbar {
            background-color: rgba(13, 17, 23, 0.9) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-white) !important;
            font-family: 'JetBrains Mono', monospace;
        }
        .nav-link {
            color: #8b949e !important;
            font-weight: 500;
            margin-left: 20px;
            transition: 0.3s;
        }
        .nav-link:hover {
            color: var(--accent-blue) !important;
        }
        .btn-login {
            background-color: var(--accent-green);
            color: #fff;
            font-weight: 700;
            border-radius: 6px;
            padding: 8px 25px;
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #238636;
            color: #fff;
        }

        /* Hero Section */
        .hero-section {
            padding: 120px 0 60px;
            position: relative;
        }
        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            color: white;
            font-family: 'JetBrains Mono', monospace;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #8b949e;
            margin-bottom: 40px;
        }
        
        /* Countdown Box */
        .countdown-box {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            display: inline-flex;
            gap: 30px;
            margin-bottom: 40px;
        }
        .time-val {
            font-size: 2rem;
            font-weight: 700;
            display: block;
            color: var(--accent-blue);
            font-family: 'JetBrains Mono', monospace;
        }
        .time-label {
            font-size: 0.8rem;
            color: #8b949e;
            text-transform: uppercase;
        }

        /* Buttons */
        .btn-cta {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #3771c8 100%);
            color: white;
            padding: 12px 30px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(88, 166, 255, 0.3);
        }
        .btn-cta:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #69b1ff 0%, #4a88ff 100%);
            color: white;
            box-shadow: 0 6px 12px rgba(88, 166, 255, 0.4);
        }
        .btn-outline-cta {
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            margin-left: 15px;
            transition: 0.3s;
        }
        .btn-outline-cta:hover {
            border-color: var(--text-white);
            color: var(--text-white);
        }

        .timeline-section {
            padding: 80px 0;
            position: relative;
            background-color: #0d1117;
        }
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 60px;
            font-family: 'JetBrains Mono', monospace;
        }
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background-color: #30363d;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
        }
        .container-tl {
            padding: 10px 40px;
            position: relative;
            background-color: inherit;
            width: 50%;
        }
        .container-tl::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            right: -8px;
            background-color: var(--primary-bg);
            border: 3px solid var(--accent-green);
            top: 15px;
            border-radius: 50%;
            z-index: 1;
        }
        .left { left: 0; }
        .right { left: 50%; }
        .left::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 18px;
            width: 0;
            z-index: 1;
            right: 30px;
            border: medium solid white;
            border-width: 10px 0 10px 10px;
            border-color: transparent transparent transparent var(--card-bg);
        }
        .right::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 18px;
            width: 0;
            z-index: 1;
            left: 30px;
            border: medium solid white;
            border-width: 10px 10px 10px 0;
            border-color: transparent var(--card-bg) transparent transparent;
        }
        .right::after { left: -8px; }
        .content-tl {
            padding: 20px 30px;
            background-color: var(--card-bg);
            position: relative;
            border-radius: 6px;
            border: 1px solid #30363d;
            transition: 0.3s;
        }
        .content-tl:hover {
            border-color: var(--accent-blue);
            transform: translateY(-2px);
        }
        .content-tl h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 5px;
            font-family: 'JetBrains Mono', monospace;
        }
        .content-tl p {
            font-size: 0.9rem;
            margin: 0;
            color: #8b949e;
        }

        .modal-content {
            background-color: var(--card-bg);
            border: 1px solid #30363d;
            color: white;
        }
        .modal-header { border-bottom: 1px solid #30363d; }
        .form-select {
            background-color: #0d1117;
            color: white;
            border: 1px solid #30363d;
        }
        .form-select:focus {
            background-color: #0d1117;
            color: white;
            box-shadow: none;
            border-color: var(--accent-blue);
        }
        
        @media screen and (max-width: 600px) {
            .timeline::after { left: 31px; }
            .container-tl { width: 100%; padding-left: 70px; padding-right: 25px; }
            .container-tl::before { left: 60px; border: medium solid white; border-width: 10px 10px 10px 0; border-color: transparent var(--card-bg) transparent transparent; }
            .left::after, .right::after { left: 23px; }
            .right { left: 0%; }
            .hero-title { font-size: 3rem; }
        }
        
        /* Syntax Highlight Decoration */
        .code-snippet {
            font-family: 'JetBrains Mono', monospace;
            background: #161b22;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #30363d;
            color: #ff7b72;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        .code-green { color: #7ee787; }
        .code-blue { color: #79c0ff; }
        
        /* Rules Section */
        .rules-section {
            padding: 80px 0;
            background: linear-gradient(180deg, #0d1117 0%, #161b22 100%);
        }
        
        .rule-card {
            background: linear-gradient(135deg, #161b22 0%, #1c2128 100%);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 35px 25px;
            text-align: center;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .rule-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 166, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .rule-card:hover::before {
            left: 100%;
        }
        
        .rule-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-blue);
            box-shadow: 0 10px 30px rgba(88, 166, 255, 0.3);
            background: linear-gradient(135deg, #1c2128 0%, #21262d 100%);
        }
        
        .rule-icon {
            font-size: 3rem;
            color: var(--accent-blue);
            margin-bottom: 20px;
            display: block;
            transition: all 0.4s ease;
        }
        
        .rule-card:hover .rule-icon {
            color: var(--accent-green);
            transform: scale(1.2) rotateY(360deg);
        }
        
        .rule-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            font-family: 'JetBrains Mono', monospace;
            transition: color 0.3s ease;
        }
        
        .rule-card:hover .rule-title {
            color: var(--accent-blue);
        }
        
        .rule-desc {
            font-size: 0.95rem;
            color: #8b949e;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Add animation delay for each card */
        .rule-card:nth-child(1) { animation: fadeInUp 0.6s ease-out 0.1s both; }
        .rule-card:nth-child(2) { animation: fadeInUp 0.6s ease-out 0.2s both; }
        .rule-card:nth-child(3) { animation: fadeInUp 0.6s ease-out 0.3s both; }
        .rule-card:nth-child(4) { animation: fadeInUp 0.6s ease-out 0.4s both; }
        .rule-card:nth-child(5) { animation: fadeInUp 0.6s ease-out 0.5s both; }
        .rule-card:nth-child(6) { animation: fadeInUp 0.6s ease-out 0.6s both; }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <span class="code-green">&lt;/&gt;</span> CODING DAY
            </a>
            <button class="navbar-toggler navbar-dark" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#beranda">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#timeline">Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kategori">Rules</a></li>
                    <li class="nav-item ms-3">
                        <a href="/coding-day-app/login" class="btn btn-login">
                            <i class="bi bi-terminal-fill me-1"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="beranda" class="hero-section d-flex align-items-center" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="code-snippet">
                        <span class="code-blue">Welcome</span> <span class="code-green">To</span>
                    </div>
                    <h1 class="hero-title">CODING DAY<br><span style="color: var(--accent-blue);">CHALLENGE 2026</span></h1>
                    <p class="hero-subtitle">Show Your Code, Prove Your Skills.<br>Satu hari penuh, tantangan nyata, dan satu pemenang.</p>

                    <div class="countdown-box">
                        <div class="time-item">
                            <span class="time-val" id="days">00</span>
                            <span class="time-label">Hari</span>
                        </div>
                        <div class="time-item">
                            <span class="time-val" id="hours">00</span>
                            <span class="time-label">Jam</span>
                        </div>
                        <div class="time-item">
                            <span class="time-val" id="minutes">00</span>
                            <span class="time-label">Menit</span>
                        </div>
                        <div class="time-item">
                            <span class="time-val" id="seconds">00</span>
                            <span class="time-label">Detik</span>
                        </div>
                    </div>

                    <div>
                        <a href="/coding-day-app/login" class="btn-cta">Mulai Coding</a>
                        <a href="#timeline" class="btn-outline-cta">Lihat Rundown</a>
                    </div>
                </div>
                
                <div class="col-lg-5 text-center mt-5 mt-lg-0 ">
                    <img src="/coding-day-app/public/logoo.png" alt="Coding Day Logo" class="img-fluid" style="max-width: 80%; filter: drop-shadow(0 0 30px rgba(88, 166, 255, 0.3)); animation: float 3s ease-in-out infinite;">
                </div>
            </div>
        </div>
    </section>

    <section id="timeline" class="timeline-section">
        <div class="container">
            <h2 class="section-title">Rundown Acara</h2>
            
            <div class="timeline">
                <div class="container-tl left">
                    <div class="content-tl">
                        <h2>08:00 - 09:00 WIB</h2>
                        <p><strong>Registrasi & Check-in</strong><br>Peserta melakukan daftar ulang dan persiapan setup laptop/environment.</p>
                    </div>
                </div>
                <div class="container-tl right">
                    <div class="content-tl">
                        <h2>09:00 - 10:00 WIB</h2>
                        <p><strong>Opening & Tech Briefing</strong><br>Pembukaan acara dan penjelasan aturan main serta studi kasus.</p>
                    </div>
                </div>
                <div class="container-tl left">
                    <div class="content-tl">
                        <h2>10:00 - 15:00 WIB</h2>
                        <p><strong>CODING TIME!</strong><br>Waktu pengerjaan proyek. 5 Jam nonstop untuk menyelesaikan tantangan.</p>
                    </div>
                </div>
                <div class="container-tl right">
                    <div class="content-tl">
                        <h2>16:00 WIB</h2>
                        <p><strong>Demo & Awarding</strong><br>Presentasi hasil karya dan pengumuman pemenang Coding Day.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="kategori" class="rules-section">
        <div class="container">
            <h2 class="section-title">
                <span style="color: var(--accent-green);"></span> Aturan Kompetisi
            </h2>
            <p class="text-center" style="color: #8b949e; font-size: 1.1rem; margin-top: -40px; margin-bottom: 60px;">
                Baca dengan teliti sebelum mendaftar dan berpartisipasi
            </p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-people-fill rule-icon"></i>
                        <h3 class="rule-title">Komposisi Tim</h3>
                        <p class="rule-desc">Setiap tim terdiri dari maksimal 3 anggota. Setiap anggota harus terdaftar resmi dan membawa kartu identitas saat check-in.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-laptop rule-icon"></i>
                        <h3 class="rule-title">Bring Your Own Device</h3>
                        <p class="rule-desc">Peserta wajib membawa laptop dan perlengkapan coding sendiri. Panitia hanya menyediakan koneksi internet dan listrik.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-shield-check rule-icon"></i>
                        <h3 class="rule-title">Originalitas Kode</h3>
                        <p class="rule-desc">Dilarang keras melakukan plagiasi. Kode harus ditulis selama kompetisi berlangsung. Template dasar diperbolehkan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-code-slash rule-icon"></i>
                        <h3 class="rule-title">Teknologi Bebas</h3>
                        <p class="rule-desc">Peserta bebas menggunakan bahasa pemrograman atau framework apa saja (Web/Mobile/Desktop) sesuai kenyamanan tim.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-git rule-icon"></i>
                        <h3 class="rule-title">Submission</h3>
                        <p class="rule-desc">Hasil akhir wajib di-upload ke Repository (GitHub/GitLab) dan link dikumpulkan sebelum batas waktu berakhir.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rule-card">
                        <i class="bi bi-trophy-fill rule-icon"></i>
                        <h3 class="rule-title">Fair Play</h3>
                        <p class="rule-desc">Keputusan juri bersifat mutlak. Segala bentuk kecurangan atau gangguan terhadap tim lain akan didiskualifikasi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center py-4" style="background: rgba(0,0,0,0.2); font-size: 0.9rem; color: #777; border-top: 1px solid #30363d;">
        <p class="mb-0">© 2026 Coding Day Organization. Built for Developers.</p>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const targetDate = new Date("March 1, 2026 09:00:00").getTime();

        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("days").innerText = days < 10 ? "0" + days : days;
            document.getElementById("hours").innerText = hours < 10 ? "0" + hours : hours;
            document.getElementById("minutes").innerText = minutes < 10 ? "0" + minutes : minutes;
            document.getElementById("seconds").innerText = seconds < 10 ? "0" + seconds : seconds;

            if (distance < 0) {
                clearInterval(timer);
                document.getElementById("days").innerHTML = "00";
            }
        }, 1000);
        
        // Animasi Floating
        const styleSheet = document.createElement("style");
        styleSheet.innerText = `
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-15px); }
                100% { transform: translateY(0px); }
            }
        `;
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>