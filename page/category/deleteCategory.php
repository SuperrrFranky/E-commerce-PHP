<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php

require '../../_base.php';
$_title = 'Delete Category';
include '../../_head.php';
auth("admin");

$_category=$_db->query('SELECT * FROM category')->fetchAll();

if(is_post())
{   
    $stm = $_db->prepare('UPDATE category
    Set status = ?
    WHERE category_id = ?');

        $id       = req('categoryTick',[]);
        if(!$id){
            $_err['categoryTick'] = 'Check at least one box to delete';
        }
        
        if (!$_err) {

            foreach($id as $i)
            {
                $stm->execute([FALSE, $i]);
            }
            
            temp('info', 'Record made inactive');
            redirect('categoryList.php');
        }
    
}
?>
</head>
<body>
<h2>Delete Category </h2>
<?=html_button('back','modify','back','btn','data-get=categoryList.php')  ?>

<form method="post">
<table class="table">
    <tr>
        <th></th>
        <th>Category ID</th>
        <th>Name</th>
        <th>Status</th>
    </tr>

    <?php foreach ($_category as $s): ?>
    <?php if($s->status!=0){ ?>
    <tr>
        <td><?= html_check('categoryTick[]','check',$s->category_id)?></td>
        <td><?= $s->category_id ?></td>
        <td><?= $s->name?> </td>
        <td><?= $s->status==1 ? 'ACTIVE' : 'INACTIVE' ?></td>
    </tr>
    <?php }?>
    <?php endforeach ?>
</table>
<?= html_button('submit','submit','delete','btn','data-confirm') ?>
<?=err('categoryTick') ?>
</form>
<br>
</body>
<?php
include '../../_foot.php';