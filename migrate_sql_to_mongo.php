<?php
/**
 * Migration Script: MySQL to MongoDB
 * 
 * Script ini akan memigrasikan semua data dari MySQL ke MongoDB
 * Jalankan script ini satu kali saja setelah MongoDB siap
 * 
 * Usage: php migrate_sql_to_mongo.php
 */

require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/config/mongo_config.php';

echo "=== MIGRATION: MySQL to MongoDB ===\n\n";

try {
    // 1. Migrate Users
    echo "1. Migrating users...\n";
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $userIdMap = []; // Map SQL ID to MongoDB ObjectId
    
    foreach ($users as $user) {
        $mongoUser = [
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'role' => $user['role'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $usersCollection->insertOne($mongoUser);
        $userIdMap[$user['id']] = $result->getInsertedId();
        
        echo "  ✓ User migrated: {$user['email']} (Role: {$user['role']})\n";
    }
    
    echo "  Total users migrated: " . count($users) . "\n\n";
    
    // 2. Migrate Teams
    echo "2. Migrating teams...\n";
    $stmt = $pdo->query("SELECT * FROM teams ORDER BY id ASC");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $teamIdMap = []; // Map SQL ID to MongoDB ObjectId
    
    foreach ($teams as $team) {
        $mongoTeam = [
            'team_name' => $team['team_name'],
            'leader_id' => $userIdMap[$team['leader_id']],
            'is_verified' => (bool)$team['is_verified'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        // Add verified_by if exists
        if ($team['verified_by_user_id']) {
            $mongoTeam['verified_by_user_id'] = $userIdMap[$team['verified_by_user_id']];
            $mongoTeam['verified_at'] = new MongoDB\BSON\UTCDateTime();
        }
        
        $result = $teamsCollection->insertOne($mongoTeam);
        $teamIdMap[$team['id']] = $result->getInsertedId();
        
        echo "  ✓ Team migrated: {$team['team_name']}\n";
    }
    
    echo "  Total teams migrated: " . count($teams) . "\n\n";
    
    // 3. Migrate Verification Logs
    echo "3. Migrating verification logs...\n";
    $stmt = $pdo->query("SELECT * FROM verification_logs ORDER BY id ASC");
    $verificationLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($verificationLogs as $log) {
        $mongoLog = [
            'team_id' => $teamIdMap[$log['team_id']],
            'admin_id' => $userIdMap[$log['admin_id']],
            'verified_at' => new MongoDB\BSON\UTCDateTime(strtotime($log['verified_at']) * 1000)
        ];
        
        $verificationLogsCollection->insertOne($mongoLog);
        echo "  ✓ Verification log migrated for team ID: {$log['team_id']}\n";
    }
    
    echo "  Total verification logs migrated: " . count($verificationLogs) . "\n\n";
    
    // 4. Migrate Submissions
    echo "4. Migrating submissions...\n";
    $stmt = $pdo->query("SELECT * FROM submissions ORDER BY id ASC");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $submissionIdMap = []; // Map SQL ID to MongoDB ObjectId
    
    foreach ($submissions as $submission) {
        $mongoSubmission = [
            'team_id' => $teamIdMap[$submission['team_id']],
            'gdrive_link' => $submission['gdrive_link'],
            'status' => $submission['status'],
            'submitted_at' => new MongoDB\BSON\UTCDateTime(strtotime($submission['submitted_at']) * 1000)
        ];
        
        $result = $submissionsCollection->insertOne($mongoSubmission);
        $submissionIdMap[$submission['id']] = $result->getInsertedId();
        
        echo "  ✓ Submission migrated: ID {$submission['id']} for team {$submission['team_id']}\n";
    }
    
    echo "  Total submissions migrated: " . count($submissions) . "\n\n";
    
    // 5. Migrate Scores
    echo "5. Migrating scores...\n";
    $stmt = $pdo->query("SELECT * FROM scores ORDER BY id ASC");
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($scores as $score) {
        $mongoScore = [
            'submission_id' => $submissionIdMap[$score['submission_id']],
            'jury_user_id' => $userIdMap[$score['jury_user_id']],
            'score' => (int)$score['score'],
            'comments' => $score['comments'],
            'rated_at' => new MongoDB\BSON\UTCDateTime(strtotime($score['rated_at']) * 1000)
        ];
        
        $scoresCollection->insertOne($mongoScore);
        echo "  ✓ Score migrated: {$score['score']}/100 for submission {$score['submission_id']}\n";
    }
    
    echo "  Total scores migrated: " . count($scores) . "\n\n";
    
    // Summary
    echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
    echo "Summary:\n";
    echo "  - Users: " . count($users) . "\n";
    echo "  - Teams: " . count($teams) . "\n";
    echo "  - Verification Logs: " . count($verificationLogs) . "\n";
    echo "  - Submissions: " . count($submissions) . "\n";
    echo "  - Scores: " . count($scores) . "\n";
    echo "\nAll data has been successfully migrated to MongoDB!\n";
    echo "You can now use the application with MongoDB.\n\n";
    
} catch (PDOException $e) {
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ MongoDB Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
