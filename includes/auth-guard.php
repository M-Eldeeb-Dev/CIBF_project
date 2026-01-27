<?php


// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 * @return bool
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_type']) &&
        isset($_SESSION['user_code']) &&
        !empty($_SESSION['user_type']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin(): bool
{
    return isAuthenticated() && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if user is volunteer
 * @return bool
 */
function isVolunteer(): bool
{
    return isAuthenticated() && $_SESSION['user_type'] === 'volunteer';
}

/**
 * Require authentication - redirects to login if not authenticated
 * @param string $requiredRole Optional - 'admin' or 'volunteer'
 */
function requireAuth(string $requiredRole = ''): void
{
    if (!isAuthenticated()) {
        header('Location: index.php');
        exit;
    }

    if ($requiredRole === 'admin' && !isAdmin()) {
        header('Location: volunteer-dashboard.php');
        exit;
    }

    if ($requiredRole === 'volunteer' && !isVolunteer()) {
        header('Location: admin-dashboard.php');
        exit;
    }

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get hidden CSRF input field HTML
 * @return string
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

/**
 * Get current user info
 * @return array
 */
function getCurrentUser(): array
{
    if (!isAuthenticated()) {
        return [];
    }
    return [
        'type' => $_SESSION['user_type'] ?? '',
        'name' => $_SESSION['user_name'] ?? '',
        'code' => $_SESSION['user_code'] ?? '',
        'group' => $_SESSION['user_group'] ?? '',
        'period' => $_SESSION['user_period'] ?? '',
        'sector' => $_SESSION['user_sector'] ?? '',
        'hall_id' => $_SESSION['user_hall_id'] ?? null,
    ];
}

/**
 * Logout user
 */
function logout(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
