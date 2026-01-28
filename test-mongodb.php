<?php
/**
 * Test Script for MongoDB Integration
 * Tests all major functionality
 */

require_once __DIR__ . '/config/mongo_config.php';
require_once __DIR__ . '/config/auth.php';

echo "=== MongoDB Application Test Suite ===\n\n";

// Test 1: MongoDB Connection
echo "1. Testing MongoDB Connection...\n";
try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $adminDb = $client->admin;
    $adminDb->command(['ping' => 1]);
    echo "   ✅ MongoDB Connected Successfully\n";
} catch (Exception $e) {
    echo "   ❌ MongoDB Connection Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Collections Check
echo "\n2. Checking Collections...\n";
$collections = [
    'users' => 'Users Collection',
    'teams' => 'Teams Collection',
    'submissions' => 'Submissions Collection',
    'scores' => 'Scores Collection',
    'verification_logs' => 'Verification Logs',
    'activity_logs' => 'Activity Logs'
];

foreach ($collections as $name => $label) {
    $count = $db->$name->countDocuments([]);
    echo "   ✅ $label: $count documents\n";
}

// Test 3: User Query (MongoDB)
echo "\n3. Testing User Query with MongoDB...\n";
try {
    $user = $usersCollection->findOne(['email' => 'panitia@codingday.com']);
    if ($user) {
        echo "   ✅ User Found: " . $user['email'] . " (Role: " . $user['role'] . ")\n";
        echo "   User ID: " . $user['_id'] . "\n";
    } else {
        echo "   ❌ User Not Found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Query Failed: " . $e->getMessage() . "\n";
}

// Test 4: Teams Query
echo "\n4. Testing Teams Query...\n";
try {
    $teams = $teamsCollection->find([])->toArray();
    echo "   ✅ Found " . count($teams) . " teams\n";
    foreach ($teams as $team) {
        echo "      - " . $team['team_name'] . " (Leader ID: " . $team['leader_id'] . ")\n";
    }
} catch (Exception $e) {
    echo "   ❌ Teams Query Failed: " . $e->getMessage() . "\n";
}

// Test 5: Aggregation Test (Complex Query)
echo "\n5. Testing Aggregation Pipeline...\n";
try {
    $pipeline = [
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
    ];
    
    $result = $teamsCollection->aggregate($pipeline)->toArray();
    echo "   ✅ Aggregation Successful\n";
    echo "   Results: " . count($result) . " teams with submission info\n";
} catch (Exception $e) {
    echo "   ❌ Aggregation Failed: " . $e->getMessage() . "\n";
}

// Test 6: PHP Extension Check
echo "\n6. Checking PHP Extensions...\n";
$extensions = ['mongodb', 'curl', 'json', 'session'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext) ? '✅' : '❌';
    echo "   $loaded $ext\n";
}

echo "\n=== All Tests Completed ===\n";
?>
