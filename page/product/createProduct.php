<head>
<?php
include '../../_base.php';
auth("admin");
// ----------------------------------------------------------------------------

$catList=$_db->query('SELECT * FROM category')->fetchAll();
$optList =[];
foreach($catList as $s)
{
    if($s->status !=false){
    $optList[$s->category_id]=$s->name;
    }
}

if (is_post()) {
    $name  = req('name');
    $desc    = req('description');
    $price = req('price');
    $quantity = req('quantity');
    $threshold = req('threshold');
    $category = req('category');
    $f     = get_file('photo');

    if ($name == '') {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    if ($desc == '') {
        $_err['description'] = 'Required';
    }
    else if (strlen($desc) > 150) {
        $_err['description'] = 'Maximum 150 characters';
    }

    if ($price == '') {
        $_err['price'] = 'Required';
    }
    else if (!is_money($price)) { 
        $_err['price'] = 'Must be money';
    }
    else if ($price<0.01 | $price >10000.00 ) { 
        $_err['price'] = 'Must between 0.01 - 10000.00';
    }

    if ($quantity == '') {
        $_err['quantity'] = 'Required';
    }
    else if ($quantity<1 | $quantity >10000 ) { 
        $_err['quantity'] = 'Must between 1 - 10000';
    }

    if ($threshold == '') {
        $_err['threshold'] = 'Required';
    }
    else if ($threshold<1 | $threshold >10000 ) { 
        $_err['threshold'] = 'Must between 1 - 10000';
    }

    if ($category == '') {
        $_err['category'] = 'Required';
    }

    if (!$f) {
        $_err['photo'] = 'Required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    if (!$_err) { 
       $photo = save_photo($f,'../../images');

        $stm = $_db->prepare('
            INSERT INTO product (name,description,quantity,threshold,price,product_photo,status,category_id)
        VALUES (?, ?, ?,?,?,?,?,?)
        ');
        $stm->execute([$name,$desc,$quantity,$threshold, $price, $photo,TRUE,$category]);

        temp('info', 'Product inserted');
        redirect('productList.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'Product | Insert';
include '../../_head.php';
?>
<link href="../../css/base.css" rel="stylesheet">
</head>
<body>
<h2>Create Product</h2>
<p>
    <?= html_button('back','back','back','btn','data-get=../adminPageSelector.php')?>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="name">Name</label>
    <?= html_text('name', 'maxlength="100" placeholder="Max 100 character"') ?>
    <?= err('name') ?>

    <label for="description">Description</label>
    <?= html_textarea('description', 'maxlength="150" placeholder="Max 150 character"') ?>
    <?= err('description') ?>

    <label for="price">Price(RM)</label>
    <?= html_number('price',0.01,10000.00,0.01,'placeholder="0.01-10000.00"') ?>
    <?= err('price') ?>

    <label for="quantity">Quantity</label>
    <?= html_number('quantity',1,10000,1,'placeholder="1-10000"') ?>
    <?= err('quantity') ?>

    <label for="threshold">Minimum threshold</label>
    <?= html_number('threshold',1,10000,1,'placeholder="1-10000"') ?>
    <?= err('threshold') ?>

    <label for="category">Category</label>
    <?= html_select('category', $optList); ?>
    <?= err('category') ?>

    <label for="photo">Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('photo', 'image/*', 'hidden') ?>
        <img src="../../images/photo.jpg">
    </label>
    <?= err('photo') ?>
    
    <section>
    <?= html_button('productButton','','Submit','btn');
         html_button('productButton','','Reset','btn','type=reset');?>
    </section>
</form>
</body>
<?php
include '../../_foot.php';