<?php
// products/delete.php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    try {
        $db = getDBConnection();
        // Since database schema uses ON DELETE CASCADE for stock_transactions, deleting a product is clean.
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: " . base_url('dashboard.php?success=deleted'));
        exit;
    } catch (\PDOException $e) {
        header("Location: " . base_url('dashboard.php?error=db_error'));
        exit;
    }
} else {
    header("Location: " . base_url('dashboard.php?error=invalid_input'));
    exit;
}
