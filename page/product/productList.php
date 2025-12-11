<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php

require '../../_base.php';
$_title = 'Product List Page';
include '../../_head.php';
auth("admin");
$_category=$_db->query('SELECT category_id,name FROM category')->fetchAll(PDO::FETCH_KEY_PAIR);
$fields = [
    'product_id' => 'Product_id',
    'name'       => 'Name',
    'price'=>'Price(RM)',
    'quantity'=>'Quantity',
    'threshold'=>'Mininum Threshold',
    'description'=>'Description',
    'status'     => 'Status',
    'Category'=>'Category',
    'product_photo'=>'Product Photo',
];
$cat = req('cat');
$name = req('name');
$sort = req('sort');
key_exists($sort, $fields) || $sort = 'product_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);
require_once '../../lib/SimplePager.php';
$p = new SimplePager("SELECT * FROM product where (category_id =? or ?) and name like ?  ORDER BY $sort $dir", [$cat,$cat==null,"%$name%"], 10, $page);
$arr = $p->result;
?>
</head>

<body>
<?=html_button('back','back','back','btn','data-get=../adminPageSelector.php') ?>
<p>
    <?= $p->count ?> of <?= $p->item_count ?> record(s) |
    Page <?= $p->page ?> of <?= $p->page_count ?>
</p>

<section>
<?=html_button('create','','create','btn','data-get=createproduct.php') ?>
<?=html_button('del','','delete','btn','data-get=deleteproduct.php') ?>
</section>
<br>

<form id=prodList>
<?= html_select('cat', $_category,'All');?>
<?= html_search('name','placeholder="Search"') ?>
<?= html_button('submit','','filter','btn');?>
<?= html_button('submit','','reset','btn',"onclick=resetForm('prodList')");?>
</form>

<?php foreach ($arr as $b): if($b->quantity<$b->threshold){?>
    <php ?>
    <p style="color: red;">Warning <?= $b->name ?> is running low on stock </p>
<?php }endforeach; ?>

<table class="table">
    <tr>
        <?= table_headers($fields, $sort, $dir,"page=$page&cat=$cat&name=$name") ?>
        <th>mod product</th>
    </tr>
    <?php foreach ($arr as $s): ?>
    <tr>
        <td><?= $s->product_id ?></td>
        <td><?= $s->name ?></td>
        <td><?= $s->price ?></td>
        <?php if($s->quantity<$s->threshold){?>
        <td style="color: red;"><?= $s->quantity ?> Running low on stock!</td>
        <?php }else{ ?>
        <td ><?= $s->quantity ?> </td>
        <?php } ?>
        <td><?= $s->threshold ?></td>
        <td><?= $s->description ?></td>
        <td><?= $s->status==1 ? 'ACTIVE' : 'INACTIVE' ?></td>
        <td><?= $_category[$s->category_id] ?></td>
        <td><img src=../../images/<?= $s->product_photo?> width="150px" height="150px"></td>
        <td><button  data-get="modifyProduct.php?id=<?=$s->product_id?>">Update</button></td>
    </tr>
    <?php endforeach ?>
</table>
<?php if(!$arr){?>
        <p>No record found</p>
        <?php }?>
<br>
<?= $p->html("sort=$sort&dir=$dir&cat=$cat&name=$name") ?>
</body>
<?php
include '../../_foot.php';