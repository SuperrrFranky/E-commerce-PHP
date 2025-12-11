<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php

    require '../../_base.php';
    $_title = 'Modify Product Page';
    include '../../_head.php';
    auth("admin");
    $statusOptions = [
        1 => 'Active',
        0 => 'Inactive'
    ];

    if(is_get())
    {
        $id = req('id');
    
        $stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
        $stm->execute([$id]);
        $s = $stm->fetch();
    
        if (!$s) {
            temp('info', 'Illegal modification');
            redirect('productList.php');
        }
        $GLOBALS['productName' ] = $s->name;
        $GLOBALS['productPrice' ] = $s->price;
        $GLOBALS['productQuantity' ] = $s->quantity;
        $GLOBALS['productThreshold' ] = $s->threshold;
        $GLOBALS['productDesc' ] = $s->description;
        $GLOBALS['productStatus' ] = $s->status;
        $GLOBALS['productCategory'] = $s->category_id;
    } 

    $catList = $_db->query('SELECT * FROM category')->fetchAll();
    $optList = [];
    foreach ($catList as $d) {
        if ($d->status != false) {
            $optList[$d->category_id] = $d->name;
        }
    }

    if (is_post()) {
        $id = req('id');
        $stmid = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
        $stmid->execute([$id]);
        $s = $stmid->fetch();

        $stm = $_db->prepare('UPDATE product
                    SET name = ?, description = ?, price = ?, quantity = ?,threshold = ?, status = ?, category_id = ?, product_photo = ?
                    WHERE product_id = ?');

        $st = $_db->prepare('UPDATE product
                            SET name = ?, description = ?, price = ?, quantity = ?,threshold = ?, status = ?, category_id = ?
                            WHERE product_id = ?');
        
            $name  = req('productName');
            $desc    = req('productDesc');
            $price = req('productPrice');
            $quantity = req('productQuantity' );
            $threshold = req('productThreshold' );
            $stat       = req('productStatus' );
            $category = req('productCategory' );
            $f     = get_file('productphoto');

            $GLOBALS['productName' ] = $name;
            $GLOBALS['productPrice' ] = $price;
            $GLOBALS['productQuantity' ] = $quantity;
            $GLOBALS['productThreshold' ] = $threshold;
            $GLOBALS['productDesc' ] = $desc;
            $GLOBALS['productStatus' ] = $stat;
            $GLOBALS['productCategory'] = $category;

            if ($name == '') {
                $_err['productName'] = 'Required';
            } 
             else if (strlen($name) > 100) {
                $_err['productName' ] = 'Maximum 100 characters';
             }

            if ($desc == '') {
                $_err['productDesc' ] = 'Required';
            } else if (strlen($desc) > 150) {
                $_err['productDesc' ] = 'Maximum 150 characters';
            } 

            if ($price == '') {
                $_err['productPrice' ] = 'Required';
            } else if (!is_money($price)) {
                $_err['productPrice' ] = 'Must be money';
            } else if ($price < 0.01 | $price > 10000.00) {
                $_err['productPrice' ] = 'Must between 0.01 - 10000.00';
            } 

            if ($quantity == '') {
                $_err['productQuantity'] = 'Required';
            } else if ($quantity < 0 | $quantity > 10000) {
                $_err['productQuantity' ] = 'Must between 1 - 10000';
            } 

            if ($threshold == '') {
                $_err['productThreshold'] = 'Required';
            } else if ($threshold < 1 | $threshold > 10000) {
                $_err['productThreshold'] = 'Must between 1 - 10000';
            } 
            
            if ($f) {
                if (!str_starts_with($f->type, 'image/')) {
                    $_err['productphoto' . $s->product_id] = 'Must be image';
                } else if ($f->size > 1 * 1024 * 1024) {
                    $_err['productphoto' . $s->product_id] = 'Maximum 1MB';
                }
        }

        if (!$_err) {
                if ($f) {
                    unlink("../../images/$s->product_photo");
                    $photo = save_photo($f, '../../images');
                    $stm->execute([$name, $desc, $price, $quantity, $threshold, $stat, $category, $photo, $id]);
                } else {
                    $st->execute([$name, $desc, $price, $quantity, $threshold, $stat, $category, $id]);
                }
                temp('info', 'Product modified');
                redirect('productList.php');
            }
            
        }
    ?>
</head>

<body>
    <h2>Modify Product</h2>
    <?= html_button('back', 'modify', 'back', 'btn', 'data-get=productList.php')  ?>
    <form method="post" enctype="multipart/form-data" novalidate>
        <table class="table">
            <tr>
                <th>Product ID</th>
                <th>Name</th>
                <th>Price(RM)</th>
                <th>Quantity</th>
                <th>Minimum Threshold</th>
                <th>Description</th>
                <th>Status</th>
                <th>Category</th>
                <th>Image</th>

            </tr>

                <tr>
                    <td><?= $id ?></td>
                    <td><?= html_text('productName') ?> <?= err('productName') ?></td>
                    <td><?= html_number('productPrice', 0.01, 10000.00, 0.01) ?><?= err('productPrice') ?></td>
                    <td><?= html_number('productQuantity', 1, 10000, 1) ?><?= err('productQuantity') ?></td>
                    <td><?= html_number('productThreshold', 1, 10000, 1) ?><?= err('productThreshold') ?></td>
                    <td><?= html_textarea('productDesc') ?> <?= err('productDesc') ?></td>
                    <td><?= html_select('productStatus' , $statusOptions, null); ?></td>
                    <td> <?= html_select('productCategory', $optList, null); ?></td>
                    <td><label class="upload" tabindex="0"><?= html_file('productphoto', 'image/*', 'hidden') ?><img src="../../images/<?= $s->product_photo ?>"></label></td>
                </tr>
        </table>
        <?= html_button('submit', 'submit', 'submit', 'btn', 'data-confirm') ?>
    </form>
    <br>
</body>
<?php
include '../../_foot.php';
