<head>
<?php
require '../_base.php';
$_title = 'Product detail Page';
include '../_head.php';
auth("admin");
$stm = $_db->query('SELECT * FROM product');
$products = $stm->fetchAll();

$alertMessage= "Below stock is running low of stock\n";
$count=0;
$appeared = $_SESSION["alertAppeared"]??false;
if(!$appeared){
foreach($products as $p)
{
    if($p->quantity <= $p->threshold)
    {
        $alertMessage .= $p->name."\n";
        $count +=1;
    }
}
$alertMessage .= "Total product running low of stock : ".$count;
$_SESSION["alertAppeared"]=true;
}
?>

<link href="../css/admin.css" rel="stylesheet">
<script>
    var count = <?php echo $count; ?>;
    var alertMessage = <?php echo json_encode($alertMessage); ?>;
    if (count > 1) {
        alert(alertMessage);
    }
</script>

</head>
<body class="dashboard">
<h1>Admin dashboard</h1>
<div class="adminLinksContainer">
<a href="category/categoryList.php" class="adminLink">Category listing</a>
<a href="product/productList.php" class="adminLink">Product listing</a>
<a href="orderList.php" class="adminLink">Order listing</a>
<a href="admin/viewMembers.php" class="adminLink">View Member</a>
</div>
</body>
<?php
include '../_foot.php';