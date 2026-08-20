<?php
// index.php

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . base_url('dashboard.php'));
} else {
    header("Location: " . base_url('login.php'));
}
exit;
