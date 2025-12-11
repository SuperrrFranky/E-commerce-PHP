<?php
require '../_base.php';
require '../util/login_functions.php';

if ($_user != null) {
    redirect('/');
}

$loginAttempted = attemptMemberLogin();

$_title = 'Login Page';
$extraScripts = '<link rel="stylesheet" href="/css/login.css">';
include '../_head.php';
?>

<div class="login_main">
    <div class="login_register_form_box">
        <div class="container_tabs">
            <a href="/page/register.php">
                <div class="tab">New Customer</div>
            </a>
            <a>
                <div class="tab active">Returning Customer</div>
            </a>
        </div>
        <h1 class="form_subtitle">Log in to your account</h1>
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

            <div class="remember-me">
                <input type="checkbox" id="keep-signed-in" name="keep-signed-in">
                <label for="keep-signed-in">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">LOG IN</button>
        </form>

    </div>
</div>
</body>