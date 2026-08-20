<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . base_url('login.php'));
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header("Location: " . base_url('dashboard.php?error=unauthorized'));
        exit;
    }
}

function base_url($path = '') {
    static $base = null;
    if ($base === null) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/inventory-devops') !== false) {
            $base = '/inventory-devops/';
        } else {
            $base = '/';
        }
    }
    return $base . ltrim($path, '/');
}
