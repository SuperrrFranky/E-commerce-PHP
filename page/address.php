<?php

use function PHPSTORM_META\type;

require '../_base.php';
$_title = 'My Address';
include '../_head.php';

auth();

$user_id = $_user->user_id;

// Fetch address details for the logged-in user
$stmt = $_db->prepare('SELECT * FROM address WHERE user_id = ? ORDER BY is_default DESC');
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

foreach ($addresses as $address) {
    $GLOBALS['address_id' . $address->address_id] = $address->address_id;
    $GLOBALS['state' . $address->address_id] = $address->state;
    $GLOBALS['street' . $address->address_id] = $address->street;
    $GLOBALS['postcode' . $address->address_id] = $address->postcode;
    $GLOBALS['receiver_name' . $address->address_id] = $address->receiver_name;
    $GLOBALS['receiver_no' . $address->address_id] = $address->receiver_no;
    $GLOBALS['addressDetail' . $address->address_id] = $address->addressDetail;
}

if (is_post()) {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'create' || $action === 'edit') {

        $id = req('address_id');
        $state = req('state');
        $street = req('street');
        $postcode = req('postcode');
        $addressDetail = req('addressDetail');
        $receiver_name = req('receiver_name');
        $receiver_no = req('receiver_no');
        $latitude = req('latitude');
        $longitude = req('longitude');

        $edit_state = req('edit-state');
        $edit_street = req('edit-street');
        $edit_postcode = req('edit-postcode');
        $edit_addressDetail = req('edit-addressDetail');
        $edit_receiver_name = req('edit-receiver_name');
        $edit_receiver_no = req('edit-receiver_no');
        $edit_latitude = req('edit-latitude');
        $edit_longitude = req('edit-longitude');

        // echo "<pre>";
        // print_r($latitude);
        // print_r($longitude);
        // echo "</pre>";

        if ($action === 'edit') {

            // Validation for edit
            if (empty($edit_state)) {
                $_err['edit-state'] = '*State is required.';
            }

            if (empty($edit_street)) {
                $_err['edit-street'] = '*Street is required.';
            }

            // if (empty($edit_postcode)) {
            //     $_err['edit-postcode'] = '*Postcode is required.';
            // } elseif (!preg_match('/^\d{5}$/', $postcode)) {
            //     $_err['edit-postcode'] = '*Postcode must be 5 digits.';
            // }

            if (empty($edit_addressDetail)) {
                $_err['edit-addressDetail'] = '*Address is required.';
            }

            if (empty($edit_receiver_name)) {
                $_err['edit-receiver_name'] = '*Receiver name is required.';
            } elseif (strlen($receiver_name) > 100) {
                $_err['edit-receiver_name'] = '*Receiver name must not exceed 100 characters.';
            }

            if (empty($edit_receiver_no)) {
                $_err['edit-receiver_no'] = '*Receiver contact number is required.';
            } elseif (!preg_match('/^(1[0-9])-?[0-9]{7,8}$/', $edit_receiver_no)) {
                $_err['edit-receiver_no'] = '*Invalid Phone Number.';
            }

            if (empty($edit_latitude) || empty($edit_longitude)) {
                $_err['map-edit'] = '*Please select a location on the map.';
            }

            // Update address
            if (!$_err) {
                $stmt = $_db->prepare('UPDATE address SET state = ?, street = ?, postcode = ?, addressDetail = ?, receiver_name = ?, receiver_no = ?, latitude = ?, longitude = ? WHERE address_id = ? AND user_id = ?');
                $stmt->execute([$edit_state, $edit_street, $edit_postcode, $edit_addressDetail, $edit_receiver_name, $edit_receiver_no, $edit_latitude, $edit_longitude, $id, $user_id]);
                temp('info', 'Address updated successfully.');
                redirect('address.php');
            }
        } elseif ($action === 'create') {
            // Validate inputs
            if (empty($state)) {
                $_err['state'] = '*State is required.';
            }

            if (empty($street)) {
                $_err['street'] = '*Street is required.';
            }

            if (empty($postcode)) {
                $_err['postcode'] = '*Postcode is required.';
            } elseif (!preg_match('/^\d{5}$/', $postcode)) {
                $_err['postcode'] = '*Postcode must be 5 digits.';
            }

            if (empty($addressDetail)) {
                $_err['addressDetail'] = '*Address is required.';
            }

            if (empty($receiver_name)) {
                $_err['receiver_name'] = '*Receiver name is required.';
            } elseif (strlen($receiver_name) > 100) {
                $_err['receiver_name'] = '*Receiver name must not exceed 100 characters.';
            }

            if (empty($receiver_no)) {
                $_err['receiver_no'] = '*Receiver contact number is required.';
            } elseif (!preg_match('/^(1[0-9])-?[0-9]{7,8}$/', $receiver_no)) {
                $_err['receiver_no'] = '*Invalid Phone Number.';
            }

            if (empty($latitude) || empty($longitude)) {
                $_err['map'] = '*Please select a location on the map.';
            }

            // Use for add-address-btn
            if ($_err) {
                $_SESSION['error'] = true;
            }

            // Insert address
            if (!$_err) {
                $stmt = $_db->prepare('INSERT INTO address (user_id, state, street, postcode, addressDetail, receiver_name, receiver_no, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$user_id, $state, $street, $postcode, $addressDetail, $receiver_name, $receiver_no, $latitude, $longitude]);
                temp('info', 'Address saved successfully.');
                redirect('address.php');
            }
        }
    }

    if ($action === 'delete') {
        if (isset($_POST['delete'])) {
            $id = $_POST['address_id'];  // Get the address_id from the form submission

            // Prepare and execute the delete query
            $stmt = $_db->prepare('DELETE FROM address WHERE address_id = ?');
            $stmt->execute([$id]);

            // Redirect to the address page after deletion
            temp('info', 'Address deleted successfully.');
            redirect('address.php');
        }
    }

    if ($action === 'set_default') {
        if (isset($_POST['set_default'])) {
            $address_id = $_POST['address_id'];

            // Set all addresses to non-default
            $stmt = $_db->prepare('UPDATE address SET is_default = 0 WHERE user_id = ?');
            $stmt->execute([$user_id]);

            // Set the selected address as default
            $stmt = $_db->prepare('UPDATE address SET is_default = 1 WHERE address_id = ? AND user_id = ?');
            $stmt->execute([$address_id, $user_id]);

            temp('info', 'Address set as default.');
            redirect('address.php');
        }
    }

    if (isset($_POST['delete_selected']) && isset($_POST['address_ids'])) {
        $addressIds = $_POST['address_ids'];

        foreach ($addressIds as $address_id) {
            $stmt = $_db->prepare('DELETE FROM address WHERE address_id = ?');
            $stmt->execute([$address_id]);
        }

        temp('info', 'Address deleted successfully.');
        redirect('address.php');
    }
}
?>

<head>
    <link rel="stylesheet" href="/css/profile.css">
    <!-- Include Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const map = L.map('map').setView([3.1390, 101.6869], 4); // Default coordinates
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);

            let marker;
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const stateInput = document.getElementById('state');
            const streetInput = document.getElementById('street');
            const postcodeInput = document.getElementById('postcode');
            const addressDetailInput = document.getElementById('addressDetail');

            // Function to fetch latitude and longitude using Google Maps API
            async function fetchLatLong(address) {
                const apiKey = 'AIzaSyA_NWBfdBxrVOjj-vCEV2uY7gaSSM3X6JE'; // Use a secure method for API key
                const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${apiKey}`;
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.status === 'OK') {
                        const lat = data.results[0].geometry.location.lat;
                        const lng = data.results[0].geometry.location.lng;
                        return {
                            lat,
                            lng
                        };
                    } else {
                        console.error("Error fetching location:", data.status);
                        return null;
                    }
                } catch (error) {
                    console.error("Fetch error:", error);
                    return null;
                }
            }

            // Event handler to update map and form inputs when the address form is filled
            async function updateMapAndCoordinates() {
                const state = stateInput.value;
                const street = streetInput.value;
                const postcode = postcodeInput.value;
                const addressDetail = addressDetailInput.value;

                const fullAddress = `${addressDetail}, ${street}, ${postcode}, ${state}`;
                const location = await fetchLatLong(fullAddress);

                if (location) {
                    const {
                        lat,
                        lng
                    } = location;

                    // Update hidden inputs
                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);

                    // Update map
                    map.setView([lat, lng], 17);
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng]).addTo(map);
                    }
                }
                // else {
                //     alert("Unable to fetch location.");
                // }
            }

            // Trigger the update map function when any of the input fields change
            stateInput.addEventListener('input', updateMapAndCoordinates);
            streetInput.addEventListener('input', updateMapAndCoordinates);
            postcodeInput.addEventListener('input', updateMapAndCoordinates);
            addressDetailInput.addEventListener('input', updateMapAndCoordinates);

            // Handle form submission to include updated coordinates in the hidden fields
            document.getElementById('address-form').addEventListener('submit', function(event) {
                if (!latInput.value || !lngInput.value) {
                    event.preventDefault(); // Prevent form submission if coordinates are not set
                    alert("Please complete the address and map.");
                }
            });

            // Populate map with existing coordinates if editing
            const existingLat = parseFloat(latInput.value) || 3.1390;
            const existingLng = parseFloat(lngInput.value) || 101.6869;
            if (existingLat && existingLng) {
                marker = L.marker([existingLat, existingLng]).addTo(map);
                map.setView([existingLat, existingLng], 17);
            }

            // Handle map click to set marker and update inputs
            map.on('click', function(e) {
                const {
                    lat,
                    lng
                } = e.latlng;

                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);

                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
            });
        });

        // Handle add address->show when want to add address
        document.addEventListener("DOMContentLoaded", function() {
            const addAddressBtn = document.getElementById("add-address-btn");
            const addressForm = document.getElementById("address-form");


            // Check if an error occurred and open the form
            <?php if (isset($_SESSION['error']) && $_SESSION['error'] === true): ?>
                addressForm.style.display = "block";
                addAddressBtn.classList.add("toggled");
                //clear the error flag to prevent it from persisting across refreshes
                <?php unset($_SESSION['error']); ?>
            <?php else: ?>
                addressForm.style.display = "none";
                addAddressBtn.classList.remove("toggled");
            <?php endif; ?>

            addAddressBtn.addEventListener("click", function() {
                // Toggle form visibility
                const isVisible = addressForm.style.display === "block";
                addressForm.style.display = isVisible ? "none" : "block";

                // Save state to localStorage
                localStorage.setItem('addressFormOpen', !isVisible);

                // Toggle button class
                addAddressBtn.classList.toggle("toggled", !isVisible);
            });
        });

        // Open the edit address popup and assign the fields
        function openEditPopup(addressId, state, street, postcode, address, receiverName, receiverNo, latitude, longitude) {

            document.getElementById('edit-address-popup').style.display = 'flex';
            document.getElementById('edit-address-id').value = addressId;
            document.getElementById('edit-state').value = state;
            document.getElementById('edit-street').value = street;
            document.getElementById('edit-postcode').value = postcode;
            document.getElementById('edit-addressDetail').value = address;
            document.getElementById('edit-receiver_name').value = receiverName;
            document.getElementById('edit-receiver_no').value = receiverNo;
            document.getElementById('edit-latitude').value = latitude;
            document.getElementById('edit-longitude').value = longitude;
        }

        // Handle edit map
        document.addEventListener("DOMContentLoaded", function() {
            const mapEdit = L.map('map-edit').setView([3.1390, 101.6869], 4); // Default coordinates
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(mapEdit);

            let markerEdit;
            const edit_latInput = document.getElementById('edit-latitude');
            const edit_lngInput = document.getElementById('edit-longitude');
            const edit_stateInput = document.getElementById('edit-state');
            const edit_streetInput = document.getElementById('edit-street');
            const edit_postcodeInput = document.getElementById('edit-postcode');
            const edit_addressDetailInput = document.getElementById('edit-addressDetail');

            // Function to fetch latitude and longitude using Google Maps API
            async function fetchLatLong(address) {
                const apiKey = 'AIzaSyA_NWBfdBxrVOjj-vCEV2uY7gaSSM3X6JE'; // Use a secure method for API key
                const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${apiKey}`;
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.status === 'OK') {
                        const lat = data.results[0].geometry.location.lat;
                        const lng = data.results[0].geometry.location.lng;
                        return { lat, lng };
                    } else {
                        console.error("Error fetching location:", data.status);
                        return null;
                    }
                } catch (error) {
                    console.error("Fetch error:", error);
                    return null;
                }
            }

            // Event handler to update map and form inputs when the address form is filled
            async function updateMapAndCoordinates() {
                const edit_state = edit_stateInput.value;
                const edit_street = edit_streetInput.value;
                const edit_postcode = edit_postcodeInput.value;
                const edit_addressDetail = edit_addressDetailInput.value;

                const edit_fullAddress = `${edit_addressDetail}, ${edit_street}, ${edit_postcode}, ${edit_state}`;
                const edit_location = await fetchLatLong(edit_fullAddress);

                if (edit_location) {
                    const { lat, lng } = edit_location;

                    // Update hidden inputs
                    edit_latInput.value = lat.toFixed(6);
                    edit_lngInput.value = lng.toFixed(6);

                    // document.getElementById('edit-latitude').value = lat.toFixed(6);
                    // document.getElementById('edit-longitude').value = lng.toFixed(6);
                    // console.log("Latitude: ", document.getElementById('edit-latitude').value);
                    // console.log("Longitude: ", document.getElementById('edit-longitude').value);
                    // console.log("dasdasd");
                    // console.log("Latitude: ", edit_latInput.value);
                    // console.log("Longitude: ", edit_lngInput.value);
                    // debugger;

                    // Update map
                    mapEdit.setView([lat, lng], 17);
                    if (markerEdit) {
                        markerEdit.setLatLng([lat, lng]);
                    } else {
                        markerEdit = L.marker([lat, lng]).addTo(mapEdit);
                    }
                }
                // else {
                //     alert("Unable to fetch location.");
                // }
            }

            // Trigger the update map function when any of the input fields change
            edit_stateInput.addEventListener('input', updateMapAndCoordinates);
            edit_streetInput.addEventListener('input', updateMapAndCoordinates);
            edit_postcodeInput.addEventListener('input', updateMapAndCoordinates);
            edit_addressDetailInput.addEventListener('input', updateMapAndCoordinates);

            // Handle form submission to include updated coordinates in the hidden fields
            document.getElementById('edit-address-form').addEventListener('submit', function(event) {
                // document.getElementById('edit-latitude').value = lat.toFixed(6);
                // document.getElementById('edit-longitude').value = lng.toFixed(6);
                // console.log("Latitude: ", document.getElementById('edit-latitude').value);
                // console.log("Longitude: ", document.getElementById('edit-longitude').value);
                // debugger;
                if (!latInput.value || !lngInput.value) {
                    event.preventDefault(); // Prevent form submission if coordinates are not set
                    alert("Please complete the address and map.");
                }
            });

            // Populate map with existing coordinates if editing
            const edit_existingLat = parseFloat(edit_latInput.value) || 3.1390;
            const edit_existingLng = parseFloat(edit_lngInput.value) || 101.6869;
            if (edit_existingLat && edit_existingLng) {
                markerEdit = L.marker([edit_existingLat, edit_existingLng]).addTo(mapEdit);
                mapEdit.setView([edit_existingLat, edit_existingLng], 17);
            }

            // Handle map click to set marker and update inputs
            mapEdit.on('click', function(e) {
                const { lat, lng } = e.latlng;

                edit_latInput.value = lat.toFixed(6);
                edit_lngInput.value = lng.toFixed(6);

                if (markerEdit) {
                    markerEdit.setLatLng([lat, lng]);
                } else {
                    markerEdit = L.marker([lat, lng]).addTo(mapEdit);
                }
            });
        });

        // Close the Edit Address Popup
        function closeEditPopup() {
            document.getElementById('edit-address-popup').style.display = 'none';
        }

        // batch deleting
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const deleteButton = document.getElementById('delete_selected');
            const addressCheckboxes = document.querySelectorAll('.address-checkbox');

            // Update delete button visibility based on selected checkboxes
            function updateDeleteButton() {
                const selectedCheckboxes = document.querySelectorAll('.address-checkbox:checked');
                deleteButton.style.display = selectedCheckboxes.length > 0 ? 'inline-block' : 'none';
            }

            // When the 'Select All' checkbox is clicked
            selectAllCheckbox.addEventListener('change', function() {
                addressCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateDeleteButton();
            });

            // When an individual checkbox is clicked
            addressCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateDeleteButton);
            });

            // Initial update of the delete button visibility
            updateDeleteButton();
        });
    </script>
</head>

<!-- Sidebar -->
<div class="sidebar">
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="changePassword.php">Change Password</a></li>
        <li class="selected"><a href="address.php">Address</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="userpoint.php">My Points</a></li>
        <li><a href="pendingReview.php">My Reviews</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>
</div>

<!-- Main Layout -->
<div class="main-layt">
    <div class="profile-title">
        <p>My Addresses</p>
    </div>

    <div class="profile-details">
        <!-- Button to Show Address Form -->
        <!-- <ul>
            <li class="add-address"> -->
        <button id="add-address-btn">+ Create / Add Address</button>

        <!-- Address Form -->
        <form id="address-form" method="post" class="form" style="display: none;" novalidate>
            <!-- Address Form -->
            <div class="internal-container">
                <table class="input-form">
                    <input type="hidden" name="action" value="create">
                    <tr>
                        <th><label for="state">State</label></th>
                        <td><?= html_text('state', 'placeholder="State" required maxlength="100"') ?><span class="error-message-address"><?= err('state') ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="street">Street</label></th>
                        <td><?= html_text('street', 'placeholder="Street" required maxlength="255"') ?><span class="error-message-address"><?= err('street') ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="postcode">Postcode</label></th>
                        <td><?= html_text('postcode', 'placeholder="XXXXX" required maxlength="5"') ?><span class="error-message-address"><?= err('postcode') ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="address">Address</label></th>
                        <td><?= html_text('addressDetail', 'placeholder="House number, building, street name" required maxlength="100"') ?><span class="error-message-address"><?= err('addressDetail') ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="receiver_name">Receiver Name</label></th>
                        <td><?= html_text('receiver_name', 'placeholder="Name" required maxlength="100"') ?><span class="error-message-address"><?= err('receiver_name') ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="receiver_no">Receiver Contact No</label></th>
                        <td><?= html_text('receiver_no', 'placeholder="XX XXX XXXX" required maxlength="15"') ?><span class="error-message-address"><?= err('receiver_no') ?></span></td>
                    </tr>
                </table>
            </div>

            <!-- Map Section -->
            <div id="map" style="height: 300px; margin: 20px 0;"></div>
            <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars(req('latitude') ?? '') ?>">
            <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars(req('longitude') ?? '') ?>">
            <span class="error-message-address"><?= err('map') ?></span>

            <?= html_button('reset-address', '', 'Reset', 'reset-address', 'type="reset"')?>
            <button id="form-button" type="submit">Save Address</button>
        </form>
        <hr>
        <!-- Existing Addresses -->
        <div class="address-list">
            <h3>Saved Addresses</h3>
            <?php if (!empty($addresses)): ?>
                <form method="post" action="address.php" id="addressForm" novalidate>
                    <table class="address-table">
                        <thead>
                            <tr>
                                <th><?= html_checkbox('selectAll') ?></th> <!-- Select All checkbox -->
                                <th>Address</th>
                                <th>Receiver Name</th>
                                <th>Phone Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($addresses as $address): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="address_ids[]" value="<?= $address->address_id ?>" class="address-checkbox">
                                    </td>
                                    <td class="address-col">
                                        <?= htmlspecialchars($address->addressDetail) ?><br>
                                        <?= htmlspecialchars($address->state) ?>,
                                        <?= htmlspecialchars($address->street) ?>,
                                        <?= htmlspecialchars($address->postcode) ?>

                                        <?php if ($address->is_default == 1): ?>
                                            <div class="default-address">Default</div> <!-- Show default label -->
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($address->receiver_name) ?></td>
                                    <td><?= htmlspecialchars($address->receiver_no) ?></td>
                                    <td class="address-col-1">
                                        <!-- Edit Button -->
                                        <a href="#" onclick="openEditPopup(
                                        '<?= $address->address_id ?>',
                                        '<?= $address->state ?>',
                                        '<?= $address->street ?>',
                                        '<?= $address->postcode ?>',
                                        '<?= $address->addressDetail ?>',
                                        '<?= $address->receiver_name ?>',
                                        '<?= $address->receiver_no ?>',
                                        '<?= $address->latitude ?>',
                                        '<?= $address->longitude ?>'
                                    )">Edit</a> |
                                        <!-- Delete Button -->
                                        <form method="POST" action="address.php" onsubmit="return confirm('Are you sure to delete this address?')" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="address_id" value="<?= $address->address_id ?>">
                                            <button type="submit" name="delete" id="delete" class="delete-button">Delete</button>
                                        </form>
                                        <!-- Set the default button when only show if it si not the default address -->
                                        <?php if ($address->is_default != 1): ?>
                                            <form method="POST" action="address.php" style="display:inline;">
                                                <input type="hidden" name="action" value="set_default">
                                                <input type="hidden" name="address_id" value="<?= $address->address_id ?>">
                                                <button type="submit" name="set_default" class="set-default-button">Set as Default</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Delete Button (Only shows when at least one address is selected) -->
                    <?= html_button('delete_selected', '', '⇪ Delete Selected Address', '', 'type="submit" onclick="return confirm(\'Are you sure to delete the selected addresses?\')" style="display:none;"') ?>

                </form>
            <?php else: ?>
                <p>No addresses found.</p>
            <?php endif; ?>
        </div>
    </div>


    <!-- Edit Address Popup (Hidden by default) -->
    <div id="edit-address-popup" class="popup-overlay">
        <div class="popup-content">
            <div class="close-popup-header">
                <h2>Edit Address</h2>
                <p><span onclick="closeEditPopup()">✖</span></p>
            </div>

            <form id="edit-address-form" method="post" class="form" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="address_id" id="edit-address-id">

                <label for="edit-state">State</label>
                <?= html_text('edit-state', ' required maxlength="100"') ?>
                <span class="error-message-address"><?= err('edit-state') ?></span>

                <label for="edit-street">Street</label>
                <?= html_text('edit-street', ' required maxlength="255"') ?>
                <span class="error-message-address"><?= err('edit-street') ?></span>

                <label for="edit-postcode">Postcode</label>
                <?= html_text('edit-postcode', ' required maxlength="5"') ?>
                <span class="error-message-address"><?= err('edit-postcode') ?></span>

                <label for="edit-address">Address</label>
                <?= html_text('edit-addressDetail', ' required maxlength="100"') ?>
                <span class="error-message-address"><?= err('edit-addressDetail') ?></span>

                <label for="edit-receiver_name">Receiver Name</label>
                <?= html_text('edit-receiver_name', ' required maxlength="100"') ?>
                <span class="error-message-address"><?= err('edit-receiver_name') ?></span>

                <label for="edit-receiver_no">Receiver Contact No</label>
                <?= html_text('edit-receiver_no', ' required maxlength="15"') ?>
                <span class="error-message-address"><?= err('edit-receiver_no') ?></span>

                <div id="map-edit" style="height: 300px; margin: 20px 0;"></div>
                <input type="hidden" name="edit-latitude" id="edit-latitude" value="<?= htmlspecialchars(req('edit-latitude') ?? '') ?>">
                <input type="hidden" name="edit-longitude" id="edit-longitude" value="<?= htmlspecialchars(req('edit-longitude') ?? '') ?>">
                <span class="error-message-address"><?= err('map-edit') ?></span>

                <?= html_button('reset-address', '', 'Reset', 'reset-address', 'type="reset"')?>
                <button type="submit" name="save-address" id="form-button">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>