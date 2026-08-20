<?php
// categories/index.php

$active_page = 'categories';
$page_title = "Product Categories";
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = getDBConnection();

try {
    $categories = $db->query("
        SELECT c.*, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (\PDOException $e) {
    $categories = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; letter-spacing: -0.02em;">Product Categories</h1>
        <p style="color: var(--text-secondary);">Organize and group items in the tile catalogue.</p>
    </div>
    <a href="<?php echo base_url('categories/add.php'); ?>" class="btn btn-primary" id="btn-add-category">
        <i class="fa-solid fa-plus"></i> Add Category
    </a>
</div>

<div class="categories-grid" id="categories-container">
    <?php if (count($categories) > 0): ?>
        <?php foreach ($categories as $category): ?>
            <div class="category-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 0.5rem;">
                    <h3 class="category-title" style="margin: 0;"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <span class="badge badge-success" style="flex-shrink: 0; font-family: monospace;">
                        <?php echo $category['product_count']; ?> ITEMS
                    </span>
                </div>
                <p class="category-desc">
                    <?php echo htmlspecialchars($category['description'] ?: 'No description registered for this category.'); ?>
                </p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-secondary);">
            <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 0.5rem; display: block;"></i>
            No categories registered. Click "Add Category" to get started.
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
