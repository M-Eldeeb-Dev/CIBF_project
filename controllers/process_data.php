<?php
/**
 * Data Processing Script
 * Parses PDF/CSV/Excel files and syncs to Supabase
 * Now with iLovePDF integration for PDF → Excel conversion
 */

// Configuration
$SUPABASE_URL = "https://ximlhsxjtzakznrcytpu.supabase.co";
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhpbWxoc3hqdHpha3pucmN5dHB1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjkzNzY0NzQsImV4cCI6MjA4NDk1MjQ3NH0.MJaamCrETHMvox44f4Q2lz-ygwGEmf7X7tCN1gZW018";

// iLovePDF API Configuration
$ILOVEPDF_PUBLIC_KEY = "project_public_0fd373b20a0c3a76051372a2e926f9af_xzurcaf146916274322b7a01fed8866a0bbca";
$ILOVEPDF_SECRET_KEY = "secret_key_de52032c55c6b5c291be8e15666f9af9_meErs0f1e1cf9403986f611735ab507999c2a";

/**
 * Convert PDF to Excel using iLovePDF API
 * @param string $pdfPath - Path to the PDF file
 * @return array - ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function convertPdfToExcelWithILovePDF($pdfPath)
{
    global $ILOVEPDF_PUBLIC_KEY, $ILOVEPDF_SECRET_KEY;

    $apiBase = "https://api.ilovepdf.com/v1";

    // Step 1: Authenticate and get JWT token
    $authUrl = "$apiBase/auth";
    $authData = json_encode(['public_key' => $ILOVEPDF_PUBLIC_KEY]);

    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $authResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'path' => null, 'error' => "cURL Error: $curlError"];
    }

    if ($httpCode !== 200) {
        return ['success' => false, 'path' => null, 'error' => "Auth failed (HTTP $httpCode): $authResponse"];
    }

    $authResult = json_decode($authResponse, true);
    $token = $authResult['token'] ?? null;
    if (!$token) {
        return ['success' => false, 'path' => null, 'error' => "No token in response"];
    }

    // Step 2: Start pdfexcel task
    $startUrl = "$apiBase/start/pdfexcel";
    $ch = curl_init($startUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $startResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'path' => null, 'error' => "Start task failed (HTTP $httpCode): $startResponse"];
    }

    $startResult = json_decode($startResponse, true);
    $server = $startResult['server'] ?? null;
    $taskId = $startResult['task'] ?? null;

    if (!$server || !$taskId) {
        return ['success' => false, 'path' => null, 'error' => "Missing server/task in response"];
    }

    // Step 3: Upload PDF file
    $uploadUrl = "https://$server/v1/upload";
    $cFile = new CURLFile($pdfPath, 'application/pdf', basename($pdfPath));

    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'task' => $taskId,
        'file' => $cFile
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $uploadResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'path' => null, 'error' => "Upload failed (HTTP $httpCode): $uploadResponse"];
    }

    $uploadResult = json_decode($uploadResponse, true);
    $serverFilename = $uploadResult['server_filename'] ?? null;

    if (!$serverFilename) {
        return ['success' => false, 'path' => null, 'error' => "No server_filename in upload response"];
    }

    // Step 4: Process the PDF to XLSX
    $processUrl = "https://$server/v1/process";
    $processData = json_encode([
        'task' => $taskId,
        'tool' => 'pdfexcel',
        'files' => [
            ['server_filename' => $serverFilename, 'filename' => basename($pdfPath)]
        ]
    ]);

    $ch = curl_init($processUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $processData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $processResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'path' => null, 'error' => "Process failed (HTTP $httpCode): $processResponse"];
    }

    // Step 5: Download the result XLSX
    $downloadUrl = "https://$server/v1/download/$taskId";

    $ch = curl_init($downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $xlsxContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($xlsxContent)) {
        return ['success' => false, 'path' => null, 'error' => "Download failed (HTTP $httpCode)"];
    }

    // Save XLSX to temp file
    $xlsxPath = sys_get_temp_dir() . '/ilovepdf_' . uniqid() . '.xlsx';
    file_put_contents($xlsxPath, $xlsxContent);

    return ['success' => true, 'path' => $xlsxPath, 'error' => null];
}



/**
 * Check if character is Arabic
 */
function isArabic($char)
{
    $code = mb_ord($char, 'UTF-8');
    return $code >= 0x0600 && $code <= 0x06FF;
}

/**
 * Reverse Arabic word for RTL handling
 */
function reverseArabicWord($word)
{
    $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $char) {
        if (isArabic($char)) {
            return implode('', array_reverse($chars));
        }
    }
    return $word;
}

/**
 * Parse a line from PDF (RTL text handling)
 */
function parseLineRTL($line)
{
    $line = trim($line);
    if (empty($line))
        return null;

    $words = preg_split('/\s+/', $line);
    $wordsReversed = array_reverse($words);
    $processedWords = array_map('reverseArabicWord', $wordsReversed);

    if (empty($processedWords) || !is_numeric($processedWords[0])) {
        return null;
    }

    try {
        $code = null;
        $codeIdx = -1;
        $nameStart = 0;

        // Look for O- code
        foreach ($processedWords as $i => $w) {
            if (strpos($w, 'O-') !== false) {
                if ($w === 'O-') {
                    if ($i > 0 && is_numeric(str_replace('O-', '', $processedWords[$i - 1]))) {
                        $code = 'O-' . $processedWords[$i - 1];
                        $codeIdx = $i;
                        $nameStart = $i + 1;
                    } elseif ($i + 1 < count($processedWords) && is_numeric(str_replace('O-', '', $processedWords[$i + 1]))) {
                        $code = 'O-' . $processedWords[$i + 1];
                        $codeIdx = $i;
                        $nameStart = $i + 2;
                    } else {
                        return null;
                    }
                } else {
                    $code = $w;
                    $codeIdx = $i;
                    $nameStart = $i + 1;
                }
                break;
            }
        }

        if (!$code)
            return null;

        // Period search
        $period = null;
        $periodIdx = -1;

        for ($i = $codeIdx; $i < count($processedWords); $i++) {
            if (preg_match('/^\d{1,2}:\d{2}$/', $processedWords[$i])) {
                $period = $processedWords[$i];
                $periodIdx = $i;
                break;
            }
        }

        if (!$period)
            return null;

        $nameGroupParts = array_slice($processedWords, $nameStart, $periodIdx - $nameStart);
        if (empty($nameGroupParts))
            return null;

        $group = end($nameGroupParts);
        array_pop($nameGroupParts);
        $name = implode(' ', $nameGroupParts);

        $rest = array_slice($processedWords, $periodIdx + 1);

        $breaks = [];
        while (!empty($rest) && preg_match('/^\d{1,2}:\d{2}$/', end($rest))) {
            array_unshift($breaks, array_pop($rest));
        }

        $break1 = count($breaks) >= 2 ? $breaks[count($breaks) - 2] : 'N/A';
        $break2 = count($breaks) >= 1 ? $breaks[count($breaks) - 1] : 'N/A';

        $middleStr = implode(' ', $rest);
        $sector = 'Unknown';

        if (strpos($middleStr, 'قطاع') !== false) {
            if (preg_match('/قطاع\s*([ABCD])/', $middleStr, $match)) {
                $sector = $match[1];
            } else {
                foreach ($rest as $w) {
                    if (in_array($w, ['A', 'B', 'C', 'D'])) {
                        $sector = $w;
                        break;
                    }
                }
            }
        }

        $code = str_replace(' ', '', $code);

        return [
            'volunteerCode' => $code,
            'name' => $name,
            'group' => $group,
            'period' => $period,
            'sector' => $sector,
            'break1' => $break1,
            'break2' => $break2
        ];

    } catch (Exception $e) {
        error_log("Error parsing line: " . substr($line, 0, 30) . "... " . $e->getMessage());
        return null;
    }
}



/**
 * Parse XLSX file using pure PHP (ZipArchive + SimpleXML)
 * XLSX files are ZIP archives containing XML files
 */
function parseXLSX($xlsxPath, &$debugInfo = [])
{
    $records = [];

    // Check if ZipArchive is available
    if (!class_exists('ZipArchive')) {
        $debugInfo[] = "ZipArchive class missing";
        error_log("ZipArchive not available. Please use CSV format instead.");
        return [];
    }

    $zip = new ZipArchive();
    if ($zip->open($xlsxPath) !== true) {
        $debugInfo[] = "Cannot open XLSX file";
        error_log("Cannot open XLSX file");
        return [];
    }

    // Read shared strings (cell values are stored here and referenced by index)
    $sharedStrings = [];
    $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($stringsXml) {
        $xml = @simplexml_load_string($stringsXml);
        if ($xml) {
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string) $si->t;
            }
        }
    }

    // Read sheet1 data
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheetXml) {
        $zip->close();
        $debugInfo[] = "sheet1.xml not found";
        error_log("Cannot find sheet1.xml in XLSX");
        return [];
    }

    $xml = @simplexml_load_string($sheetXml);
    if (!$xml) {
        $zip->close();
        $debugInfo[] = "XML parse failed";
        return [];
    }

    $rows = [];
    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $cell) {
            // Get cell reference (e.g., "A1", "B2")
            $cellRef = (string) $cell['r'];
            // Extract column letter(s) from cell reference
            preg_match('/^([A-Z]+)/', $cellRef, $matches);
            $colLetters = $matches[1] ?? 'A';
            // Convert column letters to zero-based index (A=0, B=1, ... Z=25, AA=26, etc.)
            $colIndex = 0;
            $len = strlen($colLetters);
            for ($i = 0; $i < $len; $i++) {
                $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - ord('A') + 1);
            }
            $colIndex--; // Convert to zero-based

            $value = '';
            $type = (string) $cell['t'];

            if ($type === 's') {
                // Shared string reference
                $idx = (int) $cell->v;
                $value = $sharedStrings[$idx] ?? '';
            } elseif (isset($cell->v)) {
                $value = (string) $cell->v;
            } elseif (isset($cell->is->t)) {
                // Inline string
                $value = (string) $cell->is->t;
            }

            // Store value at the correct column index
            $rowData[$colIndex] = $value;
        }

        // Fill in any missing columns with empty strings
        if (!empty($rowData)) {
            $maxCol = max(array_keys($rowData));
            for ($i = 0; $i <= $maxCol; $i++) {
                if (!isset($rowData[$i])) {
                    $rowData[$i] = '';
                }
            }
            ksort($rowData); // Ensure columns are in order
            $rows[] = array_values($rowData);
        }
    }

    $zip->close();

    if (empty($rows)) {
        $debugInfo[] = "No rows found in sheet";
        return [];
    }

    // First row is header
    $headers = array_shift($rows);
    $debugInfo[] = "Headers: [" . implode(', ', array_slice($headers, 0, 5)) . "...]";

    // Map columns
    $columnMap = [];
    $possibleMappings = [
        'volunteerCode' => ['volunteerCode', 'code', 'الكود', 'Code'],
        'name' => ['name', 'الاسم', 'الاسم رباعي', 'Name'],
        'group' => ['group', 'المجموعة', 'Group'],
        'period' => ['period', 'الفترة', 'Period'],
        'sector' => ['sector', 'القطاع', 'Sector'],
        'loc1' => ['loc1', '10:11', 'Loc1'],
        'loc2' => ['loc2', '11:03', 'Loc2'],
        'loc3' => ['loc3', '3:06', 'Loc3'],
        'loc4' => ['loc4', '6:07', 'Loc4'],
        'break1' => ['break1', 'Break 1', 'Break1', 'استراحة1'],
        'break2' => ['break2', 'Break2', 'استراحة2'],
        'hall_id' => ['hall_id', 'القاعة', 'Hall'],
    ];

    foreach ($possibleMappings as $target => $sources) {
        foreach ($sources as $src) {
            $idx = array_search($src, $headers);
            if ($idx !== false) {
                $columnMap[$target] = $idx;
                break;
            }
        }
    }

    if (empty($columnMap)) {
        $debugInfo[] = "No columns matched";
    } else {
        $debugInfo[] = "Mapped: " . implode(', ', array_keys($columnMap));
    }

    // Parse data rows
    foreach ($rows as $row) {
        $record = [];
        foreach ($columnMap as $target => $idx) {
            $record[$target] = isset($row[$idx]) ? trim($row[$idx]) : '';
        }

        if (!empty($record['volunteerCode']) && !empty($record['name'])) {
            $records[] = $record;
        }
    }

    return $records;
}

/**
 * Parse CSV file
 * @param string $csvPath - Path to CSV file
 * @param array &$debugInfo - Debug information array
 * @return array - Parsed records
 */
function parseCSV($csvPath, &$debugInfo = [])
{
    $records = [];

    $handle = fopen($csvPath, 'r');
    if (!$handle) {
        $debugInfo[] = "Cannot open CSV file";
        return [];
    }

    // Skip BOM if present
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Read headers
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        $debugInfo[] = "No headers found in CSV";
        return [];
    }

    $debugInfo[] = "Headers: [" . implode(', ', array_slice($headers, 0, 5)) . "...]";

    // Map columns
    $columnMap = [];
    $possibleMappings = [
        'volunteerCode' => ['volunteerCode', 'code', 'الكود', 'Code'],
        'name' => ['name', 'الاسم', 'الاسم رباعي', 'Name'],
        'group' => ['group', 'المجموعة', 'Group'],
        'period' => ['period', 'الفترة', 'Period'],
        'sector' => ['sector', 'القطاع', 'Sector'],
        'loc1' => ['loc1', '10:11', 'Loc1'],
        'loc2' => ['loc2', '11:03', 'Loc2'],
        'loc3' => ['loc3', '3:06', 'Loc3'],
        'loc4' => ['loc4', '6:07', 'Loc4'],
        'break1' => ['break1', 'Break 1', 'Break1', 'استراحة1'],
        'break2' => ['break2', 'Break2', 'استراحة2'],
        'hall_id' => ['hall_id', 'القاعة', 'Hall'],
    ];

    foreach ($possibleMappings as $target => $sources) {
        foreach ($sources as $src) {
            $idx = array_search($src, $headers);
            if ($idx !== false) {
                $columnMap[$target] = $idx;
                break;
            }
        }
    }

    if (empty($columnMap)) {
        $debugInfo[] = "No columns matched";
    } else {
        $debugInfo[] = "Mapped: " . implode(', ', array_keys($columnMap));
    }

    // Parse data rows
    while (($row = fgetcsv($handle)) !== false) {
        $record = [];
        foreach ($columnMap as $target => $idx) {
            $record[$target] = isset($row[$idx]) ? trim($row[$idx]) : '';
        }

        if (!empty($record['volunteerCode']) && !empty($record['name'])) {
            $records[] = $record;
        }
    }

    fclose($handle);
    return $records;
}

/**
 * Write records to CSV file
 */
function writeCSV($records, $outputPath)
{
    if (empty($records)) {
        return false;
    }

    $fieldnames = ['volunteerCode', 'name', 'group', 'period', 'sector', 'loc1', 'loc2', 'loc3', 'loc4', 'break1', 'break2', 'hall_id'];

    $handle = fopen($outputPath, 'w');
    if (!$handle)
        return false;

    // Add BOM for Excel UTF-8 compatibility
    fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($handle, $fieldnames);

    foreach ($records as $record) {
        $row = [];
        foreach ($fieldnames as $field) {
            $row[] = $record[$field] ?? '';
        }
        fputcsv($handle, $row);
    }

    fclose($handle);
    return true;
}

/**
 * Sync records to Supabase using upsert
 */
function syncToSupabase($records)
{
    global $SUPABASE_URL, $SUPABASE_KEY;

    $url = $SUPABASE_URL . '/rest/v1/volunteers';

    $headers = [
        'apikey: ' . $SUPABASE_KEY,
        'Authorization: Bearer ' . $SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: resolution=merge-duplicates'
    ];

    // Prepare records for upsert - validate data first
    $upsertData = [];
    $skippedCount = 0;

    foreach ($records as $record) {
        $name = trim($record['name'] ?? '');
        $code = trim($record['volunteerCode'] ?? '');

        // Skip if no volunteer code
        if (empty($code)) {
            $skippedCount++;
            continue;
        }

        // Skip if name is empty or just a sector letter (invalid parse)
        if (empty($name) || in_array($name, ['A', 'B', 'C', 'D', 'N/A', 'Unknown'])) {
            // Log for debugging
            error_log("Skipping invalid record - code: $code, name: '$name' (invalid name)");
            $skippedCount++;
            continue;
        }

        $upsertRecord = [
            'volunteerCode' => $code,
            'name' => $name,
            'group' => $record['group'] ?? '',
            'period' => $record['period'] ?? '',
            'sector' => $record['sector'] ?? '',
            'loc1' => $record['loc1'] ?? '',
            'loc2' => $record['loc2'] ?? '',
            'loc3' => $record['loc3'] ?? '',
            'loc4' => $record['loc4'] ?? '',
            'break1' => $record['break1'] ?? '',
            'break2' => $record['break2'] ?? '',
        ];

        if (!empty($record['hall_id']) && is_numeric($record['hall_id'])) {
            $upsertRecord['hall_id'] = (int) $record['hall_id'];
        }

        $upsertData[] = $upsertRecord;
    }

    if ($skippedCount > 0) {
        error_log("Skipped $skippedCount invalid records during sync");
    }

    // Upsert in batches of 100
    $batchSize = 100;
    $totalSynced = 0;

    for ($i = 0; $i < count($upsertData); $i += $batchSize) {
        $batch = array_slice($upsertData, $i, $batchSize);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($batch));
        // Fix SSL certificate issue on Windows
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $totalSynced += count($batch);
        } else {
            error_log("Supabase error: HTTP $httpCode - $response");
        }
    }

    return $totalSynced;
}

/**
 * Main processing function
 * PDF → Excel (iLovePDF) → CSV → Supabase
 */
/**
 * Main processing function
 * PDF → Excel (iLovePDF) → JSON → Supabase
 */
function processDataFile($inputFile)
{
    if (!file_exists($inputFile)) {
        return ['success' => false, 'message' => 'ملف الإدخال غير موجود'];
    }

    $ext = strtolower(pathinfo($inputFile, PATHINFO_EXTENSION));
    $records = [];
    $debugInfo = [];

    // Parse based on file type
    switch ($ext) {
        case 'pdf':
            // Step 1: Convert PDF to Excel using iLovePDF
            $result = convertPdfToExcelWithILovePDF($inputFile);
            if (!$result['success']) {
                return ['success' => false, 'message' => 'فشل تحويل PDF: ' . $result['error']];
            }

            $xlsxPath = $result['path'];
            if (!$xlsxPath || !file_exists($xlsxPath)) {
                return ['success' => false, 'message' => 'ملف Excel غير موجود بعد التحويل'];
            }

            // Step 2: Parse the XLSX file
            $records = parseXLSX($xlsxPath, $debugInfo);

            // Cleanup temp file
            @unlink($xlsxPath);
            break;

        case 'xlsx':
            $records = parseXLSX($inputFile, $debugInfo);
            break;

        case 'csv':
            $records = parseCSV($inputFile, $debugInfo);
            break;

        default:
            return ['success' => false, 'message' => 'نوع الملف غير مدعوم: ' . $ext . ' (فقط PDF، XLSX، CSV)'];
    }

    if (empty($records)) {
        $msg = 'لا توجد سجلات صالحة في الملف';
        if (!empty($debugInfo)) {
            $msg .= " (Debug: " . implode(", ", $debugInfo) . ")";
        }
        return ['success' => false, 'message' => $msg];
    }

    // Sync to Supabase directly (skipping CSV)
    $synced = syncToSupabase($records);

    return [
        'success' => true,
        'message' => "تم معالجة " . count($records) . " سجل، تم مزامنة $synced إلى قاعدة البيانات",
        'records' => count($records),
        'synced' => $synced
    ];
}

// CLI mode
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $result = processDataFile($argv[1]);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit($result['success'] ? 0 : 1);
}

