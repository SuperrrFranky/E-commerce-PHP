<head>
<link rel="stylesheet" href="../css/base.css">
<?php
include '../_base.php';

// ----------------------------------------------------------------------------
auth("admin");

$count=0;
$orderId = req('orderId');
$productId = req('productId');

$stm= $_db->prepare('SELECT i.*, p.name,p.product_photo From order_item as i, product as p Where i.product_id = p.product_id and i.order_id =?');
$stm->execute([$orderId]);
$arr = $stm->fetchAll();
if(!$arr)
{
    redirect("OrderList.php");
}
// ----------------------------------------------------------------------------

$_title = 'Order | Detail';
include '../_head.php';
?>
</head>
<p>
<?=html_button('back','back','back','backButton','data-get=orderList.php') ?>
</p>

<form class="form">
    <label>Order Id</label>
    <b><?= $o->order_id ?></b>
    <br>

    <label>Order Date</label>
    <div><?= $o->order_date ?></div>
    <br>

    <label>Status</label>
    <div><?= $o->order_status ?></div>
    <br>

    <label>Total</label>
    <div>RM <?= $o->total_amount ?></div>
    <br>
</form>

<p><?= count($arr) ?> item(s)</p>

<table class="table">
    <tr>
        <th>Product Id</th>
        <th>Product Name</th>
        <th>Price (RM)</th>
        <th>Unit</th>
        <th>Subtotal (RM)</th>
    </tr>

    <?php foreach ($arr as $i): ?>
    <tr>
        <td><?= $i->product_id ?></td>
        <td><?= $i->name ?></td>
        <td ><?= $i->price_per_unit ?></td>
        <td ><?= $i->quantity ?></td>
        <td >
            <?= $i->price_per_unit*$i->quantity ?>
            <img src="../images/<?= $i->product_photo ?>" width="100px" height="100px">
        </td>
        <?php $count+=$i->quantity?>
    </tr>
    <?php endforeach ?>

    <tr>
        <th colspan="3"></th>
        <th><?= $count ?></th>
        <th><?= $o->total_amount ?></th>
    </tr>
</table>

<?php
include '../_foot.php';