<?php
// setNewPassword.php
require '../_base.php';
$_title = 'Set New Password';
include '../_head.php';

auth();

// Check if password has been verified
if (!isset($_SESSION['password_verified']) || $_SESSION['password_verified'] !== true) {
    redirect('changePassword.php');
    exit;
}

$user_id = $_user->user_id;

if (is_post()) {
    $new_password = req('new_password');
    $confirm_password = req('confirm_password');

    $temp_pwd = sha1($new_password);

    // Validate inputs
    if ($new_password == '') {
        $_err['new_password'] = 'Required';
    } else if (strlen($new_password) < 5) {
        $_err['new_password'] = '*Must contain 5 characters or more';
    }

    if ($confirm_password == '') {
        $_err['confirm_password'] = 'Required';
    } else if (strlen($confirm_password) < 5) {
        $_err['confirm_password'] = '*Must contain 5 characters or more';
    }

    $stmt = $_db->prepare('SELECT password_hash FROM user WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $old_pwd = $stmt->fetchColumn();

    // Check if the new password matches the current password
    if ($temp_pwd === $old_pwd) {
        $_err['new_password'] = '*Password cannot be the same as the original.';
    }

    if (empty($_err) && $new_password !== $confirm_password) {
        $_err['confirm_password'] = '*Passwords do not match.';
    }

    // Check if passwords match
    if (empty($_err) && $new_password === $confirm_password) {
        $hashed_password = sha1($new_password);

        // Update password in the database
        $stmt = $_db->prepare('UPDATE user SET password_hash = ? WHERE user_id = ?');
        $stmt->execute([$hashed_password, $user_id]);
        temp('info', "Password update successful.");
        // Ensure password_verified always false
        $_SESSION['password_verified'] = false;
        redirect('changePassword.php');
    }
}
?>

<link rel="stylesheet" href="/css/profile.css">

<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li class="selected"><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order.php">Order History</a></li>
        <li><a href="order_history.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>New Password</p>
    </div>
    <div class="profile-details" style="margin:0;">
        <div class="password-lay">
            <h2>Set New Password</h2>
            <form method="post" class="form" novalidate>
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <span class="error-message"><?= err('new_password') ?></span>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <span class="error-message"><?= err('confirm_password') ?></span>
                <button id="pwd-form-button" type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>