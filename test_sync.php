<?php
/**
 * Test CSV Sync Script
 * Directly tests uploading converted_output.csv to Supabase
 */

require_once __DIR__ . '/controllers/process_data.php';

echo "=== CSV Sync Test ===\n\n";

$csvFile = __DIR__ . '/converted_output.csv';

if (!file_exists($csvFile)) {
    die("Error: converted_output.csv not found!\n");
}

echo "Testing file: $csvFile\n\n";

// Call processDataFile directly
$result = processDataFile($csvFile);

echo "\n=== Result ===\n";
print_r($result);

if ($result['success']) {
    echo "\n✅ SUCCESS: {$result['synced']} records synced to Supabase\n";
} else {
    echo "\n❌ ERROR: {$result['message']}\n";
}
