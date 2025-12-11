<?php
require '../_base.php';
$url = '';
$is_admin = get('is_admin');
if ($is_admin) {
    $url = '/admin.php';
} else {
    $url = '/';
}
$_SESSION["alertAppeared"] = false;
logout($url);
