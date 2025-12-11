<?php
require '../_base.php';

auth();

$selectedId = get('productId');
$newQty = get('quantity');
$newStatus = get('status');
$is_removed = get('remove');


if ($is_removed) {
    $stm = $_db->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");
    $stm->execute([$selectedId, $_user->user_id]);
}


if ($selectedId != null && $newQty != null) {
    $stm = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
    $stm->execute([$selectedId]);
    $product = $stm->fetch();

    if ($product) {
        if ($newQty > $product->quantity) {
            temp('info', 'You have reached the maximum quantity allowed for this product.');
            redirect("", 0.1);
        } else {
            addToCart($_user->user_id, $selectedId, $newQty, $newStatus);
            temp('info');
        }
    } else {
        temp('info', 'Product not found.');
        redirect("", 0.1);
    }
}


$product = getCartItems($_user->user_id,['pending', 'checkOut']);

$stm = $_db->prepare("
    SELECT 
        COALESCE(SUM(price), 0) AS total_price,
        COUNT(DISTINCT product_id) AS total_products
    FROM cart
    WHERE status = 'checkOut' AND user_id = ?
");
$stm->execute([$_user->user_id]);
$checkOutItem = $stm->fetch();


echo "<div class=\"cart-header\">
    <h1>Cart (" . sizeof($product) . ")</h1>
</div>";

if ($product) {
    foreach ($product as $p) {

        echo "<div class=\"cart-product-container\" >";
        echo "<div class=\"delete-btn-container\">
        <span class=\"material-symbols-outlined removeCartBtn\" data-selected=\"" . $p->product_id . "\">delete</span>
    </div>";
        echo "<img src=\"/../images/" . $p->product_photo . "\" alt=\"" . $p->name . "\" class=\"cart-photo\">";

        echo "<div class=\"cart-detail\">
        <h2>" . $p->name . "</h2>
        <p>
            Price: (RM " . number_format($p->price, 2) . " X " . $p->cQuantity . ") = RM" . number_format((float)$p->cPrice, 2) . "
        </p>
    </div>";


        echo "<div class=\"cart-input\">
        <span class=\"material-symbols-outlined cart-addBtn\" data-selected=\"" . $p->product_id . "\">add_circle</span>";
        echo "<input type=\"number\"  id=\"$p->product_id-quantity\" name=\"$p->product_id-quantity\" data-selected=\"" . $p->product_id . "\" max=$p->quantity value = $p->cQuantity>";
        echo "<span class=\"material-symbols-outlined cart-minBtn\" data-selected=\"" . $p->product_id . "\">do_not_disturb_on</span>
    </div>";

        if ($p->cQuantity > $p->quantity) {
            echo "<div class=\"outOfStock\">";
            echo "<h3> Out Of Stock </h3>
                <p> available : $p->quantity </P>";
            echo "</div>";
        } else {
            echo "<div class=\"cart-check\">";
            if ($p->status == 'checkOut') {
                $atr = 'checked data-selected="' . $p->product_id . '"';
            } else {
                $atr = 'data-selected="' . $p->product_id . '"';
            }
            echo html_check($p->product_id . '-check', 'cart-check', $p->product_id, $atr);
            echo "</div>";
        }

        echo "</div>";

    }
    echo "<div class=\"cart-footer\">";
    echo "<h2 class = \"cart-footer-detail\"> Total Amount : RM " . number_format($checkOutItem->total_price, 2) . " </h2>";
    echo "<span class = \"cart-footer-button \" data-get=\"/page/checkout.php\"> Check Out ($checkOutItem->total_products) </span>
    </div>";
} else {
    echo "<h2>Your Cart is Empty</h2>";
}
