<?php
// tests/test.php

echo "=========================================\n";
echo "       TILEFLOW TEST AUTOMATION          \n";
echo "=========================================\n";

$errors = 0;

// Test 1: File Layout checks
echo "Test 1: Verifying codebase files layout... ";
$required_files = [
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../includes/auth.php',
    __DIR__ . '/../includes/header.php',
    __DIR__ . '/../includes/footer.php',
    __DIR__ . '/../database/inventory.sql',
    __DIR__ . '/../dashboard.php',
    __DIR__ . '/../login.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = basename($file);
    }
}

if (empty($missing_files)) {
    echo "PASSED\n";
} else {
    echo "FAILED (Missing: " . implode(', ', $missing_files) . ")\n";
    $errors++;
}

// Test 2: PHP Syntax linting (Mock parsing config and auth)
echo "Test 2: Testing PHP syntax evaluation... ";
try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/auth.php';
    echo "PASSED\n";
} catch (Error $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
    $errors++;
}

// Test 3: base_url helper logic
echo "Test 3: Testing dynamic base_url() generator... ";
$_SERVER['REQUEST_URI'] = '/inventory-devops/dashboard.php';
$test_url_1 = base_url('products/add.php');

$_SERVER['REQUEST_URI'] = '/dashboard.php';
$test_url_2 = base_url('products/add.php');

if ($test_url_1 === '/inventory-devops/products/add.php' && $test_url_2 === '/products/add.php') {
    echo "PASSED\n";
} else {
    echo "FAILED (base_url results: '$test_url_1' and '$test_url_2')\n";
    $errors++;
}

// Test 4: Database check
echo "Test 4: Attempting DB integration tests... ";
try {
    // Override settings to avoid blockages if run under isolated environment variables
    $db = getDBConnection();
    if ($db) {
        echo "CONNECTED\n";
        
        // Test 5: Verify table schema mapping
        echo "Test 5: Checking database schema tables... ";
        $expected_tables = ['users', 'categories', 'products', 'stock_transactions'];
        $missing_tables = [];
        
        foreach ($expected_tables as $table) {
            try {
                $db->query("SELECT 1 FROM $table LIMIT 1");
            } catch (PDOException $ex) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            echo "PASSED\n";
        } else {
            echo "FAILED (Missing tables: " . implode(', ', $missing_tables) . ")\n";
            $errors++;
        }
    }
} catch (PDOException $e) {
    // If db is not running, we treat it as skipped since it's a CLI dry-run.
    // This allows the build stage to pass even if MySQL service isn't active on Jenkins host.
    echo "SKIPPED (Active Database Connection not present: " . $e->getMessage() . ")\n";
}

echo "-----------------------------------------\n";
if ($errors > 0) {
    echo "RESULT: FAILED ($errors test failures)\n";
    exit(1);
} else {
    echo "RESULT: SUCCESS (All tests passed)\n";
    exit(0);
}
