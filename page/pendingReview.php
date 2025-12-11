<?php
require '../_base.php';
$_title = 'My Review';
include '../_head.php';

auth();

$user_id = $_user->user_id; // Retrieve the current user ID

$updateQuery = "    INSERT INTO review (rating, description, status, product_id, user_id, order_id)
                    SELECT 5, NULL, 'pending', oi.product_id, o.user_id, oi.order_id
                    FROM orders o
                    JOIN order_item oi ON o.order_id = oi.order_id
                    LEFT JOIN review r ON oi.product_id = r.product_id 
                        AND r.user_id = o.user_id 
                        AND r.order_id = o.order_id
                    WHERE o.user_id = ? AND o.order_status = 'completed' AND r.product_id IS NULL ";

$updateStmt = $_db->prepare($updateQuery);
$updateStmt->execute([$user_id]);

// Fetch pending review items
$query = "  SELECT r.product_id, p.name, p.description, p.price, p.product_photo, oi.quantity
            FROM review r
            JOIN order_item oi ON r.order_id = oi.order_id AND r.product_id = oi.product_id
            JOIN product p ON oi.product_id = p.product_id
            WHERE r.user_id = ? 
            AND r.status = 'pending'";

$stmt = $_db->prepare($query);
$stmt->execute([$user_id]);
$pendingReviews = $stmt->fetchAll();

// Add review logic (if form submitted)
if (is_post()) {
    $product_id = req('product_id');
    $rating = req('rating');
    $description = req('description');
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $photos = get_files('photo');

    $updateReviewQuery = "UPDATE review SET rating = ?, description = ?, status = 'completed', anonymous = ? WHERE product_id = ? AND user_id = ?";
    $updateReviewStmt = $_db->prepare($updateReviewQuery);
    $updateReviewStmt->execute([$rating, $description, $anonymous, $product_id, $user_id]);

    // Retrieve the corresponding review_id
    $fetchReviewIdQuery = "SELECT review_id FROM review WHERE product_id = ? AND user_id = ?";
    $fetchReviewIdStmt = $_db->prepare($fetchReviewIdQuery);
    $fetchReviewIdStmt->execute([$product_id, $user_id]);
    $review_id = $fetchReviewIdStmt->fetchColumn();

    if ($photos) {
        foreach ($photos as $file) {
            if ($file->error === UPLOAD_ERR_OK) {
                $photo = save_photo($file, '../images');
                // Insert path into review_media table
                $insertMediaQuery = "INSERT INTO review_media (review_id, product_id, review_media_path) VALUES (?, ?, ?)";
                $insertMediaStmt = $_db->prepare($insertMediaQuery);
                $insertMediaStmt->execute([$review_id, $product_id, $photo]);

                // Retrieve the last inserted review_media_id
                $review_media_id = $_db->lastInsertId();

                // Update review table to associate review_media_id
                $updateReviewQuery = "UPDATE review SET review_media_id = ? WHERE review_id = ?";
                $updateReviewStmt = $_db->prepare($updateReviewQuery);
                $updateReviewStmt->execute([$review_media_id, $review_id]);
            }
        }
    }

    if ($updateReviewStmt->rowCount() > 0) {
        temp('info', "Review added successfully!");
        redirect('pendingReview.php');
    } else {
        temp('info', "Failed to add review.");
        redirect('pendingReview.php');
    }
}
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
            <a>
                <div class="tab active">Pending Review</div>
            </a>
            <a href="reviewed.php" style="text-decoration:none">
                <div class="tab">Reviewed</div>
            </a>
        </div>
        <?php if (empty($pendingReviews)): ?>
            <hr>
            <p>No products have to review.</p>
        <?php else: ?>
            <?php foreach ($pendingReviews as $item): ?>
                <div class="review-container">
                    <a href="product/productdetail.php?product_id=<?= $item->product_id ?>" style="text-decoration:none;">
                        <div class='product-review'>
                            <div>
                                <img src='../../images/<?= $item->product_photo ?>' alt='<?= $item->name ?>'>
                            </div>
                            <div>
                                <h3><?= $item->name ?></h3>
                                <p class="product-price">Price: RM <?= $item->price ?></p>
                                <p>✖<?= $item->quantity ?></pF>
                            </div>
                        </div>
                    </a>
                    <div>
                        <button data-product-id='<?= $item->product_id ?>' onclick="openEditPopup()" class="add-review-button">Add Review</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="edit-review-popup" class="popup-overlay">
    <div class="popup-content-review">
        <div class="close-popup-header">
            <h2>Add Review</h2>
            <p><span type="reset" onclick="closePopup()">✖</span></p>
        </div>
        <div class="review-container">
            <div class='product-review'>

                <div>
                    <img src='../../images/<?= $item->product_photo ?>' alt='<?= $item->name ?>'>
                </div>
                <div>
                    <h3><?= $item->name ?></h3>
                    <p class="product-price">Price: RM <?= $item->price ?></p>
                </div>
            </div>

        </div>
        <form method="post" enctype="multipart/form-data" class="review-form" novalidate>
            <input type="hidden" name="product_id" value="<?= $item->product_id ?>">
            <div class="rating-star">
                <div>
                    <h3>Product Quality:</h3>
                </div>
                <div id="stars-container">
                    <img src="../images/full_star_yellow.png" style="width: 50px;" data-star="1" class="star">
                    <img src="../images/full_star_yellow.png" style="width: 50px;" data-star="2" class="star">
                    <img src="../images/full_star_yellow.png" style="width: 50px;" data-star="3" class="star">
                    <img src="../images/full_star_yellow.png" style="width: 50px;" data-star="4" class="star">
                    <img src="../images/full_star_yellow.png" style="width: 50px;" data-star="5" class="star">
                </div>
                <?= html_hidden('rating', 'name="rating" id="rating" value="0" ') ?>
            </div>
            <div class="description-input">
                <h3>Write Your Review:</h3>
                <?= html_textarea('description', 'rows="5" placeholder="Share your experience"') ?>
            </div>
            <div>
                <input type="file" name="photo[]" id="fileInput" accept="video/*|image/*" multiple />
                <div class="file-container" id="fileContainer"></div>
            </div>

            <div class="anonymous-section">
                <label for="anonymous">
                    <input type="checkbox" name="anonymous" id="anonymous">
                    Leave review anonymously
                </label>
            </div>
            <div class="submit-section">
                <button type="reset" id="cancel-review" class="cancel-review" onclick="closePopup()">Cancel</button>
                <?= html_button('submit-review', '', 'Submit', 'type="submit" class="submit-review"') ?>
            </div>
        </form>
    </div>
</div>

<?php include '../_foot.php'; ?>

<script>
    function openEditPopup() {
        document.getElementById('edit-review-popup').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('edit-review-popup').style.display = 'none';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const starsContainer = document.getElementById("stars-container");
        const stars = starsContainer.querySelectorAll(".star");
        const ratingInput = document.getElementById("rating");

        // Function to update the stars based on the selected rating
        function updateStars(selectedStars) {
            stars.forEach((star, index) => {
                if (index < selectedStars) {
                    star.src = "../images/full_star_yellow.png"; // Yellow star
                } else {
                    star.src = "../images/empty_star.png"; // Empty star
                }
            });
        }
        // Set default rating to 5 (yellow stars)
        updateStars(5);
        ratingInput.value = 5; // Set the hidden rating value to 5

        // Handle click on each star to update the rating
        stars.forEach((star, index) => {
            star.addEventListener("click", () => {
                updateStars(index + 1); // Update rating stars
                ratingInput.value = index + 1; // Set hidden rating value
            });
        });
    });

    const fileInput = document.getElementById("fileInput");
    const fileContainer = document.getElementById("fileContainer");

    // Event listener for when files are selected
    fileInput.addEventListener("change", function() {
        // Clear the previous file list
        fileContainer.innerHTML = "";

        // Iterate through the selected files and display them
        Array.from(this.files).forEach((file, index) => {
            // Create a container for each file item
            const fileItem = document.createElement("div");
            fileItem.className = "file-item";

            // Create an element to display the file name
            const fileName = document.createElement("span");
            fileName.textContent = file.name;

            // Create a button to remove the file
            const removeButton = document.createElement("button");
            removeButton.className = "remove-button";
            removeButton.innerHTML = "&times;";
            removeButton.addEventListener("click", () => {
                removeFile(index); // Call the remove function when the button is clicked
            });

            // Append the file name and remove button to the file item
            fileItem.appendChild(fileName);
            fileItem.appendChild(removeButton);

            // Append the file item to the file container
            fileContainer.appendChild(fileItem);
        });
    });

    // Function to remove a specific file from the file input
    function removeFile(index) {
        const files = Array.from(fileInput.files); // Convert FileList to array
        const dt = new DataTransfer(); // Create a DataTransfer object to hold the new file list

        // Add all files except the one to be removed
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });

        // Update the file input with the new file list
        fileInput.files = dt.files;

        // Trigger the change event to refresh the displayed file list
        fileInput.dispatchEvent(new Event("change"));
    }

    document.addEventListener("DOMContentLoaded", function() {

        const deleteAccountForm = document.querySelector('.review-form');

        deleteAccountForm.addEventListener('submit', function(event) {
            const confirmed = confirm("Are you sure you want to submit this review?\nThis is not changeable after the submission.");
            if (!confirmed) {
                event.preventDefault(); // cancel form submission if the user cancel
            }
        });
    });
</script>