<?php
require '../_base.php';
$_title = 'My Review';
include '../_head.php';

auth();

$user_id = $_user->user_id; // Retrieve the current user ID

// Fetch completed review items
$query = "  SELECT DISTINCT r.review_id, r.product_id, p.name, p.description, p.price, p.product_photo, oi.quantity, r.rating, r.description AS review_description, r.anonymous, 
            GROUP_CONCAT(rm.review_media_path) AS review_media_paths
            FROM review r
            LEFT JOIN review_media rm ON r.review_id = rm.review_id
            JOIN order_item oi ON r.order_id = oi.order_id AND r.product_id = oi.product_id
            JOIN product p ON oi.product_id = p.product_id
            WHERE r.user_id = ? AND r.status = 'completed'
            GROUP BY r.review_id, r.product_id
            ORDER BY r.review_id DESC ";

$stmt = $_db->prepare($query);
$stmt->execute([$user_id]);
$completedReviews = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<link rel="stylesheet" href="/css/profile.css">
<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li class="selected"><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>Review</p>
    </div>
    <div class="profile-details">
        <div class="container_tabs">
            <a href="/page/pendingReview.php" style="text-decoration:none">
                <div class="tab">Pending Review</div>
            </a>
            <a>
                <div class="tab active">Reviewed</div>
            </a>
        </div>
        <?php if (!$completedReviews): ?>
            <hr>
            <p>No products reviewed.</p>
        <?php else: ?>
            <?php foreach ($completedReviews as $review):
                $mediaPaths = $review->review_media_paths ? explode(',', $review->review_media_paths) : [];
            ?>
                <div class="reviewed-container">
                    <a href="product/productdetail.php?product_id=<?= $review->product_id ?>" style="text-decoration:none;">
                        <div class='product-reviewed'>
                            <div>
                                <img src='../../images/<?= $review->product_photo ?>' alt='<?= $review->name ?>'>
                            </div>
                            <div>
                                <h3><?= htmlspecialchars($review->name) ?></h3>
                                <p class="product-price">Price: RM <?= $review->price ?></p>
                                <p>✖<?= $review->quantity ?></p>
                            </div>
                        </div>
                    </a>
                    <hr>
                    <div class="review-content">
                        <p>Reviewed by: <?= $_user->username ?></p>
                        <div class="rating-star">
                            <h3>Product Quality: </h3>
                            <div>
                                <?php for ($x = 0; $x < $review->rating; $x++): ?>
                                    <img src="../images/full_star_yellow.png" style="width: 50px;" class="star">
                                <?php endfor; ?>
                                <?php for ($x = $review->rating; $x < 5; $x++): ?>
                                    <img src="../images/empty_star.png" style="width: 50px;" class="star">
                                <?php endfor; ?>
                            </div>
                        </div>

                        <?php if (!empty($review->review_description)): ?>
                            <div class="review-description">
                                <h3>Review:</h3>
                                <p><?= htmlspecialchars($review->review_description) ?></p>
                            </div>
                        <?php endif; ?>

                        <div>
                            <h3>Review Media:</h3>
                            <?php if ($mediaPaths): ?>
                                <div class="media-grid">
                                    <?php foreach ($mediaPaths as $path): ?>
                                        <img
                                            src="../../images/<?= $path ?>"
                                            alt="Review Photo"
                                            class="review-photo"
                                            style="max-width: 100px; cursor: pointer;"
                                            data-largepath="../../images/<?= $path ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No photos attached.</p>
                            <?php endif; ?>
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
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../_foot.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mediaPhotos = document.querySelectorAll('.review-photo');
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxClose = document.getElementById('lightbox-close');
        const lightboxPrev = document.getElementById('lightbox-prev');
        const lightboxNext = document.getElementById('lightbox-next');

        let currentIndex = 0;
        const mediaPaths = Array.from(mediaPhotos).map(img => img.getAttribute('data-largepath'));

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