# User Login Guide - Coding Day 2026

## Sistem Login
Sistem login menggunakan **Passwordless Authentication** dengan kombinasi:
- **Email** (wajib diisi)
- **Role** (pilih dari dropdown)
- **Tanpa Password** (tidak ada password yang diperlukan)

## Real Users untuk Testing

### 1. **PANITIA (Penyelenggara)**
| Email | Role |
|-------|------|
| `panitia@comp.com` | PANITIA |
| `panitia@test.com` | PANITIA |

**Akses ke:** Panitia Dashboard - verifikasi tim, lihat statistik keseluruhan

---

### 2. **PESERTA (Peserta Coding)**
| Email | Role |
|-------|------|
| `leader@teamalpha.com` | PESERTA |
| `alansin@gmail.com` | PESERTA |
| `alanganteng@gmail.com` | PESERTA |
| `alansin4@gmail.com` | PESERTA |
| `peserta@test.com` | PESERTA |

**Akses ke:** Peserta Dashboard - submit solusi, lihat skor

---

### 3. **JURI (Penilai)**
| Email | Role |
|-------|------|
| `juri@comp.com` | JURI |
| `juri@test.com` | JURI |

**Akses ke:** Juri Dashboard - lihat submission, beri penilaian

---

## Cara Login

1. Buka halaman login: `http://localhost/coding-day-app/login`
2. Masukkan **Email** dari user yang ingin digunakan
3. Pilih **Role** sesuai dengan role user tersebut
4. Klik tombol **Login**
5. Sistem akan mengarahkan ke dashboard sesuai role

---

## Catatan Penting

✅ **Apa yang sudah diimplementasikan:**
- Passwordless login (email + role saja)
- Validasi user dari database
- Redirect otomatis ke dashboard sesuai role
- Session management
- Logout functionality

✅ **Data asli (bukan dummy):**
- Semua user yang terdaftar di database adalah user asli
- Tidak ada dummy data yang diciptakan saat migration
- Siap untuk production testing

---

## Struktur Database

```sql
-- Table users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash CHAR(60) NULL,  -- NULL karena tidak pakai password
    role ENUM('PANITIA', 'PESERTA', 'JURI') NOT NULL DEFAULT 'PESERTA'
);
```

---

## File Terkait

- Login View: `app/views/login.php`
- Auth Functions: `config/auth.php`
- Database Config: `config/db_config.php`
- Schema: `migrations/01_schema.sql`

