<head>
    <link rel="stylesheet" href="../../css/base.css">
    <?php

require '../../_base.php';
$_title = 'Category List Page';
include '../../_head.php';
auth("admin");
$fields = [
    'category_id' => 'category_id',
    'name'       => 'Name',
    'status'     => 'Status',
];
$name = req('name');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'category_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);
require_once '../../lib/SimplePager.php';
$p = new SimplePager("SELECT * FROM category where name like ?  ORDER BY $sort $dir", ['%'.$name.'%'], 10, $page);
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
<?=html_button('create','create','create','btn','data-get=createCategory.php') ?>
<?=html_button('del','delete','delete','btn','data-get=deleteCategory.php') ?>
</section>
<br>

<form id="catList">
<?= html_search('name','placeholder="Search"') ?>
<?= html_button('submit','','filter','btn');?>
<?= html_button('submit','','reset','btn',"onclick=resetForm('catList')");?>
</form>

<table class="table">
    <tr>
        <?= table_headers($fields, $sort, $dir,"page=$page&name=$name") ?>
        <th>Mod product</th>
    </tr>
    <?php foreach ($arr as $s): ?>
    <tr>
        <td><?= $s->category_id ?></td>
        <td><?= $s->name ?></td>
        <td><?= $s->status==1 ? 'ACTIVE' : 'INACTIVE' ?></td>
        <td><button  data-get="modifyCategory.php?id=<?=$s->category_id?>">Update</button></td>
    </tr>
    <?php endforeach ?>
</table>
<?php if(!$arr){?>
        <p>No record found</p>
        <?php }?>
<br>
<?= $p->html("sort=$sort&dir=$dir&name=$name") ?>
</body>
<?php
include '../../_foot.php';