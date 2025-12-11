<?php
require '../_base.php';
require '../util/verify_captcha.php';

if ($_user != null) {
    redirect('/');
}

if (is_post()) {
    $secret_key = '6LfIGqcqAAAAACRaQeJjLEYZN0To8osW2C1CGnkq';
    $captcha_response = req('g-recaptcha-response');

    if (!validate_captcha($secret_key, $captcha_response)) {
        $_err['captcha'] = 'Failed to verify captcha';
    } else {
        $_genders = [
            'F' => 'Female',
            'M' => 'Male',
        ];

        // Input
        $email      = req('email');
        $password   = req('password');
        $gender     = req('gender');
        $cust_name  = req('cust_name');
        $phone_no   = req('phone_no');

        // Validate id
        if ($email == '') {
            $_err['email'] = 'Required';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid Email';
        } else if (is_exists($email, 'user', 'email')) {
            $_err['email'] = 'Email already registered';
        }

        // Validate password
        if ($password == '') {
            $_err['password'] = 'Required';
        } else if (strlen($password) < 5) {
            $_err['password'] = 'Must contain 5 characters or more';
        }

        // Validate gender
        if ($gender == '') {
            $_err['gender'] = 'Required';
        } else if (!array_key_exists($gender, $_genders)) {
            $_err['name'] = 'Invalid value';
        }

        // Validate name
        if ($cust_name == '') {
            $_err['cust_name'] = 'Required';
        } else if (strlen($cust_name) > 100) {
            $_err['cust_name'] = 'Maximum length 100';
        }

        //Validate Phone Number
        if ($phone_no == '') {
            $_err['phone_no'] = 'Required';
        } else if (!preg_match('/^(1[0-9])-?[0-9]{7,8}$/', $phone_no)) {
            $_err['phone_no'] = 'Invalid Phone Number';
        }

        // Output
        if (!$_err) {
            $time_now = time();
            $hashed_password = sha1($password);

            $stm = $_db->prepare('INSERT INTO user
                                        (username, password_hash, email, phone, role, created_at, updated_at, gender)
                                        VALUES(?, ?, ?, ?, "member", NOW(), NOW(), ?)');
            $stm->execute([$cust_name, $hashed_password, $email, $phone_no, $gender]);
            //PLEASE REMEMBER TO DO EMAIL HTML!!! TODO

            $m = get_mail();
            $m->addAddress($email);
            $m->isHTML(true);
            $m->Subject = 'Account Created';
            
            ob_start();
            $url = base("page/user_login.php");
            $cust_name = htmlspecialchars($cust_name);
            include '../util/register_template.php';
            $emailBody = ob_get_clean();

            $m->Body = $emailBody;
            $m->send();

            temp('info', 'Account created');
            redirect('/page/user_login.php');
        }
    }
}

$_title = 'Register Page';
$extraScripts = '<script src="https://www.google.com/recaptcha/enterprise.js" async defer></script>';
include '../_head.php';
?>

<link rel="stylesheet" href="/css/login.css">

<div class="login_main">
    <div class="login_register_form_box">
        <div class="container_tabs">
            <a>
                <div class="tab active">New Customer</div>
            </a>
            <a href="/page/user_login.php">
                <div class="tab">Returning Customer</div>
            </a>
        </div>
        <h1 class="form_subtitle">Register New Account</h1>
        <form class="form_account_register" id="form_account_register" method="post">
            <div class="form_fields">
                <label for="email" class="form_field">Email Address *</label>
                <?php html_text('email', 'type="email" class="form-control" required'); ?>
                <?= err('email') ?>

                <label for="password" class="form_field">Password *</label>
                <div class="password_container">
                    <input type="password" id="password" name="password" value required>
                    <button type="button" class="show-password">Show</button>
                </div>
                <?= err('password') ?>

                <label class="form_field">Gender *</label>
                <div class="gender_selection">
                    <?php
                    $_genders = [
                        'M' => 'Male',
                        'F' => 'Female',
                    ];
                    html_radios('gender', $_genders);
                    ?>
                </div>
                <?= err('gender') ?>

                <label for="cust_name" class="form_field">Name *</label>
                <?php html_text('cust_name', 'class="form-control" required'); ?>
                <?= err('cust_name') ?>

                <label for="phone_no" class="form_field">Phone Number *</label>
                <div class="phoneNo_field">
                    <p class="sub_form_field">+60</p>
                    <?php html_text('phone_no', 'class="form-control" required'); ?>
                </div>
                <?= err('phone_no') ?>
            </div>
            <div class="g-recaptcha" data-sitekey="6LfIGqcqAAAAAKvyC3ekETdlwpl6TX1FfuQPLscF" data-action="register"></div>
            <?= err('captcha') ?>
            <button class="btn-login" type='submit'>CONFIRM AND CONTINUE</button>
        </form>
    </div>
</div>
</body>