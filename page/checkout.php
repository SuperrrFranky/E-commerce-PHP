<?php
require '../_base.php';
$_title = 'Check Out';
include '../_head.php';
require __DIR__ . '/../vendor/autoload.php';

auth();

$orderId = get('order_id');

if ($orderId) {
    $stm = $_db->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stm->execute([$orderId, $_user->user_id]);
    $order = $stm->fetch();

    if (!$order) {
        temp('error', "Order not found.");
        redirect('/index.php');
    }

    $stm = $_db->prepare("SELECT oi.order_id,oi.product_id,oi.quantity AS cQuantity,oi.price_per_unit AS cPrice, p.name, p.product_photo FROM order_item oi 
                          JOIN product p ON oi.product_id = p.product_id 
                          WHERE oi.order_id = ?");
    $stm->execute([$orderId]);
    $orderItems = $stm->fetchAll();
    $checkOutItem = $orderItems;

    $subtotal = 0;
    foreach ($orderItems as $p) {
        $subtotal += $p->cPrice * $p->cQuantity;
    }

    $tax = $subtotal * 0.06;
    $deliveryFee = 10;
    $grandTotal = $subtotal + $tax + $deliveryFee;
    $earnedPoint = (int)$grandTotal;

    $stm = $_db->prepare("SELECT * FROM user WHERE user_id = ?");
    $stm->execute([$_user->user_id]);
    $user = $stm->fetch();

    $stm = $_db->prepare("SELECT * FROM address WHERE user_id = ?");
    $stm->execute([$_user->user_id]);
    $address = $stm->fetchAll();

    $GLOBALS['username'] = $user->username;
    $GLOBALS['phoneNumber'] = $user->phone;
    $GLOBALS['email'] = $user->email;
    $GLOBALS['point'] = $user->point;

    $usedPoint = 'false';
} else {
    $checkOutItem = getCartItems($_user->user_id, ['checkOut']);

    if (!$checkOutItem) {
        temp('info', "No Item Ready For Check Out");
        redirect("/index.php");
    }

    $stm = $_db->prepare("SELECT * FROM user WHERE user_id = ?");
    $stm->execute([$_user->user_id]);
    $user = $stm->fetch();

    $stm = $_db->prepare("SELECT * FROM `address` WHERE user_id = ?");
    $stm->execute([$_user->user_id]);
    $address = $stm->fetchAll();

    $GLOBALS['username'] = $user->username;
    $GLOBALS['phoneNumber'] = $user->phone;
    $GLOBALS['email'] = $user->email;
    $GLOBALS['point'] = $user->point;

    $subtotal = 0;
    foreach ($checkOutItem as $p) {
        $subtotal += $p->cPrice;
    }
    $tax = $subtotal * 0.06;
    $grandTotal = $subtotal + $tax + 10;
    $earnedPoint = (int)$grandTotal;
    $usedPoint = 'false';
}

if (is_post()) {
    if (isset($_POST['usedPoint'])) {
        $usedPoint = req('usedPoint');
        if ($usedPoint === 'true') {
            $redeemablePoints = floor($user->point / 100) * 100;
            $maxPointsBasedOnSubtotal = floor($subtotal / 10) * 100;
            $pointsToUse = min($redeemablePoints, $maxPointsBasedOnSubtotal);
            $discount = $pointsToUse / 100;
            $subtotal -= $discount;
            $tax = $subtotal * 0.06;
            $grandTotal = $subtotal + $tax + 10;
            $earnedPoint = (int)$grandTotal;
            $remainingPoints = $user->point - $pointsToUse;
            $GLOBALS['point'] = $remainingPoints;
        }
    }

    if (isset($_POST['payNow'])) {
        $selectedAddress = req('address');

        if ($selectedAddress === 'other') {
            temp('info', "please update your billing address");
            redirect('/page/address.php');
        }

        if ($orderId) {
            $stm = $_db->prepare("UPDATE orders SET total_amount = ?, used_point = ? WHERE order_id = ?");
            $stm->execute([$grandTotal, $pointsToUse, $orderId]);

            $GLOBALS['order_id'] = $orderId;
        } else {
            $stm = $_db->prepare("INSERT INTO orders (user_id, order_date, total_amount, payment_status, order_status, address_id, used_point) 
                                VALUES (?, CURDATE(), ?, ?, ?, ?, ?)");
            $stm->execute([$_user->user_id, $grandTotal, 'pending', 'pending', $selectedAddress, $pointsToUse]);

            $GLOBALS['order_id'] = $_db->lastInsertId();

            $stm = $_db->prepare("INSERT INTO order_item (order_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
            $productQuery = $_db->prepare("UPDATE product SET quantity = ? WHERE product_id = ?");
            $cartQuery = $_db->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");

            foreach ($checkOutItem as $p) {
                $stm->execute([$GLOBALS['order_id'], $p->product_id, $p->cQuantity, $p->price]);
                $quantity = $p->quantity - $p->cQuantity;
                $productQuery->execute([$quantity, $p->product_id]);
                $cartQuery->execute([$p->product_id, $_user->user_id]);
            }
        }



        $newPoint = $GLOBALS['point'] + $earnedPoint;

        $stripeAPIKey = getenv('STRIPE_API_KEY') ?: '';
        \Stripe\Stripe::setApiKey($stripeAPIKey);

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'success_url' => base('page/orderSuccess.php?session_id={CHECKOUT_SESSION_ID}&order_id=' . $GLOBALS['order_id'] . '&point=' . $newPoint),
            'cancel_url' => base('index.php?status=failed'),
            'mode' => 'payment',
            "line_items" => [
                [
                    "quantity" => 1,
                    "price_data" => [
                        "currency" => "myr",
                        "unit_amount" => $grandTotal * 100,
                        "product_data" => [
                            "name" => "payment"
                        ]
                    ]
                ]
            ]
        ]);

        http_response_code(303);
        header("Location:" . $checkout_session->url);
    }
}

?>

<style>
    .check-out-body {
        display: flex;
        justify-content: space-evenly;
        align-items: flex-start;
        margin: 0;
    }

    .billing-card,
    .product-card {
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        width: 45%;
        margin: 20px;
    }

    .billing-card {
        max-width: 500px;
        display: flex;
        flex-direction: column;
    }

    .billing-card label {
        font-size: 16px;
        color: #333;
        margin-bottom: 5px;
        display: inline-block;
    }

    .billing-card input,
    .billing-card select {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        width: 100%;
        box-sizing: border-box;
    }

    .billing-card form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .billing-card button {
        padding: 10px 20px;
        border: none;
        background-color: #4CAF50;
        color: #fff;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        align-self: center;
    }

    .billing-card button:hover {
        background-color: #45a049;
    }

    .product-card {
        max-width: 900px;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-size: 16px;
        margin-bottom: 8px;
        color: #333;
    }

    .check-out-body input,
    select {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        width: 80%;
    }

    input:focus,
    select:focus {
        border-color: #4CAF50;
        outline: none;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th,
    table td {
        padding: 8px;
        text-align: right;
        border-bottom: 1px solid #ccc;
    }

    table th {
        background-color: #f4f4f4;
    }

    table img {
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    tr td:nth-child(1):not(.total-row td) {
        text-align: center;
        width: 35%;
    }

    .total-row td {
        text-align: right;
    }

    #usedPoint {
        width: 25px;
        height: 25px;
    }
</style>

<body>
    <form method="post">
        <div class="check-out-body">
            <div class="billing-card">
                <h3>Please Confirm Your Billing Status</h3>
                <label for="username">Name:</label>
                <?php html_text("username", 'disabled') ?>

                <label for="phoneNumber">Phone Number:</label>
                <?php html_phone("phoneNumber", "disabled") ?>

                <label for="email">Email Address:</label>
                <?php html_email("email", "disabled") ?>

                <label for="address">Address:</label>
                <select name="address" id="address">
                    <?php
                    foreach ($address as $a) {
                        $selected = $a->is_default ? "selected" : "";
                        echo "<option value=\"$a->address_id\" $selected>$a->addressDetail</option>";
                    }
                    ?>
                    <option value="other">Other</option>
                </select>

                <button type="button" data-get="/page/profile.php">Click here to modify billing information</button>
            </div>
            <div class="product-card">
                <h3>Order Item (<?= sizeof($checkOutItem) ?>)</h3>
                <table>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price (RM)</th>
                    </tr>
                    <?php foreach ($checkOutItem as $p) : ?>
                        <tr>
                            <td>
                                <img src="/../images/<?= $p->product_photo ?>" alt="<?= $p->name ?>">
                            </td>
                            <td>
                                <p><?= $p->name ?></p>
                            </td>
                            <td>
                                <p><?= $p->cQuantity ?></p>
                            </td>
                            <td><?= $p->cPrice ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3">
                            Use Points for Discount (100 points = RM1) <br>
                            Point available: <?= $GLOBALS['point'] ?> pt
                        </td>
                        <td>
                            <input type="checkbox" name="usedPoint" id="usedPoint" value="true"
                                <?php if ($usedPoint == 'true') echo 'checked' ?>
                                onchange="this.form.submit()">
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3">Subtotal <?php if ($usedPoint == 'true') echo "(after discount RM $discount)" ?>:</td>
                        <td><?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3">Tax:</td>
                        <td><?= number_format($tax, 2) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3">Delivery Fee:</td>
                        <td>10.00</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3">Total:</td>
                        <td><?= number_format($grandTotal, 2) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4">Point Earned: <?= $earnedPoint ?> (pt)</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4">
                            <button type="submit" name="payNow" id="payNow">Proceed to Payment</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>


</body>

<script>
    $(document).ready(function() {
        $('#address').on('change', function() {
            const selectedValue = $(this).val();

            if (selectedValue === 'other') {
                window.location.href = '/page/address.php';
            }
        });
    });
</script>