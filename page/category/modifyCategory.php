<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php

require '../../_base.php';
$_title = 'Modify Category Page';
include '../../_head.php';
auth("admin");
$statusOptions = [
    1 => 'Active',
    0 => 'Inactive'
];
if(is_get())
{
    $id = req('id');

    $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
    $stm->execute([$id]);
    $s = $stm->fetch();

    if (!$s) {
        temp('info', 'Illegal modification');
        redirect('categoryList.php');
    }
    $GLOBALS['categoryName']= $s->name;
    $GLOBALS['categoryStatus'] = $s->status;
}

if(is_post())
{   
    $stm = $_db->prepare('UPDATE category
                            SET name = ?, status = ?
                            WHERE category_id = ?');

    $id = req('id');   
    $name = req('categoryName');     
    
    if ($name == '') {
        $_err['categoryName'] = 'Required';
    }
    else if (strlen($name) > 50) {
        $_err['categoryName'] = 'Maximum length 50';
    }
    else
    {
        $GLOBALS['categoryName']= $name;
    }
    $stat       = req('categoryStatus');
    $GLOBALS['categoryStatus'] = $stat;
    

    if (!$_err) {
            $stm->execute([$name, $stat, $id]);
           
        temp('info', 'Record updated');
        redirect('categoryList.php');
    }
}
?>
</head>
<body>
<h2>Modify Category</h2>
<?=html_button('back','modify','back','btn','data-get=categoryList.php')  ?>

<form method="post" novalidate>
<table class="table">
    <tr>
        <th>Category ID</th>
        <th>Name</th>
        <th>Status</th>
    </tr>

    <tr>
        <td><?= $s->category_id ?></td>
        <td><?= html_text('categoryName')?> <?= err('categoryName') ?></td>
        <td><?= html_select('categoryStatus', $statusOptions,null); ?></td>
    </tr>

</table>
<?= html_button('submit','submit','submit','btn','data-confirm')?>
</form>
<br>
</body>
<?php
include '../../_foot.php';