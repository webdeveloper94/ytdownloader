<?php
session_start();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YT Downloader - Premium Video Yuklash Xizmati</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(to right, #ff0000, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .feature-card {
            background: #1e1e1e;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            transition: 0.4s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            border-color: #ff0000;
            box-shadow: 0 10px 30px rgba(255,0,0,0.1);
        }
        .feature-icon {
            font-size: 3rem;
            color: #ff0000;
            margin-bottom: 20px;
        }
        .navbar {
            background: rgba(15, 15, 15, 0.95);
            backdrop-filter: blur(10px);
        }
        footer {
            background: #0a0a0a;
            padding: 50px 0;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
        }
    </style>
</head>
<body class="bg-dark text-white">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-danger" href="#">YT Downloader</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link px-3" href="#features">Xususiyatlar</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#pricing">Narxlar</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="btn btn-danger ms-lg-3 px-4 rounded-pill" href="user/dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link px-3" href="login.php">Kirish</a></li>
                        <li class="nav-item"><a class="btn btn-outline-danger ms-lg-3 px-4 rounded-pill" href="register.php">Ro'yxatdan o'tish</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="hero-title">YouTube Videolarni Yuqori Sifatda Yuklang</h1>
                    <p class="lead text-secondary mb-5">Bizning xizmatimiz orqali sevimli videolaringizni 4K sifatgacha yuklab oling. Tez, xavfsiz va qulay!</p>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a href="register.php" class="btn btn-danger btn-lg px-5 py-3 rounded-pill fw-bold w-100 w-md-auto">Hoziroq Boshlang</a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill fw-bold w-100 w-md-auto">Ko'proq ma'lumot</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0" data-aos="zoom-in">
                    <div class="position-relative">
                        <img src="https://img.youtube.com/vi/aqz-KE-bpKQ/maxresdefault.jpg" class="img-fluid rounded-4 shadow-lg border border-secondary" alt="Demo">
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-circle-play text-danger display-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5 mt-5">
        <div class="container py-5 text-center">
            <h2 class="display-4 fw-bold mb-5" data-aos="fade-up">Nega Bizni Tanlaysiz?</h2>
            <div class="row g-4 pt-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="fa-solid fa-bolt feature-icon"></i>
                        <h3 class="mb-3">Tezkor Yuklash</h3>
                        <p class="text-secondary">Bizning serverlarimiz orqali videolaringiz soniyalar ichida yuklab olishga tayyor bo'ladi.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="fa-solid fa-video feature-icon"></i>
                        <h3 class="mb-3">Yuqori Sifat</h3>
                        <p class="text-secondary">720p dan 4K sifatgacha bo'lgan barcha formatlar qo'llab-quvvatlanadi.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="fa-solid fa-shield-halved feature-icon"></i>
                        <h3 class="mb-3">Xavfsiz Xizmat</h3>
                        <p class="text-secondary">Sizning ma'lumotlaringiz va yuklab olishlaringiz to'liq maxfiy saqlanadi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing (Static for Landing) -->
    <section id="pricing" class="py-5 mt-5 bg-black">
        <div class="container py-5">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h2 class="display-4 fw-bold mb-4">Hamyonbop Narxlar</h2>
                    <p class="text-secondary mb-5">O'zingizga mos keladigan obuna turini tanlang va chiroyli interfeysdan bahra oling.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4" data-aos="flip-left">
                    <div class="card bg-dark text-white border-secondary p-5 h-100 rounded-5 transition">
                        <h4 class="text-danger">Oylik Paketi</h4>
                        <h2 class="display-5 fw-bold my-4">50,000 <small class="fs-6 fw-normal">so'm/oy</small></h2>
                        <ul class="list-unstyled mb-5">
                            <li class="mb-3"><i class="fa-solid fa-check text-danger me-2"></i> Cheksiz yuklamalar</li>
                            <li class="mb-3"><i class="fa-solid fa-check text-danger me-2"></i> Barcha sifatlar (4K)</li>
                            <li class="mb-3"><i class="fa-solid fa-check text-danger me-2"></i> 24/7 Qo'llab-quvvatlash</li>
                            <li class="mb-3"><i class="fa-solid fa-check text-danger me-2"></i> Reklamasiz interfeys</li>
                        </ul>
                        <a href="register.php" class="btn btn-danger w-100 py-3 rounded-pill fw-bold mt-auto">Sotib olish</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-secondary">
        <div class="container text-center">
            <div class="mb-4">
                <a href="#" class="text-white mx-3 fs-4"><i class="fa-brands fa-telegram"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="fa-brands fa-youtube"></i></a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> YT Downloader. Barcha huquqlar himoyalangan.</p>
            <p class="small mt-2">Dasturchi: Antigravity AI Team</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>
