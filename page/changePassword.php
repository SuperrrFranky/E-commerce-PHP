<?php
require '../_base.php';
$_title = 'My Profile';
include '../_head.php';

$user_id = $_user->user_id;

if (is_get()) {
    // Fetch user details from the database
    $stmt = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        redirect('/'); // Redirect if user not found
    }

    extract((array)$user);
}

if (is_post()) {
    // Retrieve POST values
    $userid     = $user_id;
    $password   = req('password');

    if (!$_err) {
        $stm = $_db->prepare('
            SELECT * FROM user
            WHERE user_id = ? AND password_hash = SHA1(?)
        ');
        $stm->execute([$userid, $password]);
        $u = $stm->fetch();
        $temp = $u;
        if ($u) {
            temp('info', 'Correct Password');
            changePassword($u, true);
            // Set session variable to allow access to setNewPassword.php
            $_SESSION['password_verified'] = true;
            header('Location: setNewPassword.php'); // Redirect to setNewPassword.php aFter verification
            exit;
        } else {
            $_err['password'] = '*Incorrect Password';
        }
    }
}
?>

<link rel="stylesheet" href="/css/profile.css">

<body onload="myFunction()">

    <!-- Content wrapper to hide sidebar and main content during loading -->
    <div id="content-wrapper" style="display:none;">
        <div class="sidebar">
            <ul>
                <li><a href="profile.php">My Profile</a></li>
                <li class="selected"><a href="changePassword.php">Change Password</a></li>
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
                <p>Change Password</p>
            </div>

            <!-- Main content -->
            <div class="profile-details" style="margin:0;">
                <h2>Change Password</h2>
                <form method="post" class="form" novalidate>
                    <input type="password" name="password" placeholder="Enter currect password" required>
                    <span class="error-message"><?= err('password') ?></span>
                    <button id="pwd-form-button" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader, visible initially -->
    <div id="loader">
        <div class="spinner"></div>
    </div>

</body>

<?php include '../_foot.php'; ?>

<script>
    var myVar;

    function myFunction() {
        console.log("myFunction called");

        // Get the current URL (normalized)
        var currentUrl = window.location.href.split('?')[0].split('#')[0];

        // Check if localStorage is supported
        if (typeof(Storage) !== "undefined") {
            // Check if the URL is the same as the last stored URL
            if (localStorage.getItem("lastUrl") === currentUrl) {
                console.log("URL is the same, skipping loader");
                showPage();
            } else {
                console.log("URL is different, showing loader");
                localStorage.setItem("lastUrl", currentUrl); // Update the last visited URL
                if (myVar) clearTimeout(myVar); // Clear any existing timers
                myVar = setTimeout(showPage, 1000); // Simulate loading time
            }
        } else {
            console.warn("localStorage is not supported, always showing loader");
            if (myVar) clearTimeout(myVar);
            myVar = setTimeout(showPage, 1000);
        }
    }

    function showPage() {
        console.log("showPage called");
        var loader = document.getElementById("loader");
        var contentWrapper = document.getElementById("content-wrapper");

        if (loader) loader.style.display = "none";
        if (contentWrapper) contentWrapper.style.display = "block";
    }
</script>