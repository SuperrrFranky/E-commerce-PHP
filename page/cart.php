<?php
require '../_base.php';
$_title = 'Cart';
include '../_head.php';

auth();
?>

<style>
    body {
        background-color: gray;
    }
</style>

<body>

    <div class="cart-container ">
    </div>

    <?php
    include '../_foot.php';
    ?>
</body>