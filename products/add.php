<?php
// products/add.php

$active_page = 'add_product';
$page_title = "Add Product";
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDBConnection();
$error = null;

$product_code = '';
$name = '';
$category_id = '';
$price = '';
$quantity = '';
$minimum_stock = '5';

// Fetch categories for option list
try {
    $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (\PDOException $e) {
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = trim($_POST['product_code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $minimum_stock = trim($_POST['minimum_stock'] ?? '');
    
    if (empty($product_code) || empty($name) || empty($category_id) || $price === '' || $quantity === '' || $minimum_stock === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid positive number.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = "Quantity must be a non-negative integer.";
    } elseif (!is_numeric($minimum_stock) || $minimum_stock < 0) {
        $error = "Minimum stock threshold must be a non-negative integer.";
    } else {
        // Unique check
        $stmt = $db->prepare("SELECT id FROM products WHERE product_code = ? LIMIT 1");
        $stmt->execute([$product_code]);
        if ($stmt->fetch()) {
            $error = "A product with code '$product_code' already exists.";
        } else {
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("
                    INSERT INTO products (product_code, name, category_id, price, quantity, minimum_stock) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$product_code, $name, $category_id, $price, $quantity, $minimum_stock]);
                $product_id = $db->lastInsertId();
                
                // Log Stock Transaction
                if ($quantity > 0) {
                    $stmt_tx = $db->prepare("
                        INSERT INTO stock_transactions (product_id, transaction_type, quantity) 
                        VALUES (?, 'IN', ?)
                    ");
                    $stmt_tx->execute([$product_id, $quantity]);
                }
                
                $db->commit();
                header("Location: " . base_url('dashboard.php?success=added'));
                exit;
            } catch (\PDOException $e) {
                $db->rollBack();
                $error = "Error adding product: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-secondary btn-sm" id="btn-back-dashboard">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1 style="font-size: 2rem; font-weight: 700; margin-top: 1rem; letter-spacing: -0.02em;">Add New Product</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" id="product-add-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form action="<?php echo base_url('products/add.php'); ?>" method="POST" id="form-add-product">
            <div class="grid-half">
                <div class="form-group">
                    <label for="product_code" class="form-label">Product Code (SKU)</label>
                    <input type="text" id="product_code" name="product_code" class="form-control" placeholder="e.g. FL-CER-05" value="<?php echo htmlspecialchars($product_code); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Travertine Classic" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
            </div>
            
            <div class="grid-half">
                <div class="form-group">
                    <label for="category_id" class="form-label">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price" class="form-label">Unit Price ($)</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" placeholder="0.00" value="<?php echo htmlspecialchars($price); ?>" required>
                </div>
            </div>
            
            <div class="grid-half">
                <div class="form-group">
                    <label for="quantity" class="form-label">Initial Quantity in Stock</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0" value="<?php echo htmlspecialchars($quantity); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="minimum_stock" class="form-label">Minimum Stock Alert Level</label>
                    <input type="number" id="minimum_stock" name="minimum_stock" class="form-control" placeholder="5" value="<?php echo htmlspecialchars($minimum_stock); ?>" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-save-product">
                    <i class="fa-solid fa-check"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
