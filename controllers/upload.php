<?php
/**
 * File Upload Handler
 * Handles PDF/CSV/Excel uploads, processes them, and syncs to Supabase
 */

header('Content-Type: application/json');

// Include the data processor
require_once __DIR__ . '/process_data.php';

// Configuration
$UPLOAD_DIR = __DIR__ . '/../assets/pdfs/';

// Ensure upload directory exists
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

// Response helper
function jsonResponse($success, $message, $data = [])
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'طريقة الطلب غير صحيحة');
}

// Check for file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'الملف كبير جداً',
        UPLOAD_ERR_FORM_SIZE => 'الملف كبير جداً',
        UPLOAD_ERR_PARTIAL => 'لم يتم رفع الملف بالكامل',
        UPLOAD_ERR_NO_FILE => 'لم يتم اختيار ملف',
        UPLOAD_ERR_NO_TMP_DIR => 'خطأ في الخادم',
        UPLOAD_ERR_CANT_WRITE => 'خطأ في الكتابة',
        UPLOAD_ERR_EXTENSION => 'امتداد الملف غير مسموح',
    ];
    $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    jsonResponse(false, $errorMessages[$errorCode] ?? 'خطأ في رفع الملف');
}

$file = $_FILES['file'];
$fileName = basename($file['name']);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type
$allowedExtensions = ['pdf', 'xlsx', 'csv'];
if (!in_array($fileExt, $allowedExtensions)) {
    jsonResponse(false, 'نوع الملف غير مسموح. الأنواع المسموحة: PDF, Excel (XLSX), CSV');
}

// Generate unique filename
$uniqueName = 'upload_' . date('Ymd_His') . '_' . uniqid() . '.' . $fileExt;
$uploadPath = $UPLOAD_DIR . $uniqueName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    jsonResponse(false, 'خطأ في حفظ الملف');
}

// Process the file with PHP processor
// No CSV output, direct sync to Supabase
$result = processDataFile($uploadPath);

if ($result['success']) {
    jsonResponse(true, $result['message'], [
        'records' => $result['records'] ?? 0,
        'synced' => $result['synced'] ?? 0
    ]);
} else {
    jsonResponse(false, $result['message']);
}

