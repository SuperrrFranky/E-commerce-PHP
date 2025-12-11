<head>
<link rel="stylesheet" href="../css/base.css">
<?php
include '../_base.php';
auth("admin");
$statusOptions = [
    'pending' => 'Pending',
    'ontheroad' => 'On the road',
    'completed'  => 'Completed',
    'canceled' => 'Cancelled'
];

// ----------------------------------------------------------------------------
//auth('Member');

$name = req('name');
$status = req('orderStatus');
$stm = $_db->prepare('SELECT * FROM `orders` where user_id in (Select user_id from user where username like ?) and (order_status=? or ?)'); 
$stm->execute(["%$name%",$status,$status==null]);
$arr = $stm->fetchAll();
$getUser = $_db->prepare('SELECT * FROM `user` where user_id = ?'); 
$getAddress = $_db->prepare('SELECT * FROM `address` where user_id = ?'); 
// ----------------------------------------------------------------------------

$_title = 'Order | List';
include '../_head.php';
?>
</head>
<p>
<?=html_button('back','back','back','btn','data-get=adminPageSelector.php') ?>
<?=html_button('modify','','set status to on the road','btn','form="status"') ?>
</p>

<form id="orderList">
<?= html_search('name','placeholder="Search"') ?>
<?= html_select('orderStatus', $statusOptions,'all'); ?>
<?= html_button('submit','submit','filter','btn');?>
<?= html_button('submit','','reset','btn',"onclick=resetForm('orderList')");?>
</form>

<p><?= count($arr) ?> record(s)</p>

<form method="post" action="orderstatus.php" id="status">
<table class="table">
    <tr>
        <th></th>
        <th>Id</th>
        <th>User detail</th>
        <th>Order Date</th>
        <th>Order Address</th>
        <th>Order Status</th>
        <th>Total (RM)</th>
        <th></th>
    </tr>
    <?php foreach ($arr as $o): ?>
    <?php if($o->payment_status=='payed'||$o->order_status='canceled'){ ?>
    <tr>
        <?php if($o->order_status !='pending'){ ?>
        <td><?=html_check('order[]','check',$o->order_id,'disabled') ?></td>
        <?php } else{ ?>
            <td><?=html_check('order[]','check',$o->order_id) ?></td>
        <?php }?>
        <td><?= $o->order_id ?></td>
        <?php 
        $getUser->execute([$o->user_id]);
        $p= $getUser->fetch();
        $getAddress->execute([$o->user_id]);
        $s= $getAddress->fetch();
        ?>
        <td>Username:<?= $p->username ?> <br>Email:<?= $p->email ?> <br>PhoneNumber:<?= $p->phone ?></td>
        <td><?= $o->order_date ?></td>
        <td ><?= $s->addressDetail ?></td>
        <td ><?= $o->order_status ?></td>
        <td ><?= $o->total_amount ?></td>
        <td>
        <button class="btn" data-get="orderDetail.php?orderId=<?= $o->order_id ?>&productId=<?=$o->user_id ?>">Detail</button>
        </td>
    </tr>
    <?php }?>
    <?php endforeach ?>
</table>
<?php if(!$arr) echo "no order found"?>
</form>
<?php
include '../_foot.php';