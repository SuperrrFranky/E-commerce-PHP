<?php
require '../_base.php';
$_title = 'Order History';
include '../_head.php';

auth();
updateOrderStatusToCanceled();
$status = req('status');
$selectedId = req('order_id');

if ($selectedId) {
    $stm = $_db->prepare("UPDATE orders SET order_status = 'completed' WHERE order_id = ?");
    $stm->execute([$selectedId]);
}
if ($status) {

    if ($status === 'pending_payment') {
        $stm = $_db->prepare("SELECT o.*, GROUP_CONCAT(DISTINCT p.product_photo SEPARATOR ', ') AS product_photos
        FROM orders AS o
        JOIN order_item AS i ON o.order_id = i.order_id
        JOIN product AS p ON i.product_id = p.product_id
        WHERE o.user_id =? AND o.payment_status = 'pending'
        GROUP BY o.order_id, o.user_id, o.order_date, o.total_amount;");
        $stm->execute([$_user->user_id]);
    } else {
        $stm = $_db->prepare("SELECT o.*, GROUP_CONCAT(DISTINCT p.product_photo SEPARATOR ', ') AS product_photos
                        FROM orders AS o
                        JOIN order_item AS i ON o.order_id = i.order_id
                        JOIN product AS p ON i.product_id = p.product_id
                        WHERE o.user_id =? AND o.order_status = ?
                        GROUP BY o.order_id, o.user_id, o.order_date, o.total_amount;");
        $stm->execute([$_user->user_id, $status]);
    }
} else {

    $stm = $_db->prepare("SELECT o.*, GROUP_CONCAT(DISTINCT p.product_photo SEPARATOR ', ') AS product_photos
                        FROM orders AS o
                        JOIN order_item AS i ON o.order_id = i.order_id
                        JOIN product AS p ON i.product_id = p.product_id
                        WHERE o.user_id =?
                        GROUP BY o.order_id, o.user_id, o.order_date, o.total_amount;");
    $stm->execute([$_user->user_id]);
}

$order_item  = $stm->fetchAll();
?>

<link rel="stylesheet" href="/css/profile.css">

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
    }

    th {
        background-color: #f4f4f4;
    }

    td img {
        max-width: 50px;
        height: auto;
        margin: 5px;
        display: inline-block;
    }

    .profile-details {
        overflow-x: auto;
    }
</style>

<body>
    <div class="sidebar">
        <ul>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="changePassword.php">Change Password</a></li>
            <li><a href="address.php">Address</a></li>
            <li class="selected"><a href="order_history.php">Order History</a></li>
            <li><a href="wishlist.php">Wishlist</a></li>
            <li><a href="userpoint.php">My Points</a></li>
            <li><a href="pendingReview.php">My Reviews</a></li>
            <li><a href="privacy.php">Privacy</a></li>
        </ul>
    </div>


    <div class="main-layt">
        <div class="profile-title">
            <p>Order HIstory</p>
        </div>
        <div class="profile-details">
            <form method="post" action="order_history.php">
                <label for="category-filter">Filter by Status:</label>
                <select name="status" id="status-filter" onchange="this.form.submit()">
                    <option value="" disabled>Select a Status</option>
                    <option value="" <?php if (!$status) echo 'selected' ?>>All</option>
                    <option value="pending" <?php if ($status === 'pending') echo 'selected' ?>>Pending</option>
                    <option value="onTheRoad" <?php if ($status === 'onTheRoad') echo 'selected' ?>>On The Road</option>
                    <option value="completed" <?php if ($status === 'completed') echo 'selected' ?>>Completed</option>
                    <option value="pending_payment" <?php if ($status === 'pending_payment') echo 'selected' ?>>Pending Payment</option>
                    <option value="canceled" <?php if ($status === 'canceled') echo 'selected' ?>>Canceled</option>

                </select>
            </form>
            <?php if (!$order_item) echo 'You have Not Order Yet'; ?>
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Order Date</th>
                        <th>Price (RM)</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_item as $o): ?>
                        <tr>
                            <td>
                                <?php
                                $photos = explode(', ', $o->product_photos);
                                foreach ($photos as $photo): ?>
                                    <img src="/../images/<?= htmlspecialchars($photo) ?>" alt="Product Photo">
                                <?php endforeach; ?>
                            </td>
                            <td><?= htmlspecialchars($o->order_date) ?></td>
                            <td>RM <?= number_format($o->total_amount, 2) ?></td>
                            <td>
                                <?php
                                // Display the status
                                if ($o->order_status === 'canceled') {
                                    echo 'Canceled';
                                } else {
                                    echo ($o->payment_status === 'pending') ? 'Pending Payment' : htmlspecialchars($o->order_status);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($o->order_status === 'canceled'): ?>
                                    <span class="orderActionBtn">Canceled</span>
                                <?php else: ?>
                                    <?php if ($o->order_status === 'ontheroad'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= $o->order_id ?>">
                                            <button type="submit" class="orderActionBtn">Received?</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php
                                    $invoiceFilePath = __DIR__ . "/invoices/Invoice_{$o->order_id}.pdf";

                                    if (!file_exists($invoiceFilePath)) {
                                        $invoiceFilePath = generateInvoicePDF($o->order_id);
                                    }
                                    ?>
                                    <a href="/page/invoices/Invoice_<?= $o->order_id ?>.pdf" target="_blank" download>
                                        <button class="orderActionBtn">Download Invoice</button>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

        </div>
    </div>
</body>

<?php include '../_foot.php'; ?>