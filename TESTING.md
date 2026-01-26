# 🧪 TESTING GUIDE - Coding Day 2026

Panduan lengkap untuk testing semua fitur aplikasi.

## 🚀 Quick Start

```bash
# 1. Start LAMPP
sudo /opt/lampp/lampp start

# 2. Buka browser
# http://localhost/coding-day-app/

# 3. Login dengan salah satu akun
# Email: peserta@test.com, panitia@test.com, atau juri@test.com
# Password: password
```

---

## 📋 Testing Scenarios

### 1️⃣ Test Panitia Dashboard (Admin)

**Setup:**
- Login dengan: `panitia@test.com` / `password`

**Test Cases:**
- [ ] Dashboard memuat dengan benar
- [ ] Statistik card menampilkan angka yang benar:
  - Total Tim
  - Tim Terverifikasi
  - Tim Pending
  - Total Submission
- [ ] Form "Tambah Tim" berfungsi:
  - [ ] Input nama tim dan email valid
  - [ ] Klik tombol "Tambah"
  - [ ] Berhasil menambah tim (cek di tabel)
- [ ] Tabel tim menampilkan:
  - [ ] ID, Nama Tim, Email Leader
  - [ ] Status Verification (VERIFIED/PENDING)
  - [ ] Submission count
- [ ] Tombol "Verifikasi" berfungsi:
  - [ ] Klik tombol untuk tim yang belum verified
  - [ ] Status berubah menjadi "VERIFIED"
  - [ ] Tombol berubah menjadi disabled

---

### 2️⃣ Test Peserta Dashboard

**Setup:**
- Login dengan: `peserta@test.com` / `password`
- Pastikan tim sudah diverifikasi oleh panitia

**Test Cases:**
- [ ] Dashboard memuat dengan benar
- [ ] Statistik cards menampilkan:
  - [ ] Total Submissions
  - [ ] Rata-rata Nilai
  - [ ] Penilaian Masuk
  - [ ] Status Verifikasi
- [ ] Form upload submission tampil (jika verified)
- [ ] Upload submission:
  - [ ] Input Google Drive link (harus public)
  - [ ] Klik "Submit Proyek"
  - [ ] Berhasil submit (lihat di history)
- [ ] Riwayat submission menampilkan:
  - [ ] Tanggal submit
  - [ ] Link ke proyek
  - [ ] Status submission
- [ ] Panel nilai menampilkan:
  - [ ] Nilai dari juri
  - [ ] Komentar dari juri
  - [ ] Tanggal penilaian

---

### 3️⃣ Test Juri Dashboard

**Setup:**
- Login dengan: `juri@test.com` / `password`
- Pastikan ada submission yang status `READY_TO_RATE`

**Test Cases:**
- [ ] Dashboard memuat dengan benar
- [ ] Statistik menampilkan:
  - [ ] Total Submission
  - [ ] Sudah Dinilai
  - [ ] Pending
- [ ] Daftar submission menampilkan:
  - [ ] Nama tim
  - [ ] Tanggal submit
  - [ ] Status (Menunggu Penilaian / Sudah Dinilai)
  - [ ] Tombol "Buka Proyek"
- [ ] Form scoring berfungsi:
  - [ ] Input nilai (0-100)
  - [ ] Input komentar
  - [ ] Klik "Simpan"
  - [ ] Berhasil menyimpan
  - [ ] Form berubah menjadi "Update"
- [ ] Update penilaian:
  - [ ] Ubah nilai
  - [ ] Ubah komentar
  - [ ] Klik "Update"
  - [ ] Berhasil update

---

### 4️⃣ Test Leaderboard

**Setup:**
- Tidak perlu login
- URL: `http://localhost/coding-day-app/leaderboard.php`

**Test Cases:**
- [ ] Leaderboard menampilkan:
  - [ ] Ranking tim
  - [ ] Badges untuk top 3 (🥇🥈🥉)
  - [ ] Rata-rata nilai
  - [ ] Max dan min score
  - [ ] Total submission dan ratings
- [ ] Tim tanpa rating menampilkan:
  - [ ] Posisi dengan "—"
  - [ ] Badge "Menunggu penilaian..."
- [ ] Sorting bekerja:
  - [ ] Tim dengan score lebih tinggi di atas
  - [ ] Tim tanpa score di bawah

---

### 5️⃣ Test Authentication

**Test Cases:**
- [ ] Login dengan akun yang benar
  - [ ] Email: `peserta@test.com`, Password: `password`
  - [ ] Berhasil login → redirect ke peserta_dashboard
- [ ] Login dengan akun yang salah
  - [ ] Email: `peserta@test.com`, Password: `salah`
  - [ ] Error message muncul
- [ ] Logout berfungsi
  - [ ] Klik logout button
  - [ ] Redirect ke halaman utama
  - [ ] Session cleared
- [ ] Protected pages
  - [ ] Akses dashboard tanpa login → redirect ke login
  - [ ] Akses peserta_dashboard dengan akun juri → error atau redirect

---

### 6️⃣ Test UI/UX

**Visual Test:**
- [ ] Dark theme konsisten di semua halaman
- [ ] Icons muncul dengan benar
- [ ] Animations smooth dan tidak lag
- [ ] Responsive di mobile (buka dengan device tool)
- [ ] Cards hover effects berfungsi
- [ ] Buttons states (hover, active, disabled) bekerja

**Navigation Test:**
- [ ] Navbar muncul di semua halaman
- [ ] Leaderboard link accessible dari navbar
- [ ] Logo clickable → home
- [ ] User info menampilkan email dan role

---

### 7️⃣ Test Form Validation

**Test Cases:**
- [ ] Submission form:
  - [ ] Submit tanpa link → error
  - [ ] Submit dengan link invalid → error
  - [ ] Submit dengan link valid → success
- [ ] Scoring form:
  - [ ] Submit tanpa nilai → error
  - [ ] Submit dengan nilai > 100 → error
  - [ ] Submit dengan nilai < 0 → error
  - [ ] Submit dengan nilai valid → success

---

### 8️⃣ Test Database Integrity

**Check:**
- [ ] Data konsisten antara semua dashboard
- [ ] Update di satu tempat reflected di tempat lain
- [ ] No duplicate submissions
- [ ] Foreign key constraints work (tidak bisa delete team dengan submission)

**Query untuk cek:**
```sql
-- Cek teams
SELECT * FROM teams;

-- Cek submissions
SELECT s.*, t.team_name FROM submissions s JOIN teams t ON s.team_id = t.id;

-- Cek scores
SELECT sc.*, s.id as submission_id FROM scores sc JOIN submissions s ON sc.submission_id = s.id;
```

---

## ✅ Final Checklist

- [ ] Semua akun bisa login
- [ ] Panitia bisa verifikasi tim
- [ ] Peserta bisa submit proyek
- [ ] Juri bisa beri nilai
- [ ] Leaderboard menampilkan ranking yang benar
- [ ] Logout berfungsi
- [ ] UI responsive dan smooth
- [ ] No console errors
- [ ] No database errors

---

## 🐛 Troubleshooting

### Login Error
```
Error: Access denied for user 'root'@'localhost'
```
**Solution:** Update password di `includes/db_config.php`

### Submission tidak muncul di Juri
```
Status submission harus READY_TO_RATE atau RATED
```
**Solution:** Check di database:
```sql
UPDATE submissions SET status = 'READY_TO_RATE' WHERE status = 'SENT';
```

### Styling tidak muncul
```
CSS file tidak load
```
**Solution:** Pastikan path relatif ke assets/css/style.css benar

---

## 📊 Test Data yang Ada

```sql
-- Teams
1. Tim Beta Pending (verified)
2. alansin7 (verified)

-- Submissions
1. Tim Beta Pending - READY_TO_RATE
2. alansin7 - READY_TO_RATE

-- Users
1. peserta@test.com (PESERTA)
2. panitia@test.com (PANITIA)
3. juri@test.com (JURI)
```

---

## 📝 Notes

- Semua password: `password`
- Default juri_id untuk testing: 3 (juri@test.com)
- Google Drive links harus public untuk bisa diakses
- Log files ada di MongoDB (jika available)

---

**Happy Testing! 🎉**
