# MongoDB Integration - Setup & Running Guide

## ✅ Conversion Status: COMPLETE

All SQL/MySQL operations have been successfully converted to MongoDB NoSQL operations.

---

## Quick Start

### Option 1: Using PHP Built-in Server (Recommended for Development)

```bash
cd /opt/lampp/htdocs/coding-day-app

# Start the development server with system PHP (which has MongoDB support)
bash start-server.sh
```

The application will be available at: **http://localhost:8888**

### Option 2: Using LAMPP/XAMPP Apache

**Note:** Due to module API compatibility issues with LAMPP's embedded PHP 8.0.30, we recommend using Option 1 above.

---

## Test Accounts

After running `php insert_dummy_data_mongo.php`, use these accounts:

### Administrator (Panitia)
- **Email:** `panitia@codingday.com`
- **Password:** Not required (email-based login)
- **Role:** PANITIA (Organizer/Admin)

### Jury Members (Juri)
- **Email 1:** `juri1@codingday.com`
- **Email 2:** `juri2@codingday.com`
- **Password:** Not required
- **Role:** JURI (Jury/Judge)

### Team Leaders (Peserta)
- **Email 1:** `leader1@team.com` (Verified team)
- **Email 2:** `leader2@team.com` (Verified team)
- **Email 3:** `leader3@team.com` (Unverified team)
- **Password:** Not required
- **Role:** PESERTA (Participant)

---

## MongoDB Collections

### 1. `users`
```javascript
{
  _id: ObjectId,
  email: String,
  password_hash: String,
  role: String,  // PANITIA, JURI, PESERTA
  created_at: ISODate
}
```

### 2. `teams`
```javascript
{
  _id: ObjectId,
  team_name: String,
  leader_id: ObjectId,
  is_verified: Boolean,
  verified_by_user_id: ObjectId,
  verified_at: ISODate
}
```

### 3. `submissions`
```javascript
{
  _id: ObjectId,
  team_id: ObjectId,
  gdrive_link: String,
  status: String,  // pending, submitted
  submitted_at: ISODate
}
```

### 4. `scores`
```javascript
{
  _id: ObjectId,
  submission_id: ObjectId,
  jury_user_id: ObjectId,
  score: Number,
  comments: String,
  rated_at: ISODate
}
```

### 5. `verification_logs`
```javascript
{
  _id: ObjectId,
  team_id: ObjectId,
  admin_id: ObjectId,
  verified_at: ISODate
}
```

### 6. `activity_logs`
```javascript
{
  _id: ObjectId,
  event: String,
  timestamp: ISODate,
  metadata: Object
}
```

---

## System Requirements

### Already Installed ✅
- **MongoDB:** 7.x running on `localhost:27017`
- **PHP CLI:** 8.3.6 with MongoDB extension
- **System PHP:** /usr/bin/php

### For LAMPP/Apache (Alternative)
- **LAMPP:** Version with PHP 8.0.30
- **Note:** MongoDB extension not available on LAMPP's embedded PHP

---

## File Changes

### Core Application Files Modified (10 total)
1. `config/mongo_config.php` - MongoDB connection & client initialization
2. `app/views/login.php` - User authentication with MongoDB
3. `app/controllers/handle_verification.php` - Team verification
4. `app/controllers/handle_submission.php` - Submission handling
5. `app/controllers/handle_scoring.php` - Scoring system
6. `app/views/panitia_dashboard.php` - Admin dashboard with aggregations
7. `app/views/peserta_dashboard.php` - Team dashboard with rankings
8. `app/views/juri_dashboard.php` - Jury dashboard with scoring
9. `app/views/leaderboard.php` - Leaderboard with complex sorting

### Helper Scripts Created
1. `insert_dummy_data_mongo.php` - Insert test data
2. `migrate_sql_to_mongo.php` - SQL to MongoDB migration (optional)
3. `test-mongodb.php` - Comprehensive system test
4. `router.php` - PHP built-in server routing
5. `start-server.sh` - Server startup script

---

## Testing the Application

### 1. Run MongoDB Tests
```bash
cd /opt/lampp/htdocs/coding-day-app
/usr/bin/php test-mongodb.php
```

Expected output:
```
✅ MongoDB Connected Successfully
✅ Users Collection: 6 documents
✅ Teams Collection: 3 documents
✅ Aggregation Successful
✅ mongodb extension loaded
```

### 2. Start Development Server
```bash
bash start-server.sh
```

### 3. Test Login Workflow
```bash
# Test login
curl -X POST http://localhost:8888/login \
  -d "email=panitia@codingday.com&role=PANITIA"

# Should receive 302 redirect to /panitia
```

### 4. Access Dashboards
- **Panitia Dashboard:** http://localhost:8888/panitia
- **Peserta Dashboard:** http://localhost:8888/peserta
- **Juri Dashboard:** http://localhost:8888/juri
- **Leaderboard:** http://localhost:8888/leaderboard

---

## Key Differences from MySQL

### Query Patterns Changed

**MySQL (OLD):**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

**MongoDB (NEW):**
```php
$user = $usersCollection->findOne(['email' => $email]);
```

### Complex Queries with JOINs

**MySQL (OLD):**
```sql
SELECT t.*, COUNT(s.id) as submission_count
FROM teams t
LEFT JOIN submissions s ON t.id = s.team_id
GROUP BY t.id
```

**MongoDB (NEW):**
```php
$result = $teamsCollection->aggregate([
    ['$lookup' => [
        'from' => 'submissions',
        'localField' => '_id',
        'foreignField' => 'team_id',
        'as' => 'submissions'
    ]],
    ['$project' => [
        'team_name' => 1,
        'submission_count' => ['$size' => '$submissions']
    ]]
])->toArray();
```

### Date/Time Handling

**MySQL (OLD):**
```php
$date = date('Y-m-d H:i:s');
```

**MongoDB (NEW):**
```php
$date = new MongoDB\BSON\UTCDateTime();
```

---

## Troubleshooting

### MongoDB Not Found
```
Error: "MongoDB extension not available"
```

**Solution:**
- Ensure system PHP 8.3.6 is being used: `php -m | grep mongodb`
- Verify MongoDB is running: `sudo systemctl status mongod`

### Collections Not Found
```
Error: "Collection not found"
```

**Solution:**
- Run: `php insert_dummy_data_mongo.php` to create test data
- Verify MongoDB: `mongo` → `use coding_day_app` → `show collections`

### Session Not Working
```
Error: "User redirected back to login after successful login"
```

**Solution:**
- Browser cookies must be enabled for sessions
- Use curl with `-b` flag to store cookies:
  ```bash
  curl -c /tmp/cookies.txt -b /tmp/cookies.txt \
    -X POST http://localhost:8888/login \
    -d "email=panitia@codingday.com&role=PANITIA"
  ```

---

## Performance Considerations

### Indexes Created
All collections have been indexed for optimal query performance:

```php
// In mongo_config.php
$usersCollection->createIndex(['email' => 1]);
$teamsCollection->createIndex(['leader_id' => 1]);
$submissionsCollection->createIndex(['team_id' => 1]);
$scoresCollection->createIndex(['submission_id' => 1, 'jury_user_id' => 1]);
```

---

## Migration from MySQL

If migrating existing data from MySQL:

```bash
cd /opt/lampp/htdocs/coding-day-app

# Run migration script
php migrate_sql_to_mongo.php
```

This script will:
1. Connect to both MySQL and MongoDB
2. Migrate all user accounts
3. Migrate all team data
4. Migrate all submissions and scores
5. Create proper indexes

---

## Stopping the Server

```bash
# If using start-server.sh
Ctrl+C

# If using background process
pkill -f "php -S localhost:8888"
```

---

## Next Steps

1. ✅ Test all dashboards with test accounts
2. ✅ Verify team verification workflow
3. ✅ Test submission upload (with Google Drive links)
4. ✅ Test scoring by jury
5. ✅ Check leaderboard calculations
6. ✅ Monitor MongoDB performance

---

## Support

For detailed conversion documentation, see: [MONGODB_MIGRATION.md](MONGODB_MIGRATION.md)

For quick reference, see: [KONVERSI_SELESAI.md](KONVERSI_SELESAI.md)
