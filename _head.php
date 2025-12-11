<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>

    <link rel="stylesheet" href="/css/style.css">
    <!-- JQuery CDN-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Google Icon-->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <script src="/js/app.js"></script>
    <?php
    if (isset($extraScripts)) {
        if (is_array($extraScripts)) {
            foreach ($extraScripts as $script) {
                echo $script;
            }
        } else {
            echo $extraScripts;
        }
    }
    ?>
</head>

<body>

    <div id="info"><?= temp('info') ?></div>
    <?php if ((!$_user||$_user->role==='member') && $_SERVER['REQUEST_URI'] !== '/page/admin/admin_login.php') : ?>
        <nav class="navigation">

            <div class="home_icon" data-get="/index.php"></div>
            <div class="search_bar_container">
                <input type="text" name="searchInput" id="searchInput" placeholder="Search...">
                <span class="material-symbols-outlined search-icon" id="searchBtn">
                    search
                </span>
            </div>
            <div class="navContainer">
                <span class="material-symbols-outlined" data-get="/page/product/product.php">
                    store
                </span>
                <span class="material-symbols-outlined" data-get="/page/cart.php">
                    shopping_cart
                </span>
                <span class="material-symbols-outlined" data-get="/page/profile.php">
                    account_circle
                </span>
                <?php if ($_user) {
                    echo '<span class="material-symbols-outlined" data-get="/page/logout.php">
            logout
        </span>';
                }
                ?>
            </div>
        </nav>
    <?php elseif ($_SERVER['REQUEST_URI'] == '/page/admin/admin_login.php' || $_user->role === 'admin'): ?>
        <nav class="navigation">
            <div class="navContainer admin">
                <span data-get="/page/adminPageSelector.php">
                    admin page</span>
                <span class="material-symbols-outlined" data-get="/page/profile.php?is_admin=true">
                    account_circle
                </span>


                <?php if ($_user) {
                    echo '<span class="material-symbols-outlined" data-get="/page/logout.php?is_admin=true">
                 logout
                </span>';
                }?>
            </div>


        </nav>


    <?php endif; ?>