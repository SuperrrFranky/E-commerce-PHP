<?php
require '../_base.php';
$_title = 'Order Success';
include '../_head.php';

$stripeAPIKey = getenv('STRIPE_API_KEY') ?: '';
\Stripe\Stripe::setApiKey($stripeAPIKey);

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    header("Location: /index.php");
    exit;
}

$checkout_session = \Stripe\Checkout\Session::retrieve($session_id);

if ($checkout_session->payment_status !== 'paid') {
    header("Location: /index.php");
    exit;
}
$payment_intent_id = $checkout_session->payment_intent;

$payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

$payment_reference = $payment_intent->id;

$order_id = get('order_id');
$newPoint = get('point');
$stm = $_db->prepare("UPDATE `orders` SET `payment_status` = 'payed',payment_reference = ? WHERE `orders`.`order_id` = ?");
$stm->execute([$payment_reference,$order_id]);

$stm = $_db->prepare("UPDATE user SET point = ? WHERE user_id =?");
$stm->execute([$newPoint, $_user->user_id]);

$filePath = generateInvoicePDF($order_id);

$m = get_mail();
$m->addAddress($_user->email);
$m->Subject = "Order #$order_id";
$m->Body = "Thank you for your order!";
$m->addAttachment($filePath);
$m->send();

?>
<script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
<style>

    .card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        width: 400px;
        text-align: center;
        padding: 20px;
        margin: 10% auto;
    }

    .card h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 10px;
    }

    .card p {
        font-size: 16px;
        color: #555;
        margin-bottom: 20px;
    }

    .card button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .card button:hover {
        background-color: #0056b3;
    }

    dotlottie-player {
        margin-bottom: 20px;
    }
</style>

<body>
    <div class="card">
        <dotlottie-player src="https://lottie.host/9fdaab4f-7f97-4c23-b66d-faaad52b0011/2kHOb1SxKx.lottie" 
                          background="transparent" 
                          speed="1" 
                          style="width: 200px; height: 200px; margin :0 auto;" 
                          loop autoplay></dotlottie-player>
        <h1>Order Success</h1>
        <p>Payment successful. Thank you for your order!</p>
        <button onclick="window.location.href='/index.php'">Back To Home Page</button>
    </div>
</body>
