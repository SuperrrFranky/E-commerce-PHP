<?php
require '_base.php';
if($_user){
    redirect('/index.php');
}
redirect('/page/admin/admin_login.php');
?>