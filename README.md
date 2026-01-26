# 🚀 Coding Day 2026 - Competition Management System

Aplikasi modern untuk mengelola kompetisi pemrograman dengan dark theme UI yang elegant dan clean code structure.

## ✨ Highlights

- 🎨 **Modern Dark Theme** - Inspired by GitHub dark mode
- 👥 **Multi-Role System** - Peserta, Panitia, Juri with different access levels  
- 📊 **Real-time Leaderboard** - Automatic ranking dengan medal system (🥇🥈🥉)
- 🔐 **Secure Authentication** - Session-based with bcrypt hashing
- 📱 **Fully Responsive** - Mobile-friendly design
- 🗂️ **Clean MVC Architecture** - Organized folder structure

## 📁 Project Structure

```
coding-day-app/
├── app/
│   ├── controllers/         # POST request handlers
│   ├── views/               # Dashboard pages (PHP templates)
│   └── models/              # Future: database models
├── config/                  # Configuration & authentication
├── public/                  # CSS, JS, and static assets
├── migrations/              # Database schemas & seeds
├── legacy/                  # Deprecated files
└── index.php                # Main router & entry point
```

## 🚀 Quick Start

### 1. Setup Database

```bash
# Start LAMPP
sudo /opt/lampp/lampp start

# Import database
mysql -u root -pxampp < migrations/01_schema.sql
mysql -u root -pxampp coding_day < migrations/02_procs_triggers.sql
mysql -u root -pxampp coding_day < migrations/03_dummy_users.sql
```

### 2. Access Application

Open browser: **http://localhost/coding-day-app/**

### 3. Test Login

| Role | Email | Password |
|------|-------|----------|
| Peserta | peserta@test.com | password |
| Panitia | panitia@test.com | password |
| Juri | juri@test.com | password |

## 🔀 URL Routes

| URL | Description |
|-----|-------------|
| `/` | Landing page dengan countdown |
| `/login` | Authentication page |
| `/peserta` | Dashboard peserta (submit solutions) |
| `/panitia` | Dashboard admin (manage teams) |
| `/juri` | Dashboard juri (scoring submissions) |
| `/leaderboard` | Public ranking display |

## 🎨 Color Fixes Applied

✅ **Fixed black text on dark background issues:**
- Button colors: Changed from white bg + black text → blue gradient + white text
- Warning badges: Changed from `bg-warning text-dark` → custom orange bg + white text
- Leaderboard medals: Updated gold/silver text for better contrast
- All forms and inputs: Proper dark theme colors

## 📚 Documentation

- [README.md](README.md) - This file
- [STRUCTURE.md](STRUCTURE.md) - Detailed folder structure explanation
- [TESTING.md](TESTING.md) - Testing guide dengan scenarios
- [FIXES.md](FIXES.md) - Bug fixes history

## 🔧 Configuration

### Database Connection
File: `config/db_config.php`
```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=coding_day;charset=utf8mb4',
    'root',
    'xampp'
);
```

### Authentication
File: `config/auth.php`
- `requireLogin()` - Protect pages
- `requireRole($role)` - Check specific role
- `loginUser($email, $role)` - Set session
- `logoutUser()` - Clear session

## 📦 Git Workflow

```bash
# Check status
git status

# Stage changes
git add .

# Commit dengan pesan jelas
git commit -m "feat: Add new feature"
# atau
git commit -m "fix: Resolve bug issue"

# Push ke remote
git push origin main
```

### Recent Commits

```
a8f89cd feat: Add .htaccess for clean URLs + fix all header includes
8054cec fix: Use absolute paths for require statements
3bb07ff fix: Update all paths and routes to match new MVC structure
1a25eac refactor: Reorganize files into clean MVC structure + fix dark theme text colors
```

## 🐛 Known Issues & Solutions

### Issue 1: Black text on dark background
**Status:** ✅ FIXED
**Solution:** Updated all color variables and inline styles

### Issue 2: File structure messy
**Status:** ✅ FIXED  
**Solution:** Reorganized into proper MVC structure

### Issue 3: Routes not working
**Status:** ✅ FIXED
**Solution:** Added .htaccess for URL rewriting

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'feat: Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is for educational purposes - Coding Day 2026 event.

---

**Built with ❤️ for Coding Day 2026**
