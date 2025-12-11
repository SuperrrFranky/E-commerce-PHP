<?php
include '../_base.php';

$_db->query('DELETE FROM token WHERE expire < NOW()');

$id = req('id');
$is_admin = req('is_admin');

//check token validity
if (!is_exists($id, 'token', 'id')) {
    temp('info', 'Invalid token. Try again');
    redirect('/');
}

if (is_post()) {
    $password = req('password');
    $confirm  = req('confirm');

    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) < 5) {
        $_err['password'] = 'Must contain 5 characters or more';
    }

    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    } else if (strlen($confirm) < 5) {
        $_err['confirm'] = 'Must contain 5 characters or more';
    } else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    if (!$_err) {
        // update user pw based on token id -> delete token
        $stm = $_db->prepare('
            UPDATE user
            SET password_hash = SHA1(?)
            WHERE user_id = (SELECT user_id FROM token WHERE id = ?);

            DELETE FROM token WHERE id = ?;
        ');
        $stm->execute([$password, $id, $id]);

        temp('info', 'Password Changed Successfully');
        if ($is_admin) {
            redirect('/admin.php');
        } else {
            redirect('/page/user_login.php');
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Reset Password';
$extraScripts = '<link rel="stylesheet" href="/css/login.css">';
include '../_head.php';
?>

<div class="login_main">
    <div class="login_register_form_box">
        <h1 class="form_subtitle">Reset Your Password</h1>
        <form class="form_account_register" method="post">
            <div class="form_fields">
                <label for="password">Password</label>
                <?= html_password('password', 'class="form-control" maxlength="100" required') ?>
                <?= err('password') ?>

                <label for="confirm">Confirm</label>
                <?= html_password('confirm', 'class="form-control" maxlength="100" required') ?>
                <?= err('confirm') ?>
            </div>
            <button type="submit" class="btn-login">Change Password</button>
        </form>

    </div>
</div>

</body>