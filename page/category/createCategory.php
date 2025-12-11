<?php
require '../../_base.php';
// ----------------------------------------------------------------------------
auth("admin");
if (is_post()) {
    $name       = req('categoryName');
    if ($name == '') {
        $_err['name'] = 'Required';
    }
    else if(!is_unique($name,'category','name'))
    {
        $_err['name'] = 'Error category name already exist';
    }
    else if (strlen($name) > 50) {
        $_err['name'] = 'Maximum length 50';
    }

    $status=1;

    if (!$_err) {
        $stm = $_db->prepare('INSERT INTO category
                              ( name, status)
                              VALUES( ?, ?)');
        $stm->execute([$name, $status]);
        
        temp('info', 'Record inserted');
        redirect('categoryList.php');
    }
}

// ----------------------------------------------------------------------------
$_title = 'Insert Category';
include '../../_head.php';
?>
<head>
<link href="../../css/base.css" rel="stylesheet">
</head>
<body>
<h2>Create Category</h2>
<?=html_button('back','back','back','btn','data-get=../adminPageSelector.php') ?>
<form method="post" class="form" novalidate>
    <label for="categoryName">Category Name</label>
    <?= html_text('categoryName', 'maxlength="50" placeholder="Max 50 character"') ?>
    <?= err('name') ?>

    <section>
        <?= html_button('categoryButton','','Submit','btn');
         html_button('categoryButton','','Reset','btn','type=reset');?>
    </section>
</form>
</body>
<?php
include '../../_foot.php';