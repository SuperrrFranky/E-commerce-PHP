<?php
require '../_base.php';
$_title = 'My Profile';
include '../_head.php';

auth();

// Temporary until session is implemented
$user_id = $_user->user_id;

$categories = $_db->query('SELECT category_id, name FROM category WHERE status = 1')->fetchAll();

$selected_category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

$query = 'SELECT p.product_id, p.name, p.description, p.quantity, p.threshold, p.price, p.product_photo, p.status, p.category_id, w.user_id
                        FROM wishlist w
                        JOIN product p
                        ON  w.product_id = p.product_id
                        WHERE w.user_id = ?';

$temp_paramtr = [$user_id];

if ($selected_category_id) {
    // append query
    $query .= ' AND p.category_id = ?';
    // append temporary parameter for above query
    $temp_paramtr[] = $selected_category_id;
}

// only here prepare the final complete query
$stmt = $_db->prepare($query);
$stmt->execute($temp_paramtr);
$wishlist_product = $stmt->fetchAll();


foreach ($wishlist_product as $product) {
    $GLOBALS['product_id' . $product->product_id] = $product->product_id;
}

if (is_post()) {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'delete') {
        if (isset($_POST['product_id'])) {
            $product_id = $_POST['product_id'];  // get the product_id from the form

            // Prepare and execute the delete query
            $stmt = $_db->prepare('DELETE FROM wishlist WHERE product_id = ? AND user_id = ?');
            $stmt->execute([$product_id, $user_id]);

            // Redirect to the wishlist page after deletion
            temp('info', 'Item deleted successfully.');
            redirect('wishlist.php');
        }
    }

    if ($action === 'batch_delete') {
        $product_ids = isset($_POST['product_ids']) ? explode(',', $_POST['product_ids']) : [];

        if (!empty($product_ids)) {
            $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
            $stmt = $_db->prepare("DELETE FROM wishlist WHERE product_id IN ($placeholders) AND user_id = ?");
            $stmt->execute([...$product_ids, $user_id]);

            temp('info', 'Selected items deleted successfully.');
            redirect('wishlist.php');
        }
    }
}

?>

<link rel="stylesheet" href="/css/profile.css">
<script>
    // handle remove action without trigger entire link
    function removeFromWishlist(event, productId) {

        // Find the form associated with this product
        var form = document.getElementById("delete-wishlistProduct-" + productId);

        // Set the product_id value before submitting the form
        form.querySelector('input[name="product_id"]').value = productId;

        // Submit the form to trigger the PHP delete logic
        form.submit();
    }

    let batchMode = false;

    function toggleBatchMode() {
        batchMode = !batchMode;

        // Toggle checkboxes
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.style.display = batchMode ? 'inline-block' : 'none';
        });

        // Toggle batch delete button
        const deleteButton = document.getElementById('batch-delete-btn');
        deleteButton.style.display = batchMode ? 'block' : 'none';

        const resetButton = document.getElementById('cancel-btn');
        resetButton.style.display = batchMode ? 'block' : 'none';
    }

    function submitBatchDelete() {
        const selectedProducts = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            selectedProducts.push(checkbox.value);
        });

        if (selectedProducts.length === 0) {
            alert('Please select at least one product to delete.');
            return;
        }

        if (confirm('Are you sure you want to delete the selected items?')) {
            // Create a hidden form and submit it
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = `
                <input type="hidden" name="action" value="batch_delete">
                <input type="hidden" name="product_ids" value="${selectedProducts.join(',')}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li class="selected"><a href="wishlist.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>Wishlist</p>
    </div>

    <div class="profile-details">
        <div class="wishlist-option">
            <div class="filter-section">
                <form method="get" action="wishlist.php">
                    <label for="category-filter">Filter by Category:</label>
                    <select name="category_id" id="category-filter" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->category_id ?>"
                                <?= $selected_category_id == $category->category_id ? 'selected' : '' ?>>
                                <?= $category->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <button class="top-left-btn" onclick="toggleBatchMode()">Select item</button>
        </div>



        <?php if (empty($wishlist_product)): ?>
            <hr>
            <p>No products have been added to this category.</p>
        <?php else: ?>
            <div class="wishlist-container">
                <?php foreach ($wishlist_product as $product): ?>
                    <div class="product">
                        <!-- Checkbox for batch mode -->
                        <input type="checkbox" class="product-checkbox" value="<?= $product->product_id ?>">
                        <a href="product/productdetail.php?product_id=<?= $product->product_id ?>">
                            <img src="../../images/<?= $product->product_photo ?>" alt="<?= $product->name ?>" class="product-image">
                            <div class="product-info">
                                <h3 class="product-name"><?= $product->name ?></h3>
                                <p class="product-description"><?= $product->description ?></p>
                                <p class="product-price">RM<?= $product->price ?></p>
                                <p class="product-quantity">Available: <?= $product->quantity ?></p>
                            </div>
                        </a>

                        <!-- Individual Remove Button -->
                        <div class="product-actions">
                            <form id="delete-wishlistProduct-<?= $product->product_id ?>" method="post" novalidate>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="">
                                <?= html_button('remove-from-wishlist', '', 'Remove', '', 'onclick="removeFromWishlist(event, ' . $product->product_id . ')"') ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>

            <!-- Batch Delete Button -->
            <div class="wishlist-delete-option">
                <button id="batch-delete-btn" onclick="submitBatchDelete()">Delete Selected</button>
                <?= html_button('cancel-btn', "", "Cancel", 'cancel-btn', 'type="reset" ') ?>
            </div>

    </div>
</div>

<?php include '../_foot.php'; ?>