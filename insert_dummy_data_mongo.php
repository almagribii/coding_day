<?php
/**
 * Insert Dummy Data to MongoDB
 * 
 * Script ini akan menambahkan dummy data ke MongoDB untuk testing
 * 
 * Usage: php insert_dummy_data_mongo.php
 */

require_once __DIR__ . '/config/mongo_config.php';

echo "=== INSERTING DUMMY DATA TO MONGODB ===\n\n";

try {
    // Clear existing data (optional - uncomment if you want fresh data)
    // echo "Clearing existing data...\n";
    // $scoresCollection->deleteMany([]);
    // $submissionsCollection->deleteMany([]);
    // $verificationLogsCollection->deleteMany([]);
    // $teamsCollection->deleteMany([]);
    // $usersCollection->deleteMany([]);
    // echo "✓ Collections cleared\n\n";
    
    // 1. Insert Users
    echo "1. Inserting users...\n";
    
    // Panitia
    $panitiaResult = $usersCollection->insertOne([
        'email' => 'panitia@codingday.com',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'PANITIA',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $panitiaId = $panitiaResult->getInsertedId();
    echo "  ✓ Panitia user created: panitia@codingday.com\n";
    
    // Juri 1
    $juri1Result = $usersCollection->insertOne([
        'email' => 'juri1@codingday.com',
        'password_hash' => password_hash('juri123', PASSWORD_DEFAULT),
        'role' => 'JURI',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $juri1Id = $juri1Result->getInsertedId();
    echo "  ✓ Juri 1 created: juri1@codingday.com\n";
    
    // Juri 2
    $juri2Result = $usersCollection->insertOne([
        'email' => 'juri2@codingday.com',
        'password_hash' => password_hash('juri123', PASSWORD_DEFAULT),
        'role' => 'JURI',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $juri2Id = $juri2Result->getInsertedId();
    echo "  ✓ Juri 2 created: juri2@codingday.com\n";
    
    // Team Leaders (Peserta)
    $team1LeaderResult = $usersCollection->insertOne([
        'email' => 'leader1@team.com',
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => 'PESERTA',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team1LeaderId = $team1LeaderResult->getInsertedId();
    echo "  ✓ Team 1 Leader created: leader1@team.com\n";
    
    $team2LeaderResult = $usersCollection->insertOne([
        'email' => 'leader2@team.com',
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => 'PESERTA',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team2LeaderId = $team2LeaderResult->getInsertedId();
    echo "  ✓ Team 2 Leader created: leader2@team.com\n";
    
    $team3LeaderResult = $usersCollection->insertOne([
        'email' => 'leader3@team.com',
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => 'PESERTA',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team3LeaderId = $team3LeaderResult->getInsertedId();
    echo "  ✓ Team 3 Leader created: leader3@team.com\n";
    
    echo "\n2. Inserting teams...\n";
    
    // Team 1 - Verified
    $team1Result = $teamsCollection->insertOne([
        'team_name' => 'Code Warriors',
        'leader_id' => $team1LeaderId,
        'is_verified' => true,
        'verified_by_user_id' => $panitiaId,
        'verified_at' => new MongoDB\BSON\UTCDateTime(),
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team1Id = $team1Result->getInsertedId();
    echo "  ✓ Team 1 (Code Warriors) created and verified\n";
    
    // Team 2 - Verified
    $team2Result = $teamsCollection->insertOne([
        'team_name' => 'Ninja Coders',
        'leader_id' => $team2LeaderId,
        'is_verified' => true,
        'verified_by_user_id' => $panitiaId,
        'verified_at' => new MongoDB\BSON\UTCDateTime(),
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team2Id = $team2Result->getInsertedId();
    echo "  ✓ Team 2 (Ninja Coders) created and verified\n";
    
    // Team 3 - Not Verified
    $team3Result = $teamsCollection->insertOne([
        'team_name' => 'Tech Innovators',
        'leader_id' => $team3LeaderId,
        'is_verified' => false,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $team3Id = $team3Result->getInsertedId();
    echo "  ✓ Team 3 (Tech Innovators) created (not verified)\n";
    
    echo "\n3. Inserting verification logs...\n";
    
    $verificationLogsCollection->insertOne([
        'team_id' => $team1Id,
        'admin_id' => $panitiaId,
        'verified_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Verification log for Team 1\n";
    
    $verificationLogsCollection->insertOne([
        'team_id' => $team2Id,
        'admin_id' => $panitiaId,
        'verified_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Verification log for Team 2\n";
    
    echo "\n4. Inserting submissions...\n";
    
    // Submission Team 1
    $submission1Result = $submissionsCollection->insertOne([
        'team_id' => $team1Id,
        'gdrive_link' => 'https://drive.google.com/drive/folders/sample-team1',
        'status' => 'RATED',
        'submitted_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $submission1Id = $submission1Result->getInsertedId();
    echo "  ✓ Submission for Team 1 (Code Warriors)\n";
    
    // Submission Team 2
    $submission2Result = $submissionsCollection->insertOne([
        'team_id' => $team2Id,
        'gdrive_link' => 'https://drive.google.com/drive/folders/sample-team2',
        'status' => 'RATED',
        'submitted_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    $submission2Id = $submission2Result->getInsertedId();
    echo "  ✓ Submission for Team 2 (Ninja Coders)\n";
    
    echo "\n5. Inserting scores...\n";
    
    // Scores for Team 1 submission
    $scoresCollection->insertOne([
        'submission_id' => $submission1Id,
        'jury_user_id' => $juri1Id,
        'score' => 85,
        'comments' => 'Excellent work! Clean code and good documentation.',
        'rated_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Score from Juri 1 for Team 1: 85/100\n";
    
    $scoresCollection->insertOne([
        'submission_id' => $submission1Id,
        'jury_user_id' => $juri2Id,
        'score' => 90,
        'comments' => 'Great project! Very innovative approach.',
        'rated_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Score from Juri 2 for Team 1: 90/100\n";
    
    // Scores for Team 2 submission
    $scoresCollection->insertOne([
        'submission_id' => $submission2Id,
        'jury_user_id' => $juri1Id,
        'score' => 78,
        'comments' => 'Good effort, but needs more testing.',
        'rated_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Score from Juri 1 for Team 2: 78/100\n";
    
    $scoresCollection->insertOne([
        'submission_id' => $submission2Id,
        'jury_user_id' => $juri2Id,
        'score' => 82,
        'comments' => 'Nice implementation, keep it up!',
        'rated_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    echo "  ✓ Score from Juri 2 for Team 2: 82/100\n";
    
    // Summary
    echo "\n=== DUMMY DATA INSERTION COMPLETED ===\n";
    echo "\nTest Accounts Created:\n";
    echo "  PANITIA:\n";
    echo "    Email: panitia@codingday.com\n";
    echo "    Password: admin123\n";
    echo "\n  JURI:\n";
    echo "    Email: juri1@codingday.com\n";
    echo "    Password: juri123\n";
    echo "    Email: juri2@codingday.com\n";
    echo "    Password: juri123\n";
    echo "\n  PESERTA:\n";
    echo "    Email: leader1@team.com (Code Warriors - Verified)\n";
    echo "    Password: password\n";
    echo "    Email: leader2@team.com (Ninja Coders - Verified)\n";
    echo "    Password: password\n";
    echo "    Email: leader3@team.com (Tech Innovators - Not Verified)\n";
    echo "    Password: password\n";
    echo "\nYou can now login and test the application!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
