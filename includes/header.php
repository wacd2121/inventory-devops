<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TileFlow - A high performance inventory management system for tile manufacturers and sellers. Built with DevOps standards.">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " | TileFlow" : "TileFlow - Inventory Management System"; ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
    <!-- Premium background glowing shapes -->
    <div class="glow-bg">
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
    </div>
    
    <header class="navbar">
        <div class="navbar-container">
            <a href="<?php echo base_url('dashboard.php'); ?>" class="brand" id="nav-brand">
                <i class="fa-solid fa-boxes-stacked brand-icon"></i>
                <span>Tile<span class="brand-highlight">Flow</span></span>
            </a>
            
            <?php if (isLoggedIn()): ?>
            <nav class="nav-links">
                <a href="<?php echo base_url('dashboard.php'); ?>" class="nav-link-item <?php echo ($active_page ?? '') === 'dashboard' ? 'active' : ''; ?>" id="nav-link-dashboard">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <a href="<?php echo base_url('categories/index.php'); ?>" class="nav-link-item <?php echo ($active_page ?? '') === 'categories' ? 'active' : ''; ?>" id="nav-link-categories">
                    <i class="fa-solid fa-tags"></i> Categories
                </a>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <span class="user-role badge-role-<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'user'); ?>">
                        <?php echo strtoupper(htmlspecialchars($_SESSION['user_role'] ?? 'User')); ?>
                    </span>
                </div>
                <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn" id="nav-btn-logout" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" id="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>
                    <?php 
                        $err = $_GET['error'];
                        if ($err === 'unauthorized') echo "Access Denied: You do not have permissions to perform this action.";
                        elseif ($err === 'db_error') echo "Database error occurred. Please try again.";
                        elseif ($err === 'invalid_input') echo "Invalid parameters provided.";
                        else echo htmlspecialchars($err);
                    ?>
                </span>
                <button type="button" class="alert-close-btn" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" id="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>
                    <?php 
                        $succ = $_GET['success'];
                        if ($succ === 'added') echo "Item added successfully.";
                        elseif ($succ === 'edited') echo "Item updated successfully.";
                        elseif ($succ === 'deleted') echo "Item deleted successfully.";
                        else echo htmlspecialchars($succ);
                    ?>
                </span>
                <button type="button" class="alert-close-btn" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>
