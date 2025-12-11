<?php
require '../_base.php';
$_title = 'My Review';
include '../_head.php';

auth();

$user_id = $_user->user_id;
?>

<link rel="stylesheet" href="/css/profile.css">
<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li class="selected"><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>My Point</p>
    </div>
    <div class="profile-details">
        <div class="point-container">
            <div class="point-container-lvl2">
                <div>
                    <b>My Current Point:</b> &nbsp;
                </div>
                <div>
                    <?php print_r($_user->point); ?>
                </div>
            </div>

            <div>
                <a href="product/product.php" class="shop-link">Boost Your Points Now!</a>
            </div>
        </div>

    </div>
</div>

<?php include '../_foot.php'; ?>