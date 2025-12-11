<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(0);
    session_start();
}
// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null, $delay = 0)
{
    $url ??= $_SERVER['REQUEST_URI'];

    if ($delay > 0) {
        // Use JavaScript for delayed redirection
        echo "<script>
                setTimeout(function() {
                    window.location.href = '" . htmlspecialchars($url, ENT_QUOTES) . "';
                }, " . ($delay * 1000) . ");
              </script>";
        exit();
    } else {
        // Immediate redirection using header
        header("Location: $url");
        exit();
    }
}


// Set or get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// Obtain uploaded file --> cast to object
function get_file($key)
{
    $f = $_FILES[$key] ?? null;

    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

function get_files($key)
{
    if (empty($_FILES[$key]['name'][0])) return null;
    $files = [];
    foreach ($_FILES[$key]['name'] as $index => $name) {
        $files[] = (object)[
            'name' => $name,
            'type' => $_FILES[$key]['type'][$index],
            'tmp_name' => $_FILES[$key]['tmp_name'][$index],
            'error' => $_FILES[$key]['error'][$index],
            'size' => $_FILES[$key]['size'][$index],
        ];
    }
    return $files;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Is money?
function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

//Generate input type="hidden"
function html_hidden($key, $attr = '')
{
    $value ??= encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

function html_phone($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='tel' id='$key' name='$key' value='$value' $attr>";
}

function html_email($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='email' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

//Generate <input type='checkbox'>
function html_check($key, $class, $value, $attr = '')
{
    echo "<input type='checkbox' id='$key' name='$key' value='$value',class='$class' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}
//Generate button
function html_button($key, $value = '', $displayText, $class = '', $attr = '')
{
    echo "<button id='$key' name='$key' value='$value' class='$class' $attr>$displayText</button>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:dbname=assignment', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is unique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ============================================================================
// Email Functions
// ============================================================================

// Initialize and return mail object
function get_mail()
{
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'demo82418@gmail.com';
    $m->Password = 'aklm plar ispz hqdw';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '😺 Admin');

    return $m;
}
// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

//return local root path
function root($path = '')
{
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}

//return base url
function base($path = '')
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['SERVER_NAME'] . ($protocol == 'https' ? '' : ':' . $_SERVER['SERVER_PORT']) . '/' . $path;
}




// ============================================================================
// Security
// ============================================================================
// Global user object
$_user = $_SESSION['user'] ?? json_decode($_COOKIE['user'] ?? null);

// Login user
function login($user, $url = '/', $remember_me = false)
{
    if ($remember_me) {
        // cookie expires in 1 days
        $expiry = time() + (24 * 60 * 60);

        setcookie('user', json_encode($user), $expiry, '/');
    } else {
        //session
        $_SESSION['user'] = $user;
    }
    redirect($url);
}

// Logout user
function logout($url = '/')
{
    if ($_SESSION['user'] != null) {
        unset($_SESSION['user']);
    } else {
        // Delete cookie by setting expiration to past time
        setcookie('user', '', time() - 3600, '/');
    }
    redirect($url);
}

// Authorization
function auth(...$roles)
{
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return; // OK
            }
        } else {
            return; // OK
        }
    }

    if (in_array('admin', $roles)) {
        redirect('/admin.php');
    } else {
        redirect('/page/user_login.php');
    }
}

// Change password function
function changePassword($user, $remember_me = false)
{
    if ($remember_me) {
        // Set cookie to expire in 30 minutes

        $expiry = time() + (30 * 60); // 30 minutes in seconds
        setcookie('user', json_encode($user), $expiry, '/');
    } else {
        // Use session for temporary storage
        $_SESSION['user'] = $user;
    }
}


function addToCart($userID, $product_id, $quantity, $status = 'pending')
{
    global $_db;

    $stm = $_db->prepare("SELECT price FROM product WHERE product_id = ?");
    $stm->execute([$product_id]);
    $product = $stm->fetch();

    if ($product) {
        $price = $product->price;
        $totalPrice = (int)$price * (int)$quantity;

        $stm = $_db->prepare('SELECT COUNT(*) FROM cart WHERE product_id = ? AND user_id = ?');
        $stm->execute([$product_id, $userID]);

        if ($stm->fetchColumn() == 0) {
            $stm = $_db->prepare("INSERT INTO cart(product_id, user_id, status, quantity, price)
                                  VALUES(?, ?, ?, ?, ?)");
            $stm->execute([$product_id, $userID, $status, (int)$quantity, $totalPrice]);

            temp('info', 'Item added to cart');
        } else {
            $stm = $_db->prepare("UPDATE cart SET quantity = ?, price = ?,status =? WHERE product_id = ? AND user_id = ?");
            $stm->execute([(int)$quantity, $totalPrice, $status, $product_id, $userID]);

            temp('info', 'Item quantity updated');
        }
    } else {
        temp('error', 'Product not found');
    }
}


function addToWishList($userId, $productId)
{
    global $_db;

    $stm = $_db->prepare("SELECT COUNT(*) FROM wishlist
                            WHERE product_id=? AND user_id = ?");

    $stm->execute([$productId, $userId]);

    if ($stm->fetchColumn() == 0) {
        $stm = $_db->prepare("INSERT INTO wishlist(product_id,user_id)
        VALUES(?,?)");

        $stm->execute([$productId, $userId]);
        temp('info', 'Item added to wishlist');
        redirect('', $delay = 0.1);

        //echo '<script> alert("item added to wishlist")</script>';
    } else {
        temp('info', 'item already in wishlist');
        redirect('', $delay = 0.1);

        //echo '<script> alert("item already in wishlist")</script>';
    }
}

function getCartItems($userId, $statuses)
{
    global $_db;

    if (!is_array($statuses)) {
        $statuses = [$statuses];
    }

    $placeholders = implode(',', array_fill(0, count($statuses), '?'));

    $stm = $_db->prepare("
        SELECT product.*, cart.quantity AS cQuantity, cart.price AS cPrice, cart.status 
        FROM product
        JOIN cart ON cart.product_id = product.product_id
        WHERE cart.user_id = ? AND cart.status IN ($placeholders)
    ");

    $stm->execute(array_merge([$userId], $statuses));

    return $stm->fetchAll();
}

function parseSearchInput($input, $validKeys)
{
    $criteria = [];
    $pairs = preg_split('/\s+/', $input); //split by whitespace

    foreach ($pairs as $pair) {
        $parts = explode(':', $pair, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            //check empty
            if (!empty($key) && !empty($value) && in_array($key, $validKeys)) {
                $criteria[$key] = $value;
            }
        }
    }

    return $criteria;
}

function getValidKeys($tableName, $pdo)
{
    $validKeys = [];
    $query = "DESCRIBE $tableName";

    try {
        $stmt = $pdo->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {  //associative array
            $validKeys[] = $row['Field']; //field is the header name for the column names returned by the 'DESC' operation
        }
    } catch (PDOException $e) {
        die();
    }

    return $validKeys;
}
function updateOrderStatusToCanceled()
{
    global $_db;

    $query = "
        UPDATE orders 
        SET order_status = 'canceled'
        WHERE payment_status = 'pending' 
        AND order_date < DATE_SUB(NOW(), INTERVAL 2 DAY)
    ";

    $stm = $_db->prepare($query);
    $stm->execute();
}
function generateInvoicePDF($order_id)
{
    global $_db;

    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);

    $ordered_item = $_db->prepare("SELECT o.total_amount, o.order_date, o.used_point, i.quantity, i.price_per_unit, p.name, p.product_photo, a.addressDetail
                                 FROM orders as  o
                                 JOIN order_item as i ON o.order_id = i.order_id
                                 JOIN product as p ON i.product_id = p.product_id
                                 JOIN address as a ON o.address_id = a.address_id
                                  WHERE o.order_id = ?");

    $ordered_item->execute([$order_id]);
    $ordered_items = $ordered_item->fetchAll(PDO::FETCH_OBJ);

    $first_item = $ordered_items[0];
    $discount = $first_item->used_point / 100;
    $subtotal = 0;
    $tax_rate = 0.06;
    $delivery_fee = 10;

    foreach ($ordered_items as $item) {
        $subtotal += $item->quantity * $item->price_per_unit;
    }

    ob_start();
    include __DIR__ . '/page/invoice_template.php';
    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    $filePath = __DIR__ . "/page/invoices/Invoice_$order_id.pdf";
    $directoryPath = dirname($filePath);

    if (!is_dir($directoryPath)) {
        if (!mkdir($directoryPath, 0777, true) && !is_dir($directoryPath)) {
            throw new RuntimeException("Failed to create directory: $directoryPath");
        }
    }

    if (file_put_contents($filePath, $pdfOutput) === false) {
        throw new RuntimeException("Failed to save PDF to $filePath");
    }

    return $filePath;
}
