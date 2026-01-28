<?php
/**
 * CSV Format Converter
 * Converts real.csv format to sample.csv format for Supabase upload
 * 
 * Usage: php convert_csv.php input.csv output.csv
 */

// Input and output files
$inputFile = $argv[1] ?? 'd:/CIBF/real.csv';
$outputFile = $argv[2] ?? 'd:/CIBF/converted_output.csv';

echo "=== CSV Format Converter ===\n";
echo "Input:  $inputFile\n";
echo "Output: $outputFile\n\n";

// Check input file exists
if (!file_exists($inputFile)) {
    die("Error: Input file '$inputFile' not found.\n");
}

// Read input file
$handle = fopen($inputFile, 'r');
if (!$handle) {
    die("Error: Cannot open input file.\n");
}

// Skip BOM if present
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

// Read header line
$header = fgetcsv($handle);
if (!$header) {
    die("Error: No header found in file.\n");
}

echo "Original headers: " . implode(', ', array_slice($header, 0, 6)) . "...\n";

// Map the real.csv columns to expected format
// real.csv format (RTL order):
// Break2, Break 1, 6:07, 3:06, 11:03, 10:11, القطاع, الف رتة, المجموعة, الاسم رباع, الكود, م
// Index:  0       1       2     3      4      5       6       7         8          9         10     11

// Expected sample.csv format:
// name, group, period, sector, break1, break2, loc1, loc2, loc3, loc4, volunteerCode

$records = [];
$lineNum = 1;

while (($row = fgetcsv($handle)) !== false) {
    $lineNum++;

    // Skip empty rows
    if (count($row) < 11 || empty(trim($row[10] ?? ''))) {
        continue;
    }

    // Extract fields from real.csv format (columns are reversed/RTL)
    $break2 = trim($row[0] ?? '');
    $break1 = trim($row[1] ?? '');
    $loc4 = trim($row[2] ?? '');  // 6:07 column
    $loc3 = trim($row[3] ?? '');  // 3:06 column
    $loc2 = trim($row[4] ?? '');  // 11:03 column
    $loc1 = trim($row[5] ?? '');  // 10:11 column
    $sectorRaw = trim($row[6] ?? '');
    $period = trim($row[7] ?? '');
    $group = trim($row[8] ?? '');
    $name = trim($row[9] ?? '');
    $codeRaw = trim($row[10] ?? '');

    // Clean volunteer code - remove space after "O-"
    $volunteerCode = str_replace('O- ', 'O-', $codeRaw);
    $volunteerCode = str_replace(' ', '', $volunteerCode); // Remove any remaining spaces

    // Clean sector - extract just the letter from "قطاع A"
    $sector = $sectorRaw;
    if (preg_match('/[ABCD]/', $sectorRaw, $match)) {
        $sector = $match[0];
    }

    // Convert period format (صباحي -> 10:06, مسائي -> 12:08)
    if ($period === 'صباحي') {
        $period = '10:06';
    } elseif ($period === 'مسائي') {
        $period = '12:08';
    }

    // Validate volunteer code
    if (empty($volunteerCode) || !preg_match('/^O-\d{4}$/', $volunteerCode)) {
        echo "Warning: Skipping line $lineNum - invalid code: '$codeRaw'\n";
        continue;
    }

    // Validate name
    if (empty($name)) {
        echo "Warning: Skipping line $lineNum - empty name\n";
        continue;
    }

    $records[] = [
        'name' => $name,
        'group' => $group,
        'period' => $period,
        'sector' => $sector,
        'break1' => $break1,
        'break2' => $break2,
        'loc1' => $loc1,
        'loc2' => $loc2,
        'loc3' => $loc3,
        'loc4' => $loc4,
        'volunteerCode' => $volunteerCode
    ];
}

fclose($handle);

echo "\nProcessed " . count($records) . " valid records.\n";

// Write output file
$outHandle = fopen($outputFile, 'w');
if (!$outHandle) {
    die("Error: Cannot create output file.\n");
}

// Add BOM for Excel UTF-8 compatibility
fprintf($outHandle, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write header
$outputHeaders = ['name', 'group', 'period', 'sector', 'break1', 'break2', 'loc1', 'loc2', 'loc3', 'loc4', 'volunteerCode'];
fputcsv($outHandle, $outputHeaders);

// Write records
foreach ($records as $record) {
    $row = [];
    foreach ($outputHeaders as $field) {
        $row[] = $record[$field] ?? '';
    }
    fputcsv($outHandle, $row);
}

fclose($outHandle);

echo "\n✅ Conversion complete!\n";
echo "Output file: $outputFile\n";
echo "Total records: " . count($records) . "\n";

// Show sample of converted data
echo "\n--- Sample Output (first 3 records) ---\n";
echo implode(',', $outputHeaders) . "\n";
for ($i = 0; $i < min(3, count($records)); $i++) {
    $r = $records[$i];
    echo "{$r['name']},{$r['group']},{$r['period']},{$r['sector']},{$r['break1']},{$r['break2']},{$r['loc1']},{$r['loc2']},{$r['loc3']},{$r['loc4']},{$r['volunteerCode']}\n";
}
