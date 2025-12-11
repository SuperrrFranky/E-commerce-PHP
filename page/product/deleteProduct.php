<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php
require '../../_base.php';
$_title = 'Delete Product Page';
include '../../_head.php';
auth("admin");
$_category=$_db->query('SELECT category_id,name FROM category')->fetchAll(PDO::FETCH_KEY_PAIR);
$_product=$_db->query('SELECT * FROM product')->fetchAll();

if(is_post())
{   
    $stm = $_db->prepare('UPDATE product
                        Set status = ?
                        WHERE product_id = ?');
        $id       = req('productTick',[]);
        if(!$id){
            $_err['productTick'] = 'Check at least one box to delete';
        }
        
        if (!$_err) {

            foreach($id as $i)
            {
                $stm->execute([FALSE, $i]);
            }
            
            temp('info', 'Record made inactive');
            redirect('productList.php');
        }
}
?>
</head>
<body>
<h2>Delete Product</h2>
<?=html_button('back','modify','back','btn','data-get=productList.php')  ?>

<form method="post">
<table class="table">
    <tr>
        <th></th>
        <th>Product ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Category</th>
        <th>Status</th>
    </tr>

    <?php foreach ($_product as $s): ?>
    <?php if($s->status!=0){ ?>
    <tr>
        <td><?= html_check('productTick[]','check',$s->product_id)?></td>
        <td><?= $s->product_id ?></td>
        <td><?= $s->name?> </td>
        <td><?= $s->description?> </td>
        <td><?= $_category[$s->category_id] ?></td>
        <td><?= $s->status==1 ? 'ACTIVE' : 'INACTIVE' ?></td>
    </tr>
    <?php }?>
    <?php endforeach ?>
</table>
<?= html_button('submit','submit','delete','btn','data-confirm')?>
<?=err('productTick') ?>
</form>
<br>

</body>
<?php
include '../../_foot.php';