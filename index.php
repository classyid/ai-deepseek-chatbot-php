<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: chat.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI DeepSeek Assistant - Asisten AI Pintar untuk Anda</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #2563eb;
            --bg-color: #f8fafc;
            --text-color: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-color);
        }

        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ffffff' fill-opacity='0.05' d='M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E") repeat;
            opacity: 0.1;
            animation: wave 20s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0) translateY(0) rotate(0); }
            100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            text-align: center;
            color: white;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: white;
            color: var(--primary-color);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 12px;
            text-align: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .feature-description {
            opacity: 0.9;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
        }

        .benefits {
            margin-top: 4rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            text-align: center;
        }

        .benefits-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
        }

        .benefit-icon {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
                padding: 0 1rem;
            }

            .cta-buttons {
                flex-direction: column;
                padding: 0 2rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 0 1rem;
            }

            .benefits {
                margin: 3rem 1rem 0 1rem;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">AI DeepSeek Assistant</h1>
                <p class="hero-subtitle">
                    Asisten AI pintar yang siap membantu Anda 24/7. Dengan teknologi DeepSeek terkini, 
                    kami menyediakan solusi cerdas untuk berbagai kebutuhan Anda, dari analisis data 
                    hingga pemecahan masalah kompleks.
                </p>
                
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                    <a href="register.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Daftar Gratis
                    </a>
                </div>

                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="feature-title">Teknologi DeepSeek</h3>
                        <p class="feature-description">
                            Didukung oleh model AI canggih DeepSeek yang mampu memahami konteks 
                            dan memberikan jawaban akurat sesuai kebutuhan Anda.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="feature-title">Percakapan Natural</h3>
                        <p class="feature-description">
                            Berkomunikasi dengan AI menjadi lebih alami dan interaktif. 
                            Asisten kami mampu memahami bahasa sehari-hari dengan baik.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Aman & Privat</h3>
                        <p class="feature-description">
                            Keamanan data Anda adalah prioritas kami. Semua percakapan 
                            dienkripsi dan dilindungi dengan sistem keamanan terkini.
                        </p>
                    </div>
                </div>

                <div class="benefits">
                    <h2 class="benefits-title">Keunggulan Kami</h2>
                    <div class="benefits-grid">
                        <div class="benefit-item">
                            <i class="fas fa-clock benefit-icon"></i>
                            <span>Tersedia 24/7</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-bolt benefit-icon"></i>
                            <span>Respon Cepat</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-history benefit-icon"></i>
                            <span>Riwayat Chat Tersimpan</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-sync benefit-icon"></i>
                            <span>Update Berkala</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
