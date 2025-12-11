<head>
    <link rel='stylesheet' href="../../css/base.css">
    <link rel='stylesheet' href="../../css/product.css">
    <?php
    require '../../_base.php';

    $_title = 'Product detail Page';
    include '../../_head.php';
    if( $_user){
    $user_id = $_user->user_id;
    }
    $id = req('product_id');

    $stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
    $stm->execute([$id]);
    $s = $stm->fetch();
    if (!$s) {
        temp('info', 'illegal product access');
        redirect('product.php');
    }

    // Fetch reviews for the product
    $reviewQuery = "    SELECT r.review_id, r.product_id, r.rating, r.description AS review_description, r.anonymous, u.username, 
                        COALESCE(u.profile_photo, 'photo.jpg') AS profile_photo, GROUP_CONCAT(rm.review_media_path) AS review_media_paths 
                        FROM review r
                        LEFT JOIN review_media rm ON r.review_id = rm.review_id
                        JOIN user u ON r.user_id = u.user_id
                        WHERE r.product_id = ? 
                        AND r.status = 'completed'
                        GROUP BY r.review_id
                        ORDER BY r.review_id DESC";

    $reviewStmt = $_db->prepare($reviewQuery);
    $reviewStmt->execute([$id]);
    $reviews = $reviewStmt->fetchAll(PDO::FETCH_OBJ);

    if (is_post()) {
        auth("member");
        $action  = req('action');
        if ($action == 'cart') {
            $qty = req('quantity');

            if ((int)$qty < 1) {
                temp('info', 'Quantity must more then 0');
                redirect('', $delay = 0.1);
            }
            addToCart($_user->user_id, $s->product_id, $qty);
        } else if ($action == 'wishlist') {
            addToWishList($_user->user_id, $s->product_id);
        }
        redirect('', $delay = 0.1);
    }
    ?>
</head>

<body class="productDetailBody">
    <?= html_button('back', 'back', 'back', 'btn', 'data-get=product.php') ?>
    <div class="productDetailContainer">
        <img name='productPhoto' id='productPhoto' src=../../images/<?= $s->product_photo ?>>
        <h2 class="productHeader">Name </h2>
        <p id='productName' name='productName' class='productDetail'><?= $s->name ?></p>
        <br>
        <h2 class="productHeader">Description</h2>
        <p id='productDesc' name='productDesc' class='productDetail'><?= $s->description ?></p>
        <br>
        <p id='productQuan' name='productQuan' class='productDetail'>Quantity Left:<?= $s->quantity ?></p>
        <section id=productButton>
            <?php
            $GLOBALS['quantity'] = 1;
            html_number('quantity', 1, $s->quantity, '1', 'class=range');
            ?>
            <br>
            <button class="actionBtn" data-action='cart'>Add to cart</button>
            <button class="actionBtn" data-action="wishlist">Wishlist</button>
        </section>
    </div>

    <div class="productDetailContainer">
        <h1>Reviews</h1>
        <?php if (!$reviews): ?>
            <p>No reviews yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="reviewContainer">
                    <div class="reviewHeader">
                        <?php if (!$review->anonymous): ?>
                            <?php $review->profile_photo = $review->profile_photo ? '../../images/' . $review->profile_photo : '../../images/photo.jpg'; ?>
                            <img src="<?= $review->profile_photo ?>" alt="<?= htmlspecialchars($review->username) ?>" class="userProfilePhoto">
                            <div class="userDetails">
                                <p class="username"><?= htmlspecialchars($review->username) ?></p>
                            </div>
                        <?php else: ?>
                            <img src="../../images/photo.jpg" alt="Anonymous User" class="userProfilePhoto">
                            <div class="userDetails">
                                <p class="username">Anonymous</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="reviewContent">
                        <div class="reviewRating">
                            <h3>Rating:</h3>
                            <div class="ratingStars">
                                <?php for ($x = 0; $x < $review->rating; $x++): ?>
                                    <img src="../../images/full_star_yellow.png" alt="Star" class="star">
                                <?php endfor; ?>
                                <?php for ($x = $review->rating; $x < 5; $x++): ?>
                                    <img src="../../images/empty_star.png" alt="Empty Star" class="star">
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if (!empty($review->review_description)): ?>
                            <div class="reviewDescription">
                                <h3>Review:</h3>
                                <p><?= htmlspecialchars($review->review_description) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($review->review_media_paths)): ?>
                            <div class="reviewMedia">
                                <h3>Review Media:</h3>
                                <div class="mediaGrid">
                                    <?php
                                    $mediaPaths = explode(',', $review->review_media_paths);
                                    foreach ($mediaPaths as $index => $path):
                                    ?>
                                        <img
                                            src="../../images/<?= htmlspecialchars($path) ?>"
                                            alt="Review Media"
                                            class="reviewMediaPhoto"
                                            data-index="<?= $index ?>"
                                            data-fullpath="../../images/<?= htmlspecialchars($path) ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Lightbox Modal -->
                            <div id="lightbox" class="lightbox hidden">
                                <div class="lightbox-content">
                                    <span id="lightbox-close">&times;</span>
                                    <img id="lightbox-image" src="" alt="Full View">
                                    <div class="lightbox-nav">
                                        <button id="lightbox-prev">&lt;</button>
                                        <button id="lightbox-next">&gt;</button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="noMedia">No media attached for this review.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mediaPhotos = document.querySelectorAll('.reviewMediaPhoto');
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxClose = document.getElementById('lightbox-close');
        const lightboxPrev = document.getElementById('lightbox-prev');
        const lightboxNext = document.getElementById('lightbox-next');

        let currentIndex = 0;
        const mediaPaths = Array.from(mediaPhotos).map(img => img.getAttribute('data-fullpath'));

        // Open Lightbox
        mediaPhotos.forEach((photo, index) => {
            photo.addEventListener('click', function() {
                currentIndex = index;
                updateLightbox();
                lightbox.classList.remove('hidden');
            });
        });

        // Update Lightbox Image
        function updateLightbox() {
            lightboxImage.src = mediaPaths[currentIndex];
        }

        // Close Lightbox
        lightboxClose.addEventListener('click', function() {
            lightbox.classList.add('hidden');
        });

        // Navigate to Previous Image
        lightboxPrev.addEventListener('click', function() {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : mediaPaths.length - 1;
            updateLightbox();
        });

        // Navigate to Next Image
        lightboxNext.addEventListener('click', function() {
            currentIndex = (currentIndex < mediaPaths.length - 1) ? currentIndex + 1 : 0;
            updateLightbox();
        });

        // Close Lightbox on Outside Click
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                lightbox.classList.add('hidden');
            }
        });
    });
</script>

<?php
include '../../_foot.php';
