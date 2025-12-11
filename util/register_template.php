<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #dddddd;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Platform!</h1>
        </div>
        
        <div class="content">
            <h2>Hello <?php echo htmlspecialchars($cust_name); ?>!</h2>
            
            <p>Thank you for creating an account with us. We're excited to have you as part of our community!</p>
            
            <p>With your new account, you can:</p>
            <ul>
                <li>Access all our features</li>
                <li>Make purchases from our website</li>
                <li>Track your purchases and reviews</li>
                <li>Receive exclusive offers</li>
            </ul>
            
            <a href="<?php echo $url ?>" class="button">Get Started Now</a>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team. We're here to help!</p>
            
            <p>Best regards,<br>The Team</p>
        </div>
        
        <div class="footer">
            <p>© 2024 Toys Paradise. All rights reserved.</p>
            <p>You received this email because you signed up for an account.<br>
        </div>
    </div>
</body>
</html>