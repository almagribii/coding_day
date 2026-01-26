# 📁 Struktur Folder Project

```
coding-day-app/
├── app/                          # Logika aplikasi
│   ├── controllers/              # Handler untuk POST/API requests
│   │   ├── handle_submission.php # Submit team solutions
│   │   ├── handle_verification.php # Verify teams
│   │   ├── handle_scoring.php    # Save jury scores
│   │   └── handle_stats.php      # Get statistics
│   ├── models/                   # Database models (future expansion)
│   └── views/                    # Halaman dan dashboard
│       ├── index.php             # Landing page dengan countdown
│       ├── login.php             # Authentication
│       ├── peserta_dashboard.php # Dashboard peserta
│       ├── panitia_dashboard.php # Dashboard admin organizer
│       ├── juri_dashboard.php    # Dashboard judge scoring
│       └── leaderboard.php       # Public ranking
│
├── config/                       # Konfigurasi
│   ├── db_config.php            # Database connection (PDO)
│   ├── mongo_config.php         # MongoDB logging (optional)
│   ├── auth.php                 # Authentication functions
│   └── header.php               # Navbar template
│
├── public/                       # Static files (akses dari browser)
│   ├── css/                      # Stylesheets
│   │   └── style.css            # Dark theme styling
│   ├── js/                       # JavaScript
│   │   └── main.js              # Utilities & interactions
│   └── fonts/                    # Web fonts (future)
│
├── migrations/                   # Database schema & seeds
│   ├── 01_schema.sql            # Table structure
│   ├── 02_procs_triggers.sql    # Stored procedures
│   └── 03_dummy_users.sql       # Test data
│
├── assets/                       # Media assets (images, etc)
│   └── img/                      # Images
│
├── vendor/                       # Composer dependencies
│   ├── composer/
│   ├── mongodb/                  # MongoDB driver
│   ├── psr/                      # PSR logging
│   └── symfony/                  # Polyfill libraries
│
├── logs/                         # Application logs
│
├── legacy/                       # Old/deprecated files
│
├── index.php                     # ⭐ Entry point untuk semua routes
├── composer.json                 # PHP dependencies
├── .gitignore                    # Git ignore rules
├── README.md                     # Setup instructions
├── TESTING.md                    # Testing guide
└── FIXES.md                      # Bug fixes log
```

## 🔀 URL Routing

Entry point `index.php` menangani semua routes:

| URL | File | Deskripsi |
|-----|------|-----------|
| `/coding-day-app/` | `app/views/index.php` | Landing page |
| `/coding-day-app/login` | `app/views/login.php` | Login |
| `/coding-day-app/peserta` | `app/views/peserta_dashboard.php` | Dashboard peserta |
| `/coding-day-app/panitia` | `app/views/panitia_dashboard.php` | Dashboard panitia |
| `/coding-day-app/juri` | `app/views/juri_dashboard.php` | Dashboard juri |
| `/coding-day-app/leaderboard` | `app/views/leaderboard.php` | Leaderboard publik |
| `/coding-day-app/submit` | `app/controllers/handle_submission.php` | Submit solution |
| `/coding-day-app/verify` | `app/controllers/handle_verification.php` | Verify team |
| `/coding-day-app/score` | `app/controllers/handle_scoring.php` | Submit score |

## 💾 Database Connection

File: `config/db_config.php`
```php
// PDO connection string
$pdo = new PDO('mysql:host=localhost;dbname=coding_day;charset=utf8mb4', 'root', 'xampp');
```

## 🔐 Authentication

File: `config/auth.php`
- `requireLogin()` - Redirect ke login jika belum authenticated
- `requireRole($role)` - Check role access (PESERTA, PANITIA, JURI)
- `loginUser($email, $role)` - Set session
- `logoutUser()` - Clear session

## 📦 Git Workflow

```bash
# Initial commit (sudah dilakukan)
git add .
git commit -m "Initial commit: Modern UI with dark theme"

# Update struktur
git add .
git commit -m "Refactor: Organize files into MVC structure"

# New feature
git add app/
git commit -m "feat: Add new feature"

# Bug fix
git add app/
git commit -m "fix: Resolve issue"
```

## ⚙️ Asset Loading

Dalam template PHP, reference assets dengan:
```html
<!-- CSS -->
<link rel="stylesheet" href="/coding-day-app/public/css/style.css">

<!-- JavaScript -->
<script src="/coding-day-app/public/js/main.js"></script>
```

## 🚀 Development Best Practices

1. **Controllers** - Hanya handle POST requests, validation, dan database
2. **Views** - Display data, form UI, tidak ada business logic
3. **Config** - Konfigurasi global, jangan ubah saat production
4. **Assets** - Hanya static files, bisa di-cache oleh browser
5. **Migrations** - Dokumentasi perubahan schema database

---

**Created:** January 26, 2026
