<?php
require '_base.php';
include '_head.php';

if (isset($_GET['status']) && $_GET['status'] == 'failed') {
    echo "<script>alert(\"Payment failed. Your ordered items will be kept for 1 day. If payment is not completed within 24 hours, your order will be canceled.\")</script>";
}

$categories = $_db->query('SELECT category_id, name FROM category WHERE status = 1')->fetchAll();


$_title = 'Product Page';
?>
<style>
    .home_page_container {
        padding: 20px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .home_page_text {
        margin-bottom: 30px;
    }

    .home_page_video {
        width: 100%;
        height: 300px;
        margin-bottom: 20px;
    }

    .homepage button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        margin: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .homepage button:hover {
        background-color: #45a049;
    }

    .home_page_category {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        margin-top: 40px;
    }

    .category_container {
        width: 250px;
        margin-bottom: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .category_container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .category_container button {
        width: 100%;
        background-color: #007BFF;
    }

    .category_container button:hover {
        background-color: #0056b3;
    }

    video {
        width: 55%;

    }
</style>

<body>
    <div class="homepage">

        <div class="home_page_container">
            <div class="home_page_text">
                <h1>WELCOME TO OUR TOY STORE</h1>
                <p>Discover the perfect toys for kids of all ages! From educational games to action figures, we have a wide variety of toys that will spark imagination and fun. Shop now for the best deals on the latest toys!</p>
            </div>
            <video src="/video/SampleVideo_1280x720_1mb.mp4" autoplay></video>

                <button data-get="/page/product/product.php"> View Now</button>
        </div>

        <div class="home_page_category">
            <h3>Category</h3>
            <?php foreach ($categories as $category): ?>
                <div class="category_container">
                    <?php
                    html_button('categoryButton', $category->category_id, $category->name, 'filter');
                    ?>
                </div>

            <?php endforeach; ?>
        </div>
    </div>

    <?php
    include '_foot.php';
    ?>

</body>

<script>
    $(document).on("click", "#categoryButton", function(e) {
        e.preventDefault();
        const categoryId = $(this).val();


        const url = `/page/product/product.php?category=${encodeURIComponent(categoryId)}`;
        window.location.href = url;
    });
</script>