<?php
require '../_base.php';

if (!isset($_GET['action']) || !isset($_GET['userId']) || !isset($_GET['is_suspended'])) {
    redirect('/page/admin/viewMembers.php');
    exit;
}


$is_suspended = $_GET['is_suspended'];
$userId = filter_var($_GET['userId'], FILTER_VALIDATE_INT);
$action = $_GET['action'];

if (!$userId) {
    redirect('/page/admin/viewMembers.php');
    exit;
}

try {
    switch ($action) {
        case 'block':
            $stmt = $_db->prepare("UPDATE user SET status = 2 WHERE user_id = :userId");
            break;
        case 'unsuspend':
            $stmt = $_db->prepare("UPDATE user SET status = 0, suspension_until = NULL WHERE user_id = :userId");
            break;
        case 'unblock':
            if($is_suspended){
                $stmt = $_db->prepare("UPDATE user SET status = 1 WHERE user_id = :userId");
            } else {
                $stmt = $_db->prepare("UPDATE user SET status = 0 WHERE user_id = :userId");
            }
            break;
        default:
            header('Location: /page/admin/viewMembers.php');
            exit;
    }

    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    redirect('/page/admin/member_detailed.php?id=' . $userId);
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirect('/page/admin/member_detailed.php?id=' . $userId . '&error=1');
}

?>