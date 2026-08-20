<?php
// products/edit.php

$active_page = 'dashboard';
$page_title = "Edit Product";
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDBConnection();
$error = null;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: " . base_url('dashboard.php?error=invalid_input'));
    exit;
}

// Fetch existing product
$stmt = $db->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: " . base_url('dashboard.php?error=not_found'));
    exit;
}

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
        // Unique check excluding current product
        $stmt = $db->prepare("SELECT id FROM products WHERE product_code = ? AND id != ? LIMIT 1");
        $stmt->execute([$product_code, $id]);
        if ($stmt->fetch()) {
            $error = "A product with code '$product_code' already exists.";
        } else {
            try {
                $db->beginTransaction();
                
                $old_quantity = intval($product['quantity']);
                $new_quantity = intval($quantity);
                $diff = $new_quantity - $old_quantity;
                
                $stmt = $db->prepare("
                    UPDATE products 
                    SET product_code = ?, name = ?, category_id = ?, price = ?, quantity = ?, minimum_stock = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$product_code, $name, $category_id, $price, $quantity, $minimum_stock, $id]);
                
                // Add Stock Transaction if quantity changed
                if ($diff > 0) {
                    $stmt_tx = $db->prepare("
                        INSERT INTO stock_transactions (product_id, transaction_type, quantity) 
                        VALUES (?, 'IN', ?)
                    ");
                    $stmt_tx->execute([$id, $diff]);
                } elseif ($diff < 0) {
                    $stmt_tx = $db->prepare("
                        INSERT INTO stock_transactions (product_id, transaction_type, quantity) 
                        VALUES (?, 'OUT', ?)
                    ");
                    $stmt_tx->execute([$id, abs($diff)]);
                }
                
                $db->commit();
                header("Location: " . base_url('dashboard.php?success=edited'));
                exit;
            } catch (\PDOException $e) {
                $db->rollBack();
                $error = "Error updating product: " . $e->getMessage();
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
        <h1 style="font-size: 2rem; font-weight: 700; margin-top: 1rem; letter-spacing: -0.02em;">Edit Product</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" id="product-edit-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form action="<?php echo base_url('products/edit.php?id=' . $id); ?>" method="POST" id="form-edit-product">
            <div class="grid-half">
                <div class="form-group">
                    <label for="product_code" class="form-label">Product Code (SKU)</label>
                    <input type="text" id="product_code" name="product_code" class="form-control" value="<?php echo htmlspecialchars($product['product_code']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
            </div>
            
            <div class="grid-half">
                <div class="form-group">
                    <label for="category_id" class="form-label">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price" class="form-label">Unit Price ($)</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
            </div>
            
            <div class="grid-half">
                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity in Stock</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="minimum_stock" class="form-label">Minimum Stock Alert Level</label>
                    <input type="number" id="minimum_stock" name="minimum_stock" class="form-control" value="<?php echo htmlspecialchars($product['minimum_stock']); ?>" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-update-product">
                    <i class="fa-solid fa-check"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
