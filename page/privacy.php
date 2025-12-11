<?php
require '../_base.php';
$_title = 'My Review';
include '../_head.php';

auth();

$user_id = $_user->user_id;

if (is_post()) {
    $stmt = $_db->prepare('UPDATE user SET status = ? WHERE user_id = ?');
    $stmt->execute(['3', $user_id]);

    temp('info', 'Successful Delete Account.');
    redirect('logout.php');
    redirect('../index.php');
}
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const deleteAccountForm = document.querySelector('.deleteAccount');

        deleteAccountForm.addEventListener('submit', function (event) {
            const confirmed = confirm("Are you sure you want to delete your account?\nThis action cannot be undone.");
            if (!confirmed) {
                event.preventDefault(); // cancel form submission if the user cancel
            }
        });
    });
</script>

<link rel="stylesheet" href="/css/profile.css">
<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li class="selected"><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>Privacy</p>
    </div>
    <div class="profile-details">
        <div class="privacy-container">
            <div>
                <b>Request to delete account</b>
            </div>
            <form method="post" class="deleteAccount">
                <div>
                    <?php html_button('delete_account', '', 'Delete Account', 'delete_account') ?>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include '../_foot.php'; ?>