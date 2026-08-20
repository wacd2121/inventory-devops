<?php
// dashboard.php

$active_page = 'dashboard';
$page_title = "Dashboard";
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$db = getDBConnection();

// Fetch Summary Stats
try {
    $total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_stock = $db->query("SELECT SUM(quantity) FROM products")->fetchColumn() ?: 0;
    $low_stock_count = $db->query("SELECT COUNT(*) FROM products WHERE quantity <= minimum_stock")->fetchColumn();
    $total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (\PDOException $e) {
    // If table doesn't exist yet or connection fails
    $total_products = 0;
    $total_stock = 0;
    $low_stock_count = 0;
    $total_categories = 0;
}

// Search Query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    if ($search !== '') {
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ? OR p.product_code LIKE ? OR c.name LIKE ?
            ORDER BY p.name ASC
        ");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        $products = $stmt->fetchAll();
    } else {
        $products = $db->query("
            SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id
            ORDER BY p.name ASC
        ")->fetchAll();
    }
} catch (\PDOException $e) {
    $products = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; letter-spacing: -0.02em;">Inventory Dashboard</h1>
        <p style="color: var(--text-secondary);">Real-time monitoring and stock level analytics.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?php echo base_url('categories/add.php'); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-folder-plus"></i> Add Category
        </a>
        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
            <a href="<?php echo base_url('products/add.php'); ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Grid -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fa-solid fa-cubes"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Products</span>
            <span class="stat-value"><?php echo number_format($total_products); ?></span>
        </div>
    </div>
    
    <div class="stat-card stat-stock">
        <div class="stat-icon">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Stock</span>
            <span class="stat-value"><?php echo number_format($total_stock); ?></span>
        </div>
    </div>
    
    <div class="stat-card stat-low-stock">
        <div class="stat-icon" style="color: var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Low Stock Products</span>
            <span class="stat-value" style="color: <?php echo $low_stock_count > 0 ? 'var(--danger)' : 'inherit'; ?>;">
                <?php echo number_format($low_stock_count); ?>
            </span>
        </div>
    </div>
    
    <div class="stat-card stat-categories">
        <div class="stat-icon">
            <i class="fa-solid fa-tags"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Categories</span>
            <span class="stat-value"><?php echo number_format($total_categories); ?></span>
        </div>
    </div>
</div>

<!-- Main Inventory Table Card -->
<div class="card" style="padding: 1.5rem;">
    <div class="actions-row">
        <h2 style="font-size: 1.25rem; font-weight: 600;">Product Catalogue</h2>
        <form action="<?php echo base_url('dashboard.php'); ?>" method="GET" class="search-form">
            <div class="input-group">
                <i class="fa-solid fa-magnifying-glass input-icon"></i>
                <input type="text" name="search" id="search-input" class="form-control form-control-icon" placeholder="Search product name, code or category..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if ($search !== ''): ?>
                <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-secondary" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 15%">Product Code</th>
                    <th style="width: 25%">Product Name</th>
                    <th style="width: 18%">Category</th>
                    <th style="width: 12%">Price</th>
                    <th style="width: 15%">Stock Level</th>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <th style="text-align: right; width: 15%">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): 
                        $is_low = $product['quantity'] <= $product['minimum_stock'];
                    ?>
                        <tr class="product-row">
                            <td class="product-code" style="font-weight: 600; font-family: monospace; color: var(--text-primary);">
                                <?php echo htmlspecialchars($product['product_code']); ?>
                            </td>
                            <td class="product-name" style="font-weight: 500; color: var(--text-primary);">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </td>
                            <td class="product-category">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </td>
                            <td>
                                $<?php echo number_format($product['price'], 2); ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-weight: 600; color: <?php echo $is_low ? 'var(--danger)' : 'var(--text-primary)'; ?>;">
                                        <?php echo number_format($product['quantity']); ?>
                                    </span>
                                    <span style="color: var(--text-secondary); font-size: 0.8rem;">
                                        / min: <?php echo $product['minimum_stock']; ?>
                                    </span>
                                    <?php if ($is_low): ?>
                                        <span class="badge badge-danger">LOW STOCK</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">OK</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="<?php echo base_url('products/edit.php?id=' . $product['id']); ?>" class="table-action-link" title="Edit Product">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?php echo base_url('products/delete.php?id=' . $product['id']); ?>" class="table-action-link delete" title="Delete Product">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo ($_SESSION['user_role'] ?? '') === 'admin' ? 6 : 5; ?>" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <i class="fa-solid fa-circle-info" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                            No products found in the catalogue.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
