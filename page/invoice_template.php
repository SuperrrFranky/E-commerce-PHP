<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $order_id ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .invoice {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
        }

        .invoice-header {
            text-align: center;
        }

        .invoice-header h1 {
            margin: 0;
        }

        .invoice-details,
        .invoice-items {
            width: 100%;
            margin-top: 20px;
        }

        .invoice-details th,
        .invoice-items th {
            text-align: left;
            background-color: #f4f4f4;
            padding: 10px;
        }

        .invoice-details td,
        .invoice-items td {
            padding: 10px;
        }

        .total-row {
            font-weight: bold;
        }

        img.product-photo {
            width: 50px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="invoice-header">
            <h1>Invoice</h1>
            <p>Order ID: <strong>#<?= $order_id ?></strong></p>
        </div>

        <table class="invoice-details">
            <tr>
                <th>Order Date</th>
                <td><?= $first_item->order_date ?></td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>RM <?= number_format($first_item->total_amount, 2) ?></td>
            </tr>
            <tr>
                <th>Shipping Address</th>
                <td><?= htmlspecialchars($first_item->addressDetail) ?></td>
            </tr>
        </table>

        <h2>Items</h2>
        <table class="invoice-items" border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price per Unit (RM)</th>
                    <th>Subtotal (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordered_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->name) ?></td>
                        <td><?= $item->quantity ?></td>
                        <td><?= number_format($item->price_per_unit, 2) ?></td>
                        <td><?= number_format($item->quantity * $item->price_per_unit, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Subtotal</td>
                    <td>RM <?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Discount</td>
                    <td>(RM <?= $discount ?>)</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Tax</td>
                    <td>RM <?= number_format(($subtotal-$discount) * $tax_rate, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Delivery Fee</td>
                    <td>RM <?= number_format($delivery_fee, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Grand Total</td>
                    <td>RM <?= number_format($first_item->total_amount,2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
