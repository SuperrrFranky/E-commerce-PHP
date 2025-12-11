<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $email = req('email');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (!is_exists($email, 'user', 'email')) {
        $_err['email'] = 'Email not registered';
    }

    // Send reset token
    if (!$_err) {
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        //generate token id
        $id = sha1(uniqid() . rand());

        //renew token (5 minutes)
        $stm = $_db->prepare('
            DELETE FROM token WHERE user_id = ?;

            INSERT INTO token (id, expire, user_id)
            VALUES (?, ADDTIME(NOW(), "00:05"), ?);
        ');
        $stm->execute([$u->user_id, $id, $u->user_id]);

        $is_admin = $u->role == 'admin';

        //token url
        $url = base("util/token.php?id=$id&is_admin=$is_admin");

        $m = get_mail();
        $m->addAddress($u->email, $u->username);
        $m->Subject = 'Reset Password';
        $m->isHTML(true);
        $m->Body = "
            <p>Dear $u->username,<p>
            <h1 style='color: red'>Reset Password</h1>
            <p>
                Please click <a href='$url'>here</a>
                to reset your password.
            </p>
            <p>From, Admin</p>
        ";
        $m->send();

        temp('info', 'Email sent');
        if ($is_admin) {
            redirect('/admin.php');
        } else {
            redirect('../page/user_login.php');
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
                <label for="email" class="form_field">Email Address</label>
                <?php html_text('email', 'class="form-control" required'); ?>
                <?= err('email') ?>
            </div>
            <button type="submit" class="btn-login">Reset Password</button>
        </form>

    </div>
</div>
</body>