<?php

require '../../_base.php';

$userId = $_GET['id'] ?? null;
if (!$userId) {
    redirect('/page/admin/viewMembers.php');
}

$stmt = $_db->prepare("SELECT * FROM user WHERE user_id = :userId AND role = 'Member'");
$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$member = $stmt->fetch();

if (!$member) {
    redirect('/page/admin/viewMembers.php');
}

$_title = 'Member Information';
$extraScripts = '<link rel="stylesheet" href="/css/base.css">';
require '../../_head.php';
?>


<div class="detailed-member-card">
    <h1>Member Details</h1>
    <table class="table">
        <tr>
            <th>User ID:</th>
            <td><?= htmlspecialchars($member->user_id) ?></td>
        </tr>
        <tr>
            <th>Username:</th>
            <td><?= htmlspecialchars($member->username) ?></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?= htmlspecialchars($member->email) ?></td>
        </tr>
        <tr>
            <th>Phone:</th>
            <td><?= htmlspecialchars($member->phone) ?></td>
        </tr>
        <tr>
            <th>Role:</th>
            <td><?= htmlspecialchars($member->role) ?></td>
        </tr>
        <tr>
            <th>Created At:</th>
            <td><?= htmlspecialchars($member->created_at) ?></td>
        </tr>
        <tr>
            <th>Updated At:</th>
            <td><?= htmlspecialchars($member->updated_at) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?=
                $status = '';
                switch ($member->status) {
                    case 0:
                        $status = 'Active';
                        break;
                    case 1:
                        $status = 'Suspended';
                        break;
                    case 2: //2
                        $status = 'Blocked';
                        break;
                    default:
                        $status = 'Deactivated';
                        break;
                }
                echo htmlspecialchars($status);
                ?></td>
        </tr>
    </table>
    <br>
    <div style="display: flex;">
        <a href="/page/admin/viewMembers.php?return=1" class="btn">Back to List</a>
        <?php
        $is_suspended = $member->suspension_until == null ? false : true;

        $action_url = '/util/member_actions.php';

        if($member->status == 0) {
            echo '<button class="btn-red" data-get="' . $action_url . '?action=block&userId=' . $member->user_id . '&is_suspended=' . $is_suspended . '">Block</button>';
        } else if ($member->status == 1) {
            echo '<button class="btn-red" data-get="' . $action_url . '?action=block&userId=' . $member->user_id . '&is_suspended=' . $is_suspended . '">Block</button>';
            echo '<button class="btn-green" data-get="' . $action_url . '?action=unsuspend&userId=' . $member->user_id . '&is_suspended=' . $is_suspended . '">Unsuspend</button>';
        } else if($member->status == 2) {
            echo '<button class="btn-green" data-get="' . $action_url . '?action=unblock&userId=' . $member->user_id . '&is_suspended=' . $is_suspended . '">Unblock</button>';
        }

        ?>

    </div>

</div>
</body>

<?php
require '../../_foot.php';
