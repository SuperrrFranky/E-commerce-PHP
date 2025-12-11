<?php
require '../../_base.php';
require '../../util/login_functions.php';

if($_user != null){
    redirect('../adminPageSelector.php');
}

$loginAttempted = attemptAdminLogin();

$_title = 'Login Page';
include '../../_head.php';
?>

<link rel="stylesheet" href="../../css/login.css">


<div class="login_main">
    <div class="login_register_form_box">
        <h1 class="form_subtitle">Admin Login</h1>
        <form class="form_account_login" method="post">
            <div class="form_fields">
                <label for="email" class="form_field">Email Address</label>
                <?php html_text('email', 'type="email" class="form-control" required'); ?>
                <?= err('email') ?>

                <label for="password" class="form_field">Password</label>
                <div class="password_container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="show-password" show-password>Show</button>
                </div>
                <?= err('password') ?>
            </div>
            <a href="/page/forgot_password.php" class="forgot-password">Forgot your password?</a>
            <button type="submit" class="btn-login">LOG IN</button>
        </form>

    </div>
</div>
</body>