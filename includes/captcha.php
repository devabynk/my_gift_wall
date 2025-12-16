<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a new CAPTCHA math problem
 * @return string The CAPTCHA question
 */
function generateCaptcha()
{
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operations = ['+', '-'];
    $operation = $operations[array_rand($operations)];

    if ($operation === '+') {
        $answer = $num1 + $num2;
    } else {
        // Ensure positive results for subtraction
        if ($num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }
        $answer = $num1 - $num2;
    }

    $_SESSION['captcha_answer'] = $answer;
    $_SESSION['captcha_time'] = time();

    return "$num1 $operation $num2 = ?";
}

/**
 * Validate user's CAPTCHA answer
 * @param mixed $userAnswer User's submitted answer
 * @return bool True if correct, false otherwise
 */
function validateCaptcha($userAnswer)
{
    if (!isset($_SESSION['captcha_answer'])) {
        return false;
    }

    // Check if CAPTCHA is expired (10 minutes)
    if (isset($_SESSION['captcha_time']) && (time() - $_SESSION['captcha_time']) > 600) {
        unset($_SESSION['captcha_answer']);
        unset($_SESSION['captcha_time']);
        return false;
    }

    $correct = (int) $userAnswer === (int) $_SESSION['captcha_answer'];

    // Clear CAPTCHA after validation
    unset($_SESSION['captcha_answer']);
    unset($_SESSION['captcha_time']);

    return $correct;
}

/**
 * Get current CAPTCHA question (generate if not exists)
 * @return string The CAPTCHA question
 */
function getCaptcha()
{
    if (!isset($_SESSION['captcha_answer']) || !isset($_SESSION['captcha_time'])) {
        return generateCaptcha();
    }

    // Regenerate if expired
    if ((time() - $_SESSION['captcha_time']) > 600) {
        return generateCaptcha();
    }

    // Reconstruct question from session
    // This is a simple version - in production you'd store the question too
    return generateCaptcha();
}

/**
 * Check rate limiting for gift submission
 * @return bool True if allowed, false if rate limited
 */
function checkRateLimit()
{
    $now = time();

    if (!isset($_SESSION['last_gift_time'])) {
        $_SESSION['last_gift_time'] = $now;
        return true;
    }

    // Allow 1 gift per minute
    if (($now - $_SESSION['last_gift_time']) < 60) {
        return false;
    }

    $_SESSION['last_gift_time'] = $now;
    return true;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
