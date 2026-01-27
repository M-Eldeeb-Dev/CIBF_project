<?php
/**
 * Login Handler for Supabase Authentication
 * Receives authenticated user data from JavaScript and creates PHP session
 */
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$supabaseAuth = $_POST['supabase_auth'] ?? false;
$volunteerData = $_POST['volunteer_data'] ?? null;
$isAdmin = ($_POST['is_admin'] ?? '0') === '1';

if (!$supabaseAuth || !$volunteerData) {
    echo json_encode(['success' => false, 'error' => 'Missing authentication data']);
    exit;
}

try {
    $data = json_decode($volunteerData, true);

    if (!$data) {
        throw new Exception('Invalid volunteer data');
    }

    if ($isAdmin) {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = 'المسؤول';
        $_SESSION['user_code'] = 'O-9999';

        echo json_encode([
            'success' => true,
            'redirect' => 'admin-dashboard.php'
        ]);
    } else {
        $_SESSION['user_type'] = 'volunteer';
        $_SESSION['user_name'] = $data['name'] ?? 'متطوع';
        $_SESSION['user_code'] = $data['volunteerCode'] ?? 'N/A';
        $_SESSION['user_group'] = $data['group'] ?? 'N/A';
        $_SESSION['user_period'] = $data['period'] ?? 'N/A';
        $_SESSION['user_sector'] = $data['sector'] ?? 'N/A';
        $_SESSION['user_break1'] = $data['break1'] ?? 'N/A';
        $_SESSION['user_break2'] = $data['break2'] ?? 'N/A';
        $_SESSION['user_loc1'] = $data['loc1'] ?? 'N/A';
        $_SESSION['user_loc2'] = $data['loc2'] ?? 'N/A';
        $_SESSION['user_loc3'] = $data['loc3'] ?? 'N/A';
        $_SESSION['user_loc4'] = $data['loc4'] ?? 'N/A';
        $_SESSION['user_hall_id'] = $data['hall_id'] ?? null;
        $_SESSION['user_is_present'] = $data['is_present'] ?? false;

        echo json_encode([
            'success' => true,
            'redirect' => 'volunteer-dashboard.php'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'خطأ في معالجة البيانات: ' . $e->getMessage()
    ]);
}
