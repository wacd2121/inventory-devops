<?php
// categories/add.php

$active_page = 'categories';
$page_title = "Add Category";
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$error = null;
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $db = getDBConnection();
        // Check uniqueness
        $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = "A category with that name already exists.";
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                header("Location: " . base_url('categories/index.php?success=added'));
                exit;
            } catch (\PDOException $e) {
                $error = "Error adding category: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <a href="<?php echo base_url('categories/index.php'); ?>" class="btn btn-secondary btn-sm" id="btn-back-categories">
            <i class="fa-solid fa-arrow-left"></i> Back to Categories
        </a>
        <h1 style="font-size: 2rem; font-weight: 700; margin-top: 1rem; letter-spacing: -0.02em;">Create Category</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" id="cat-add-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form action="<?php echo base_url('categories/add.php'); ?>" method="POST" id="form-add-category">
            <div class="form-group">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Vitrified Tiles" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter category details..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="<?php echo base_url('categories/index.php'); ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-save-category">
                    <i class="fa-solid fa-check"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
