<?php
/**
 * Debug Supabase Sync Script
 * Tests direct sync with verbose output
 */

$SUPABASE_URL = "https://ximlhsxjtzakznrcytpu.supabase.co";
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhpbWxoc3hqdHpha3pucmN5dHB1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjkzNzY0NzQsImV4cCI6MjA4NDk1MjQ3NH0.MJaamCrETHMvox44f4Q2lz-ygwGEmf7X7tCN1gZW018";

echo "=== Debug Supabase Sync ===\n\n";

// Test 1: Test connection to Supabase
echo "1. Testing Supabase connection...\n";
$testUrl = $SUPABASE_URL . '/rest/v1/volunteers?select=*&limit=1';
$headers = [
    'apikey: ' . $SUPABASE_KEY,
    'Authorization: Bearer ' . $SUPABASE_KEY,
];

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
if ($curlError) {
    echo "   cURL Error: $curlError\n";
}
echo "   Response (first 200 chars): " . substr($response, 0, 200) . "\n\n";

// Test 2: Try to upsert a single record
echo "2. Testing single record upsert...\n";

$testRecord = [
    'volunteerCode' => 'O-0083',
    'name' => 'إسراء تامر عوض احمد',
    'group' => 'دلتا',
    'period' => '10:06',
    'sector' => 'A',
    'loc1' => 'صالة',
    'loc2' => 'صالة',
    'loc3' => 'باب',
    'loc4' => 'N/A',
    'break1' => '12:45',
    'break2' => '4:15'
];

$upsertUrl = $SUPABASE_URL . '/rest/v1/volunteers';
$upsertHeaders = [
    'apikey: ' . $SUPABASE_KEY,
    'Authorization: Bearer ' . $SUPABASE_KEY,
    'Content-Type: application/json',
    'Prefer: resolution=merge-duplicates'
];

$ch = curl_init($upsertUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $upsertHeaders);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([$testRecord]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
if ($curlError) {
    echo "   cURL Error: $curlError\n";
}
echo "   Response: $response\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCCESS: Upsert worked!\n";
} else {
    echo "❌ ERROR: Upsert failed with HTTP $httpCode\n";
    echo "   Full response: $response\n";
}

// Test 3: Parse and show sample from converted CSV
echo "\n3. Parsed records from CSV (first 3)...\n";
$csvFile = __DIR__ . '/converted_output.csv';
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    // Skip BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF")
        rewind($handle);

    $headers = fgetcsv($handle);
    echo "   Headers: " . implode(', ', $headers) . "\n\n";

    for ($i = 0; $i < 3 && ($row = fgetcsv($handle)) !== false; $i++) {
        echo "   Record $i: ";
        $record = array_combine($headers, $row);
        echo json_encode($record, JSON_UNESCAPED_UNICODE) . "\n";
    }
    fclose($handle);
}
