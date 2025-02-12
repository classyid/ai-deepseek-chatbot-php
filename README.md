# DeepSeek AI Chat Assistant

![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue)
![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)

Aplikasi chat AI berbasis web yang dikembangkan menggunakan PHP dan mengintegrasikan model DeepSeek untuk memberikan respons cerdas dan akurat.

## 🌟 Fitur Utama

### 1. Sistem Autentikasi Lengkap
- Registrasi pengguna dengan validasi
- Login dengan sistem keamanan berlapis
- Manajemen sesi pengguna
- Logout secure

### 2. Landing Page Modern
- Desain responsif dan menarik
- Animasi interaktif
- Penjelasan fitur komprehensif
- Call-to-action yang jelas

### 3. Chat Interface
- Antarmuka chat real-time
- Riwayat percakapan tersimpan
- Dukungan format pesan beragam
- Indikator status pengiriman

### 4. Integrasi DeepSeek
- Konfigurasi model AI yang fleksibel
- Parameter respons yang dapat disesuaikan
- Pemrosesan bahasa natural
- Respons cepat dan akurat

### 5. Manajemen Percakapan
- Penyimpanan riwayat chat
- Organisasi percakapan per pengguna


## 📋 Persyaratan Sistem

- PHP 8.3 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Ekstensi PHP: mysqli, curl, json
- Akses ke Ollama server dengan model DeepSeek

## 🚀 Instalasi

1. Clone repository:
```bash
git clone https://github.com/classyid/ai-deepseek-chatbot-php.git
```

2. Import database:
```bash
mysql -u username -p database_name < database.sql
```

3. Konfigurasi koneksi:
   - Salin `config.example.php` ke `config.php`
   - Sesuaikan pengaturan database dan Ollama


## 💻 Penggunaan

1. Akses aplikasi melalui browser
2. Daftar akun baru atau login
3. Mulai percakapan dengan AI
4. Akses riwayat chat di sidebar

## 🔧 Konfigurasi
### Pengaturan Model AI
```php
define('OLLAMA_MODEL', 'deepseek-r1');
define('OLLAMA_TEMPERATURE', 0.7);
define('OLLAMA_TOP_K', 40);
define('OLLAMA_TOP_P', 0.95);
```

### Keamanan
- Validasi input
- Prepared statements
- XSS protection
- CSRF protection
- Session management

## 🤝 Kontribusi

Kami sangat menghargai kontribusi! Silakan:
1. Fork repository
2. Buat branch fitur (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -am 'Menambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## 📝 Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT - lihat file [LICENSE](LICENSE) untuk detail.

## 📞 Dukungan

Jika Anda menemukan masalah atau memiliki pertanyaan:
- Buat issue di GitHub
- Email: kontak@classy.id
