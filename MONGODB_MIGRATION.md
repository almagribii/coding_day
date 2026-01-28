# Migration Guide: MySQL to MongoDB

## Perubahan yang Telah Dilakukan

Aplikasi Coding Day telah sepenuhnya dikonversi dari MySQL (relational database) ke MongoDB (NoSQL document database).

### File yang Diubah

1. **Config Files:**
   - `config/mongo_config.php` - Konfigurasi MongoDB dengan semua collections
   
2. **Controllers:**
   - `app/controllers/handle_verification.php` - Verifikasi tim
   - `app/controllers/handle_submission.php` - Submit proyek
   - `app/controllers/handle_scoring.php` - Penilaian juri

3. **Views:**
   - `app/views/login.php` - Halaman login
   - `app/views/panitia_dashboard.php` - Dashboard panitia (CRUD tim)
   - `app/views/peserta_dashboard.php` - Dashboard peserta
   - `app/views/juri_dashboard.php` - Dashboard juri
   - `app/views/leaderboard.php` - Leaderboard dengan aggregation

4. **Migration Scripts:**
   - `migrate_sql_to_mongo.php` - Script migrasi data dari MySQL ke MongoDB
   - `insert_dummy_data_mongo.php` - Script insert dummy data untuk testing

## Perbedaan Utama SQL vs MongoDB

### 1. ID Fields
- **MySQL**: Auto-increment integer (1, 2, 3...)
- **MongoDB**: ObjectId (contoh: `507f1f77bcf86cd799439011`)

### 2. Date/Time
- **MySQL**: `DATETIME` / `TIMESTAMP` (string format)
- **MongoDB**: `UTCDateTime` object

### 3. Boolean
- **MySQL**: `TINYINT(1)` (0 atau 1)
- **MongoDB**: `true` / `false`

### 4. Relationships
- **MySQL**: Foreign keys dengan JOIN
- **MongoDB**: Aggregation pipeline dengan `$lookup`

### 5. NULL Values
- **MySQL**: Explicit NULL
- **MongoDB**: Field tidak ada atau null

## Collections Structure

### 1. users
```json
{
  "_id": ObjectId,
  "email": "user@example.com",
  "password_hash": "...",
  "role": "PESERTA|PANITIA|JURI",
  "created_at": UTCDateTime
}
```

### 2. teams
```json
{
  "_id": ObjectId,
  "team_name": "Team Name",
  "leader_id": ObjectId (references users),
  "is_verified": true/false,
  "verified_by_user_id": ObjectId (references users),
  "verified_at": UTCDateTime,
  "created_at": UTCDateTime
}
```

### 3. submissions
```json
{
  "_id": ObjectId,
  "team_id": ObjectId (references teams),
  "gdrive_link": "https://...",
  "status": "READY_TO_RATE|RATED",
  "submitted_at": UTCDateTime
}
```

### 4. scores
```json
{
  "_id": ObjectId,
  "submission_id": ObjectId (references submissions),
  "jury_user_id": ObjectId (references users),
  "score": 85,
  "comments": "Great work!",
  "rated_at": UTCDateTime
}
```

### 5. verification_logs
```json
{
  "_id": ObjectId,
  "team_id": ObjectId (references teams),
  "admin_id": ObjectId (references users),
  "verified_at": UTCDateTime
}
```

### 6. activity_logs
```json
{
  "_id": ObjectId,
  "event": "SUBMISSION_SENT|JURY_SCORING",
  "timestamp": UTCDateTime,
  // ... event specific fields
}
```

## Setup Instructions

### 1. Pastikan MongoDB Berjalan

```bash
# Cek status MongoDB
sudo systemctl status mongod

# Start MongoDB jika belum running
sudo systemctl start mongod

# Enable auto-start on boot
sudo systemctl enable mongod
```

### 2. Pastikan PHP MongoDB Extension Terinstall

```bash
# Install PHP MongoDB extension (jika belum)
sudo pecl install mongodb

# Atau via package manager
sudo apt-get install php-mongodb  # Ubuntu/Debian
sudo yum install php-mongodb      # CentOS/RHEL
```

Tambahkan ke php.ini:
```ini
extension=mongodb.so
```

Restart web server:
```bash
sudo systemctl restart apache2  # atau
sudo /opt/lampp/lampp restart   # untuk XAMPP
```

### 3. Verifikasi MongoDB Connection

```bash
# Test koneksi MongoDB
php -r "require 'vendor/autoload.php'; \$client = new MongoDB\Client('mongodb://localhost:27017'); echo 'Connected!';"
```

### 4. Migrasi Data (Jika Ada Data MySQL Existing)

Jika Anda sudah punya data di MySQL dan ingin migrasi ke MongoDB:

```bash
cd /opt/lampp/htdocs/coding-day-app
php migrate_sql_to_mongo.php
```

Script ini akan:
- Membaca semua data dari MySQL
- Mengkonversi ke format MongoDB
- Mapping foreign keys ke ObjectId
- Insert ke MongoDB collections

### 5. Insert Dummy Data (Untuk Testing)

Jika ingin mulai fresh dengan dummy data:

```bash
cd /opt/lampp/htdocs/coding-day-app
php insert_dummy_data_mongo.php
```

Script ini akan membuat:
- 1 Panitia account
- 2 Juri accounts
- 3 Team dengan leaders
- Submissions dan scores untuk testing

## Test Accounts (Setelah Insert Dummy Data)

### Panitia
- Email: `panitia@codingday.com`
- Password: `admin123`

### Juri
- Email: `juri1@codingday.com` / `juri2@codingday.com`
- Password: `juri123`

### Peserta
- Email: `leader1@team.com` (Code Warriors - Verified)
- Email: `leader2@team.com` (Ninja Coders - Verified)
- Email: `leader3@team.com` (Tech Innovators - Not Verified)
- Password: `password`

## MongoDB Operations Cheat Sheet

### View Data via MongoDB Shell

```bash
# Masuk ke mongo shell
mongo

# Switch to database
use coding_day

# View all users
db.users.find().pretty()

# View all teams
db.teams.find().pretty()

# Count documents
db.users.countDocuments()
db.teams.countDocuments()

# Find specific user
db.users.findOne({email: "panitia@codingday.com"})

# Clear all data (HATI-HATI!)
db.users.deleteMany({})
db.teams.deleteMany({})
db.submissions.deleteMany({})
db.scores.deleteMany({})
db.verification_logs.deleteMany({})
db.activity_logs.deleteMany({})
```

### MongoDB Compass (GUI Tool)

Download dan install MongoDB Compass untuk GUI management:
https://www.mongodb.com/products/compass

Connection string:
```
mongodb://localhost:27017
```

## Troubleshooting

### Error: MongoDB extension not available
```bash
# Install extension
sudo pecl install mongodb

# Add to php.ini
echo "extension=mongodb.so" | sudo tee -a /etc/php/7.4/apache2/php.ini

# Restart Apache
sudo systemctl restart apache2
```

### Error: Failed to connect to MongoDB
```bash
# Check if MongoDB is running
sudo systemctl status mongod

# Check MongoDB logs
sudo tail -f /var/log/mongodb/mongod.log

# Check if port 27017 is listening
sudo netstat -tulpn | grep 27017
```

### Error: Class 'MongoDB\Client' not found
```bash
# Install MongoDB PHP Library via Composer
cd /opt/lampp/htdocs/coding-day-app
composer require mongodb/mongodb
```

### Error: Collection not found
```bash
# MongoDB automatically creates collections when you insert
# But you can manually create:
mongo
use coding_day
db.createCollection("users")
```

## Performance Tips

### Indexes
The application automatically creates indexes on:
- `users.email` (unique)
- `teams.leader_id`
- `teams.is_verified`
- `submissions.team_id`
- `scores.submission_id`
- `scores.jury_user_id`

To view indexes:
```javascript
db.users.getIndexes()
```

### Aggregation Pipeline
Leaderboard menggunakan aggregation pipeline yang kompleks dengan `$lookup`, `$unwind`, dan `$sort` untuk menghitung rata-rata scores.

## Rollback ke MySQL

Jika ingin kembali ke MySQL:

1. File-file SQL masih ada di folder `migrations/`
2. Restore `db_config.php` di semua file yang menggunakannya
3. Restore versi SQL dari git history jika diperlukan

## Next Steps

1. ✅ Konversi semua SQL queries ke MongoDB - **DONE**
2. ✅ Buat migration scripts - **DONE**
3. ✅ Test semua fitur dengan MongoDB - **PERLU TESTING**
4. 🔄 Deploy ke production (jika perlu)
5. 🔄 Monitor performance MongoDB vs MySQL

## Notes

- MongoDB tidak memerlukan schema definition seperti SQL migrations
- Collections dan documents dibuat otomatis saat insert pertama
- Backup regular menggunakan `mongodump` / `mongorestore`
- Consider MongoDB Atlas untuk cloud hosting production

---

**Migration Date:** January 28, 2026  
**Original Database:** MySQL  
**Target Database:** MongoDB 7.x  
**PHP Version:** 7.4+  
**MongoDB PHP Extension:** 1.x  
**MongoDB PHP Library:** 1.x
