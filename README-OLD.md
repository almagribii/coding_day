# 🏆 CODING DAY 2026 - Competition Management System

Platform manajemen kompetisi pemrograman dengan sistem penilaian terintegrasi.

## 🚀 Fitur Utama

### ✅ Yang Sudah Diimplementasikan
- ✨ **Modern UI/UX** - Dark theme dengan animasi smooth
- 🔐 **Authentication System** - Login dengan session management
- 👥 **Role-based Access** - Peserta, Panitia, dan Juri
- 📊 **Dashboard Interaktif** - Statistik real-time
- 📤 **Submission System** - Upload proyek ke Google Drive
- ⭐ **Scoring System** - Penilaian oleh juri
- 🏅 **Leaderboard** - Ranking tim berdasarkan nilai rata-rata
- ✅ **Team Verification** - Verifikasi tim oleh panitia
- 📝 **Logging** - MongoDB untuk activity logs
- 🎨 **Responsive Design** - Mobile-friendly

## 📁 Struktur Folder

```
coding-day-app/
├── assets/                     # Static files
│   ├── css/
│   │   └── style.css          # Modern dark theme styles
│   ├── js/
│   │   └── main.js            # JavaScript utilities & animations
│   └── img/                    # Images (jika ada)
├── db/                         # Database files
│   ├── 01_schema.sql          # Database schema
│   ├── 02_procs_triggers.sql  # Stored procedures & triggers
│   └── 03_dummy_users.sql     # Dummy users untuk testing
├── includes/                   # PHP includes & configs
│   ├── auth.php               # Authentication functions
│   ├── db_config.php          # MySQL configuration
│   ├── header.php             # Header template
│   └── mongo_config.php       # MongoDB configuration
├── legacy/                     # Old/deprecated files
│   ├── handle_stats.php
│   ├── tes_koneksi_mongo.php
│   ├── test_db.php
│   ├── view_logs.php
│   └── view_mongo_logs.php
├── vendor/                     # Composer dependencies
├── .gitignore                  # Git ignore rules
├── composer.json              # PHP dependencies
├── README.md                   # Dokumentasi ini
│
├── 📄 Main Pages
├── index.php                   # Landing page
├── login.php                   # Login page
├── leaderboard.php             # Public leaderboard
│
├── 🔒 Dashboard Pages (Role-based)
├── peserta_dashboard.php       # Dashboard peserta/challenger
├── panitia_dashboard.php       # Dashboard panitia/admin
├── juri_dashboard.php          # Dashboard juri/judge
│
├── 🔧 API/Handler Pages
├── handle_submission.php       # Handle team submission upload
├── handle_verification.php     # Handle team verification
└── handle_scoring.php          # Handle jury scoring
```

## 🔧 Cara Instalasi

### 1. Prerequisites
- XAMPP/LAMPP dengan PHP 7.4+
- MySQL 5.7+
- MongoDB (opsional, untuk logging)

### 2. Database Setup

```bash
# Start LAMPP
sudo /opt/lampp/lampp start

# Import database schema
/opt/lampp/bin/mysql -u root -pxampp < db/01_schema.sql
/opt/lampp/bin/mysql -u root -pxampp coding_day < db/02_procs_triggers.sql
/opt/lampp/bin/mysql -u root -pxampp coding_day < db/03_dummy_users.sql
```

### 3. Konfigurasi Database

Edit `includes/db_config.php` jika perlu:
```php
$host = 'localhost';
$db   = 'coding_day';
$user = 'root';
$pass = 'xampp'; // Sesuaikan dengan password MySQL Anda
```

### 4. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/coding-day-app/
```

## 👤 Akun Testing

**Semua password: `password`**

| Role     | Email                | Status            |
|----------|---------------------|-------------------|
| Peserta  | peserta@test.com    | Ready to use      |
| Panitia  | panitia@test.com    | Ready to use      |
| Juri     | juri@test.com       | Ready to use      |

**Catatan:** Gunakan akun Panitia terlebih dahulu untuk verifikasi tim peserta.

## 📖 Cara Penggunaan

### Workflow Rekomendasi

1. **Login as Panitia** (panitia@test.com)
   - Lihat tim yang ada atau tambah tim baru
   - Verifikasi tim peserta
   - Monitor statistik

2. **Login as Peserta** (peserta@test.com)
   - Tunggu verifikasi dari panitia
   - Setelah verified, upload proyek
   - Monitor nilai dan ranking

3. **Login as Juri** (juri@test.com)
   - Lihat daftar submission
   - Buka dan review proyek
   - Berikan nilai dan komentar

4. **View Leaderboard**
   - Akses `/leaderboard.php` untuk melihat ranking

### Untuk Peserta (Challenger)
1. Login dengan akun peserta
2. Tunggu verifikasi dari panitia
3. Setelah verified, upload proyek ke Google Drive
4. Submit link Google Drive di form submission
5. Lihat nilai dan ranking di leaderboard

### Untuk Panitia (Admin)
1. Login dengan akun panitia
2. Tambah tim baru atau kelola tim existing
3. Verifikasi tim peserta dengan tombol "Verifikasi"
4. Monitor statistik kompetisi (Total Tim, Verified, Pending)
5. Refresh halaman untuk update data real-time

### Untuk Juri (Judge)
1. Login dengan akun juri
2. Lihat daftar submission yang perlu dinilai
3. Klik "Buka Proyek" untuk review di Google Drive
4. Berikan nilai (0-100) dan komentar
5. Klik "Simpan" untuk submit penilaian
6. Untuk update nilai, ubah nilai dan klik "Update"

## 🎨 Teknologi yang Digunakan

- **Frontend**: Bootstrap 5, Bootstrap Icons, Google Fonts
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Logging**: MongoDB (optional)
- **CSS Framework**: Custom dark theme inspired by GitHub
- **JavaScript**: Vanilla JS dengan utilities modern

## 🔒 Keamanan

- ✅ Session-based authentication
- ✅ Password hashing dengan bcrypt
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ CSRF protection ready
- ✅ Input val Penting

- ✅ Pastikan Google Drive link yang disubmit bersifat **public** (Anyone with the link can view)
- ✅ Password default untuk semua akun testing adalah `password`
- ✅ MongoDB logging bersifat optional, aplikasi tetap berjalan tanpa MongoDB
- ✅ Submission otomatis akan berubah status ke `READY_TO_RATE` saat peserta submit
- ✅ Juri dapat melihat **semua submission yang ada**, bukan hanya submission dari tim tertentu
- ✅ File lama/deprecated sudah dipindahkan ke folder `legacy/`
- ✅ Leaderboard menampilkan tim yang verified saja
- ✅ Juri dapat update penilaian kapan saja sebelum acara berakhir
- Stored Procedures untuk counting
- Triggers untuk logging verifikasi
- Foreign keys untuk data integrity
- Indexes untuk performa query

## 🎯 Roadmap (Future Enhancements)

- [ ] Real-time notifications
- [ ] Email notifications
- [ ] File upload langsung (tidak hanya link)
- [ ] Multi-criteria scoring
- [ ] Team collaboration features
- [ ] Export data ke Excel/PDF
- [ ] Advanced analytics dashboard
- [ ] API endpoints
- [ ] Mobile app

## 📝 Catatan

- Pastikan Google Drive link yang disubmit bersifat **public** (Anyone with the link can view)
- Password default untuk semua akun testing adalah `password`
- MongoDB logging bersifat optional, aplikasi tetap berjalan tanpa MongoDB

## 🐛 Troubleshooting

### Error: Access denied for user 'root'@'localhost'
```bash
# Update password di db_config.php sesuai dengan MySQL password Anda
```

### Error: Database tidak ditemukan
```bash
# Import ulang schema database
/opt/lampp/bin/mysql -u root -pxampp < db/01_schema.sql
```

### UI tidak muncul dengan benar
```bash
# Pastikan folder assets/ readable
chmod -R 755 assets/
```

## 👨‍💻 Developer

Dikembangkan untuk Coding Day 2026 Competition

## 📄 License

MIT License - Feel free to use and modify

---

**Happy Coding! 🚀**
