<?php
require '../_base.php';
$_title = 'My Profile';
include '../_head.php';

$is_admin = get('is_admin');
if($is_admin){
    auth("admin");
} else {
    auth();
}

// Temporary until session is implemented
$user_id = $_user->user_id; // Replace with session or authentication mechanism

// Fetch user details from the database
$stmt = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$GLOBALS['name'] = $user->username;

if (!$user) {
    redirect('user_login.php');
}

extract((array)$user);
$_SESSION['photo'] = !empty($user->profile_photo) ? $user->profile_photo : 'photo.jpg';

$_genders = [
    'M' => 'Male',
    'F' => 'Female',
];

if (is_post()) {
    // Retrieve POST values
    $id         = $user_id;
    $name       = req('name');
    $gender     = req('gender');
    $email      = req('email');
    $phone_no   = req('phoneNo');
    $photo      = $_SESSION['photo'];
    $file       = get_file('photo');

    // Validate inputs
    if (empty($name)) {
        $_err['name'] = 'Name is required.';
    } elseif (strlen($name) > 100) {
        $_err['name'] = 'Name must not exceed 100 characters.';
    }

    if (empty($gender)) {
        $_err['gender'] = 'Gender is required.';
    }

    if (empty($email)) {
        $_err['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format.';
    }

    if (empty($phone_no)) {
        $_err['phoneNo'] = 'Required';
    } else if (!preg_match('/^(1[0-9])-?[0-9]{7,8}$/', $phone_no)) {
        $_err['phoneNo'] = 'Invalid Phone Number';
    }

    if ($file && $file->error === UPLOAD_ERR_OK) {
        if (!str_starts_with($file->type, 'image/')) {
            $_err['photo'] = 'File must be an image.';
        } elseif ($file->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Image size must be 1MB or less.';
        }
    }

    // Process form if no errors
    if (!$_err) {
        // Handle photo update logic
        if ($file && $file->error === UPLOAD_ERR_OK) {
            // Save new photo if uploaded
            unlink("../images/$photo");
            $photo = save_photo($file, '../images');
        } else {
            // If no new photo uploaded, retain the existing one
            $photo = !empty($photo) ? $photo : 'photo.jpg'; // Retain existing photo or use default if none exists
        }

        // Update user details in the database
        $stmt = $_db->prepare('UPDATE user SET username = ?, gender = ?, email = ?, phone = ?, profile_photo = ? WHERE user_id = ?');
        $stmt->execute([$name, $gender, $email, $phone_no, $photo, $id]);

        temp('info', 'Profile updated successfully.');
        redirect('profile.php');
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/profile.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
</head>

<div class="sidebar">
    <ul>
        <li class="selected"><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<div class="main-layt">
    <div class="profile-title">
        <p>Profile Details</p>
    </div>

    <div class="profile-details">
        <form method="post" class="form" enctype="multipart/form-data" novalidate>
            <!-- User Details Table -->
            <div class="internal-container">
                <div>
                    <table class="input-form">
                        <tr>
                            <th>Account ID</th>
                            <td><?= $user->user_id ?></td>
                        </tr>
                        <tr>
                            <th><label for="name">Name</label></th>
                            <td> <?php html_text('name', 'type="text"  required'); ?> <?php ?>
                                <span class="error-message-address"><?= err('name') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td> <input type="tel" id="phoneNo" name="phoneNo" value="<?= htmlspecialchars($user->phone ?? '') ?>" required>
                                <span class="error-message-address"><?= err('phoneNo') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td> <?php html_text('email', 'type="email" required'); ?>
                                <span class="error-message-address"><?= err('email') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>
                                <div class="radio">
                                    <?php
                                    // Generate radio buttons for gender selection
                                    foreach ($_genders as $key => $value): ?>
                                        <label>
                                            <input type="radio" name="gender" value="<?= htmlspecialchars($key) ?>"
                                                <?= $key == $user->gender ? 'checked' : '' ?> required>
                                            <?= htmlspecialchars($value) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td> ************ &nbsp&nbsp <a href="changePassword.php">Change</a></td>
                        </tr>
                    </table>
                </div>
                <div class="vertical"></div>
                <!-- Profile Picture Upload -->
                <div class="picture-part">
                    <div class="profile-picture">
                        <!-- Drag and Drop Area -->
                        <div class="box" id="drop-area">
                            <label class="upload" tabindex="0">
                                <input type="file" name="photo" id="photo" class="upload-input" accept="image/*">
                                <!-- Display the existing or default photo -->
                                <?php $profile_photo = strlen($user->profile_photo) != 0 ? '../images/' .$user->profile_photo : '../images/photo.jpg'; 
                                ?>
                                <img id="uploaded-photo" src="<?= $profile_photo ?>" alt="Profile Photo" width="100">
                            </label>
                            <div class="file-list"></div>
                        </div>
                    </div>
                    <p>File Size: MAX 1MB</p>
                    <p>File Type: JPEG, PNG</p>
                </div>
            </div>
            <!-- Submit Button -->
            <button id="form-button" type="submit">Save Changes</button>
            <button id="form-button" type="reset">Reset</button>
        </form>
    </div>
</div>

<script>
    const box = document.querySelector('#drop-area');
    const fileInput = document.querySelector('#photo'); // File input field
    const uploadedPhoto = document.querySelector('#uploaded-photo'); // Image preview element

    let droppedFile = null; // To store the dropped file temporarily

    // Prevent default behavior for drag events
    ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(event => {
        box.addEventListener(event, e => {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    // Highlight drop area on dragover and dragenter
    ['dragover', 'dragenter'].forEach(event => {
        box.addEventListener(event, () => box.classList.add('is-dragover'));
    });

    // Remove highlight on dragleave, dragend, or drop
    ['dragleave', 'dragend', 'drop'].forEach(event => {
        box.addEventListener(event, () => box.classList.remove('is-dragover'));
    });

    // Handle file drop
    box.addEventListener('drop', e => {
        droppedFile = e.dataTransfer.files[0]; // Store the dropped file
        updateFilePreview(droppedFile);
    });

    // Handle file selection through the file input field
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            droppedFile = fileInput.files[0]; // Store the selected file
            updateFilePreview(droppedFile);
        }
    });

    // Function to update the image preview
    function updateFilePreview(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                uploadedPhoto.src = e.target.result; // Update image preview
            };
            reader.readAsDataURL(file);
        }
    }

    // Before submitting the form, manually attach the dropped file to the file input
    document.querySelector('form').addEventListener('submit', () => {
        if (droppedFile) {
            // Create a new DataTransfer object to simulate the file input's `files`
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(droppedFile);
            fileInput.files = dataTransfer.files; // Assign the dropped file to the file input
        }
    });
</script>

<?php include '../_foot.php'; ?>