<?php
/**
 * CSRF Token Helpers
 */

/**
 * Generate or retrieve the current CSRF token.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Validate the submitted CSRF token.
 * Aborts with 403 if invalid.
 */
function validateCsrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $submitted)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}
