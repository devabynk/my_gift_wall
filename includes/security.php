<?php
/**
 * Security Headers and Helper Functions
 * Include this at the beginning of each page
 */

/**
 * Set security headers for production
 */
function setSecurityHeaders(): void
{
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Content Security Policy (adjust as needed)
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
    $csp .= "font-src 'self' https://fonts.gstatic.com; ";
    $csp .= "img-src 'self' data: https:; ";
    $csp .= "connect-src 'self'; ";
    $csp .= "frame-ancestors 'self';";
    header("Content-Security-Policy: $csp");
}

/**
 * Sanitize input string
 * @param string $input
 * @return string
 */
function sanitize(string $input): string
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Sanitize array of inputs
 * @param array $inputs
 * @return array
 */
function sanitizeArray(array $inputs): array
{
    return array_map('sanitize', $inputs);
}

/**
 * Safe redirect with validation
 * @param string $url
 * @param bool $permanent
 */
function redirect(string $url, bool $permanent = false): void
{
    // Only allow internal redirects
    if (strpos($url, 'http') === 0 && strpos($url, $_SERVER['HTTP_HOST']) === false) {
        $url = 'index.php';
    }

    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit;
}

/**
 * Set flash message
 * @param string $type success|error|warning|info
 * @param string $message
 */
function setFlash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 * @return string
 */
function getClientIp(): string
{
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Validate UUID format
 * @param string $uuid
 * @return bool
 */
function isValidUuid(string $uuid): bool
{
    return preg_match('/^[a-f0-9]{32}$/i', $uuid) === 1;
}

/**
 * Generate secure random token
 * @param int $length
 * @return string
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

// Auto-set security headers if not CLI
if (php_sapi_name() !== 'cli') {
    setSecurityHeaders();
}
