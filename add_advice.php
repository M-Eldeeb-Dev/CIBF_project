<?php
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['hall']) || !isset($data['text'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$hallKey = $data['hall'];
$text = trim($data['text']);
$code = isset($data['code']) ? trim($data['code']) : '';

if (empty($text)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Text cannot be empty']);
    exit;
}

// Load existing data
$file_path = 'json_files/halls_advices.json';
if (!file_exists($file_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Data file not found']);
    exit;
}

$json_content = file_get_contents($file_path);
// Remove BOM if present
if (strpos($json_content, "\xEF\xBB\xBF") === 0) {
    $json_content = substr($json_content, 3);
}

$halls_data = json_decode($json_content, true);

if (!isset($halls_data['halls'][$hallKey])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid hall key']);
    exit;
}

// Create new tip object
$new_tip = [
    'text' => $text,
    'code' => $code
];

// Add to tips array
$halls_data['halls'][$hallKey]['tips'][] = $new_tip;

// Save back to file
if (file_put_contents($file_path, json_encode($halls_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Tip added successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save data']);
}
?>