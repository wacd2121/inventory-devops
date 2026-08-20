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

    $total_products = $db->query(
        "SELECT COUNT(*) FROM products"
    )->fetchColumn();

    $total_stock = $db->query(
        "SELECT SUM(quantity) FROM products"
    )->fetchColumn() ?: 0;

    $low_stock_count = $db->query(
        "SELECT COUNT(*) 
         FROM products 
         WHERE quantity <= minimum_stock"
    )->fetchColumn();

    $total_categories = $db->query(
        "SELECT COUNT(*) FROM categories"
    )->fetchColumn();

} catch (\PDOException $e) {

    // If table doesn't exist yet or connection fails
    $total_products = 0;
    $total_stock = 0;
    $low_stock_count = 0;
    $total_categories = 0;
}


// ============================================================
// SEARCH AND STOCK FILTER
// ============================================================

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$stock_status = isset($_GET['stock_status'])
    ? trim($_GET['stock_status'])
    : '';


// Fetch Products
try {

    $conditions = [];

    $params = [];


    // --------------------------------------------------------
    // Product Search
    // --------------------------------------------------------

    if ($search !== '') {

        $conditions[] = "
            (
                p.name LIKE ?
                OR p.product_code LIKE ?
                OR c.name LIKE ?
            )
        ";

        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }


    // --------------------------------------------------------
    // Stock Status Filter
    // --------------------------------------------------------

    if ($stock_status === 'out_of_stock') {

        // No stock available
        $conditions[] = "p.quantity = 0";

    } elseif ($stock_status === 'low_stock') {

        // Stock is above zero but at/below minimum level
        $conditions[] = "
            p.quantity > 0
            AND p.quantity <= p.minimum_stock
        ";

    } elseif ($stock_status === 'in_stock') {

        // Stock is above minimum level
        $conditions[] = "
            p.quantity > p.minimum_stock
        ";
    }


    // --------------------------------------------------------
    // Base Product Query
    // --------------------------------------------------------

    $sql = "
        SELECT
            p.*,
            c.name AS category_name
        FROM products p
        JOIN categories c
            ON p.category_id = c.id
    ";


    // Add WHERE conditions when filters are selected
    if (!empty($conditions)) {

        $sql .= " WHERE " . implode(" AND ", $conditions);
    }


    // Sort products alphabetically
    $sql .= " ORDER BY p.name ASC";


    // Prepare and execute query
    $stmt = $db->prepare($sql);

    $stmt->execute($params);

    $products = $stmt->fetchAll();

} catch (\PDOException $e) {

    $products = [];
}


require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     DASHBOARD HEADER
========================================================= -->

<div
    class="dashboard-header"
    style="
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    "
>

    <div>

        <h1
            style="
                font-size: 2rem;
                font-weight: 700;
                letter-spacing: -0.02em;
            "
        >
            Inventory Dashboard
        </h1>

        <p style="color: var(--text-secondary);">
            Real-time monitoring and stock level analytics.
        </p>

    </div>


    <div style="display: flex; gap: 0.75rem;">

        <a
            href="<?php echo base_url('categories/add.php'); ?>"
            class="btn btn-secondary"
        >
            <i class="fa-solid fa-folder-plus"></i>
            Add Category
        </a>


        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>

            <a
                href="<?php echo base_url('products/add.php'); ?>"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-plus"></i>
                Add Product
            </a>

        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     STATS GRID
========================================================= -->

<div class="dashboard-grid">


    <!-- Total Products -->

    <div class="stat-card">

        <div class="stat-icon">

            <i class="fa-solid fa-cubes"></i>

        </div>

        <div class="stat-info">

            <span class="stat-label">
                Total Products
            </span>

            <span class="stat-value">
                <?php echo number_format($total_products); ?>
            </span>

        </div>

    </div>


    <!-- Total Stock -->

    <div class="stat-card stat-stock">

        <div class="stat-icon">

            <i class="fa-solid fa-warehouse"></i>

        </div>

        <div class="stat-info">

            <span class="stat-label">
                Total Stock
            </span>

            <span class="stat-value">
                <?php echo number_format($total_stock); ?>
            </span>

        </div>

    </div>


    <!-- Low Stock -->

    <div class="stat-card stat-low-stock">

        <div
            class="stat-icon"
            style="color: var(--danger);"
        >

            <i class="fa-solid fa-triangle-exclamation"></i>

        </div>

        <div class="stat-info">

            <span class="stat-label">
                Low Stock Products
            </span>

            <span
                class="stat-value"
                style="
                    color:
                    <?php
                    echo $low_stock_count > 0
                        ? 'var(--danger)'
                        : 'inherit';
                    ?>;
                "
            >
                <?php echo number_format($low_stock_count); ?>
            </span>

        </div>

    </div>


    <!-- Categories -->

    <div class="stat-card stat-categories">

        <div class="stat-icon">

            <i class="fa-solid fa-tags"></i>

        </div>

        <div class="stat-info">

            <span class="stat-label">
                Categories
            </span>

            <span class="stat-value">
                <?php echo number_format($total_categories); ?>
            </span>

        </div>

    </div>

</div>


<!-- =========================================================
     PRODUCT CATALOGUE
========================================================= -->

<div
    class="card"
    style="padding: 1.5rem;"
>


    <!-- =====================================================
         SEARCH AND FILTER
    ====================================================== -->

    <div class="actions-row">

        <h2
            style="
                font-size: 1.25rem;
                font-weight: 600;
            "
        >
            Product Catalogue
        </h2>


        <form
            action="<?php echo base_url('dashboard.php'); ?>"
            method="GET"
            class="search-form"
        >


            <!-- Search Input -->

            <div class="input-group">

                <i
                    class="fa-solid fa-magnifying-glass input-icon"
                ></i>

                <input
                    type="text"
                    name="search"
                    id="search-input"
                    class="form-control form-control-icon"
                    placeholder="Search product name, code or category..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>


            <!-- Stock Status Filter -->

            <select
                name="stock_status"
                class="form-control"
            >

                <option
                    value=""
                    <?php
                    echo $stock_status === ''
                        ? 'selected'
                        : '';
                    ?>
                >
                    All Stock
                </option>


                <option
                    value="in_stock"
                    <?php
                    echo $stock_status === 'in_stock'
                        ? 'selected'
                        : '';
                    ?>
                >
                    In Stock
                </option>


                <option
                    value="low_stock"
                    <?php
                    echo $stock_status === 'low_stock'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Low Stock
                </option>


                <option
                    value="out_of_stock"
                    <?php
                    echo $stock_status === 'out_of_stock'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Out of Stock
                </option>

            </select>


            <!-- Search Button -->

            <button
                type="submit"
                class="btn btn-secondary"
            >
                Search
            </button>


            <!-- Clear Filters -->

            <?php if ($search !== '' || $stock_status !== ''): ?>

                <a
                    href="<?php echo base_url('dashboard.php'); ?>"
                    class="btn btn-secondary"
                    title="Clear filters"
                >
                    <i class="fa-solid fa-xmark"></i>
                </a>

            <?php endif; ?>


        </form>

    </div>


    <!-- =====================================================
         PRODUCT TABLE
    ====================================================== -->

    <div class="table-wrapper">

        <table class="table">

            <thead>

                <tr>

                    <th style="width: 15%">
                        Product Code
                    </th>

                    <th style="width: 25%">
                        Product Name
                    </th>

                    <th style="width: 18%">
                        Category
                    </th>

                    <th style="width: 12%">
                        Price
                    </th>

                    <th style="width: 15%">
                        Stock Level
                    </th>


                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>

                        <th
                            style="
                                text-align: right;
                                width: 15%;
                            "
                        >
                            Actions
                        </th>

                    <?php endif; ?>

                </tr>

            </thead>


            <tbody>


                <?php if (count($products) > 0): ?>


                    <?php foreach ($products as $product): ?>


                        <?php

                        /*
                         * Determine stock status
                         */

                        $quantity = (int) $product['quantity'];

                        $minimum_stock = (int) $product['minimum_stock'];


                        if ($quantity === 0) {

                            $stock_status_label = 'OUT OF STOCK';

                            $stock_badge_class = 'badge-danger';

                            $stock_color = 'var(--danger)';

                        } elseif ($quantity <= $minimum_stock) {

                            $stock_status_label = 'LOW STOCK';

                            $stock_badge_class = 'badge-danger';

                            $stock_color = 'var(--danger)';

                        } else {

                            $stock_status_label = 'IN STOCK';

                            $stock_badge_class = 'badge-success';

                            $stock_color = 'var(--text-primary)';
                        }

                        ?>


                        <tr class="product-row">


                            <!-- Product Code -->

                            <td
                                class="product-code"
                                style="
                                    font-weight: 600;
                                    font-family: monospace;
                                    color: var(--text-primary);
                                "
                            >
                                <?php
                                echo htmlspecialchars(
                                    $product['product_code']
                                );
                                ?>
                            </td>


                            <!-- Product Name -->

                            <td
                                class="product-name"
                                style="
                                    font-weight: 500;
                                    color: var(--text-primary);
                                "
                            >
                                <?php
                                echo htmlspecialchars(
                                    $product['name']
                                );
                                ?>
                            </td>


                            <!-- Category -->

                            <td class="product-category">

                                <?php
                                echo htmlspecialchars(
                                    $product['category_name']
                                );
                                ?>

                            </td>


                            <!-- Price -->

                            <td>

                                $
                                <?php
                                echo number_format(
                                    $product['price'],
                                    2
                                );
                                ?>

                            </td>


                            <!-- Stock Level -->

                            <td>

                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 0.5rem;
                                        flex-wrap: wrap;
                                    "
                                >


                                    <span
                                        style="
                                            font-weight: 600;
                                            color:
                                            <?php
                                            echo $stock_color;
                                            ?>;
                                        "
                                    >
                                        <?php
                                        echo number_format(
                                            $quantity
                                        );
                                        ?>
                                    </span>


                                    <span
                                        style="
                                            color: var(--text-secondary);
                                            font-size: 0.8rem;
                                        "
                                    >
                                        / min:
                                        <?php
                                        echo number_format(
                                            $minimum_stock
                                        );
                                        ?>
                                    </span>


                                    <span
                                        class="badge
                                        <?php
                                        echo $stock_badge_class;
                                        ?>"
                                    >
                                        <?php
                                        echo $stock_status_label;
                                        ?>
                                    </span>


                                </div>

                            </td>


                            <!-- Admin Actions -->

                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>

                                <td
                                    style="
                                        text-align: right;
                                    "
                                >

                                    <div
                                        class="table-actions"
                                        style="
                                            justify-content: flex-end;
                                        "
                                    >


                                        <!-- Edit -->

                                        <a
                                            href="<?php
                                            echo base_url(
                                                'products/edit.php?id='
                                                . $product['id']
                                            );
                                            ?>"
                                            class="table-action-link"
                                            title="Edit Product"
                                        >

                                            <i
                                                class="fa-solid fa-pen-to-square"
                                            ></i>

                                        </a>


                                        <!-- Delete -->

                                        <a
                                            href="<?php
                                            echo base_url(
                                                'products/delete.php?id='
                                                . $product['id']
                                            );
                                            ?>"
                                            class="table-action-link delete"
                                            title="Delete Product"
                                        >

                                            <i
                                                class="fa-solid fa-trash"
                                            ></i>

                                        </a>


                                    </div>

                                </td>

                            <?php endif; ?>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- No Products -->

                    <tr>

                        <td
                            colspan="<?php
                            echo ($_SESSION['user_role'] ?? '') === 'admin'
                                ? 6
                                : 5;
                            ?>"
                            style="
                                text-align: center;
                                padding: 3rem;
                                color: var(--text-secondary);
                            "
                        >

                            <i
                                class="fa-solid fa-circle-info"
                                style="
                                    font-size: 2rem;
                                    margin-bottom: 0.5rem;
                                    display: block;
                                    opacity: 0.5;
                                "
                            ></i>


                            <?php if ($search !== '' || $stock_status !== ''): ?>

                                No products match the selected filters.

                            <?php else: ?>

                                No products found in the catalogue.

                            <?php endif; ?>


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