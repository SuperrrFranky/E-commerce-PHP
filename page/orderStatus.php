<?php
require '../_base.php';
if(is_post())
{   
    $stat = req('order',[]);
    if(!$stat)
    {
        temp('info', 'No order selected');
        redirect('orderList.php');
    }
    $stm = $_db->prepare('UPDATE orders
                              SET order_status = ?
                              WHERE order_id = ?');

    foreach($stat as $o)
    {
        
        $stm->execute(['ontheroad', $o]);
    }
    temp('info', 'status updated');
    redirect('orderList.php');
}

temp('info', 'illegal access');
redirect('/');
?>

</head>
