<?php
// includes/auth.php

// Start sessions only for normal web requests.
// Jenkins/CLI tests do not need a PHP session.
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check whether the current user is logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Require the user to be logged in.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: " . base_url('login.php'));
        exit;
    }
}

/**
 * Require the user to have administrator privileges.
 */
function requireAdmin(): void
{
    requireLogin();

    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header("Location: " . base_url('dashboard.php?error=unauthorized'));
        exit;
    }
}

/**
 * Generate application-relative URLs.
 *
 * When deployed under /inventory-devops/, the function returns:
 * /inventory-devops/...
 *
 * When deployed at the web root, it returns:
 * /...
 */
function base_url(string $path = ''): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    $base = '/';

    // Detect the XAMPP project directory.
    if (
        strpos($requestUri, '/inventory-devops/') === 0 ||
        strpos($scriptName, '/inventory-devops/') === 0
    ) {
        $base = '/inventory-devops/';
    }

    return $base . ltrim($path, '/');
}