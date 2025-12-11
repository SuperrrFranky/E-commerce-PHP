<?php
function validateLoginRequest($email, $password)
{
    global $_err;
    $valid = true;

    if ($email === '') {
        $_err['email'] = 'Required';
        $valid = false;
    } else if (!is_email($email)) {
        $errors['email'] = 'Invalid email';
        $valid = false;
    }

    if ($password === '') {
        $_err['password'] = 'Required';
        $valid = false;
    }

    return $valid;
}

function getUserStatus($db, $email)
{
    $stm = $db->prepare(
        'SELECT `status`, failed_attempts, suspension_until FROM user 
        WHERE email = ?
    ');
    $stm->execute([$email]);
    return $stm->fetch();
}

function updateFailedAttempts($db, $email, $failedAttempts, $role)
{
    global $_err;

    $suspensionUntil = null;
    $status = 0;

    if ($failedAttempts >= 3) {
        $suspensionUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $status = 1; // suspended
        $failedAttempts = 0;
        $_err['password'] = 'Account Suspended';
    } else if ($failedAttempts > 0) { // Already verified that failedAttempts < 3
        $remainingAttempts = 3 - $failedAttempts;
        $_err['password'] = "Incorrect Password | $remainingAttempts attempt(s) left";
    }

    $stm = $db->prepare(
        'UPDATE user 
        SET failed_attempts = ?, suspension_until = ?, status = ? 
        WHERE email = ? AND role = ?
    ');
    $stm->execute([$failedAttempts, $suspensionUntil, $status, $email, $role]);
}

function handleSuspension($db, $email, $userData, $role)
{
    global $_err;

    if ($userData->status == 1) {
        if (strtotime($userData->suspension_until) < time()) {
            // Lift suspension
            $stm = $db->prepare(
                'UPDATE user 
                SET status = 0, failed_attempts = 0, suspension_until = NULL 
                WHERE email = ? AND role = ?'
            );
            $stm->execute([$email, $role]);
        } else {
            $_err['password'] = 'Account Suspended, Please try again later';
            return false;
        }
    } else if ($userData->status == 2) {
        $_err['password'] = 'Account Banned';
        return false;
    } else if ($userData->status == 3) {
        $_err['password'] = 'Account Deactivated';
        return false;
    }

    return true;
}

function authenticateUser($db, $email, $password, $role)
{
    $stm = $db->prepare(
        'SELECT * FROM user WHERE email = ? AND password_hash = SHA1(?) AND role = ?'
    );
    $stm->execute([$email, $password, $role]);
    return $stm->fetch();
}

function is_role_matched($db, $email, $role){
    $stm = $db->prepare(
        'SELECT `role` FROM user WHERE email = ?'    
    );
    $stm->execute([$email]);
    $userRole = $stm->fetch();

    //doesnt exist
    if(!$userRole){
        return false;
    }

    //role mismatch
    if(strtolower($userRole->role) != $role){
        return false;
    }

    return true;
}

function attemptLogin($role) //core idea = any failed step -> add error message, abort login logic, return false
{
    global $_db, $_err;

    if (!is_post()) {
        return false;
    }

    $email = req('email');
    $password = req('password');
    $rememberMe = ($role === 'member') ? req('keep-signed-in') : false;

    //validate input
    if (!validateLoginRequest($email, $password)) {
        return false;
    }

    //check if user exists
    if (!is_exists($email, 'user', 'email') || !is_role_matched($_db, $email, $role)) {
        $_err['email'] = 'User not found';
        return false;
    }

    //check account status
    $userData = getUserStatus($_db, $email);
    if (!$userData) {
        $_err['email'] = 'Error retrieving user data';
        return false;
    }

    //handle suspended account
    if (!handleSuspension($_db, $email, $userData, $role)) {
        return false;
    }

    //authentication
    $user = authenticateUser($_db, $email, $password, $role);
    if (!$user) {
        $failedAttempts = $userData->failed_attempts + 1;
        updateFailedAttempts($_db, $email, $failedAttempts, $role);
        return false;
    } else {
        $failedAttempts = 0;
        updateFailedAttempts($_db, $email, $failedAttempts, $role);
    }

    //successful login
    temp('info', 'Login successful');
    $url = ($role === 'member') ? '/' : '../adminPageSelector.php';
    login($user, $url, $rememberMe);

    return true;
}

function attemptMemberLogin()
{
    return attemptLogin('member');
}

function attemptAdminLogin()
{
    return attemptLogin('admin');
}