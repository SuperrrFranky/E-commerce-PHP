<?php
require '../../_base.php';

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;
$totalRecords = 0;

$searchInput = $_POST['search'] ?? '';

$validKeys = getValidKeys('user', $_db);

$criteria = parseSearchInput($searchInput, $validKeys);

$results = null;
if ($criteria) {
    //pagination count
    $countQuery = "SELECT COUNT(*) as total FROM user WHERE `role` = 'Member' AND 1";
    foreach ($criteria as $key => $value) {
        $countQuery .= " AND $key LIKE :$key";
    }
    $countStmt = $_db->prepare($countQuery);
    foreach ($criteria as $key => $value) {
        $countStmt->bindValue(":$key", "%$value%");
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_OBJ)->total;

    //pagination data
    $query = "SELECT * FROM user WHERE `role` = 'Member' AND 1";
    foreach ($criteria as $key => $value) {
        $query .= " AND $key LIKE :$key";
    }
    $query .= " LIMIT :offset, :limit";
    
    $stmt = $_db->prepare($query);
    foreach ($criteria as $key => $value) {
        $stmt->bindValue(":$key", "%$value%");
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();

} else {
    //pagination count
    $countQuery = "SELECT COUNT(*) as total FROM user WHERE `role` = 'Member' AND (username LIKE :searchInput OR email LIKE :searchInput OR phone LIKE :searchInput OR `role` LIKE :searchInput)";
    $countStmt = $_db->prepare($countQuery);
    $countStmt->bindValue(':searchInput', "%$searchInput%");
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_OBJ)->total;

    //pagination data
    $query = "SELECT * FROM user WHERE `role` = 'Member' AND (username LIKE :searchInput OR email LIKE :searchInput OR phone LIKE :searchInput OR `role` LIKE :searchInput) LIMIT :offset, :limit";
    $stmt = $_db->prepare($query);
    $stmt->bindValue(':searchInput', "%$searchInput%");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
}

$totalPages = ceil($totalRecords / $recordsPerPage);
$startRecord = $offset + 1;
$endRecord = min($offset + $recordsPerPage, $totalRecords);

if ($results && count($results) > 0) {
    foreach ($results as $row) {
        echo "<tr class='clickable-row' data-userid='$row->user_id' style='cursor: pointer;'>";
        echo "<td>$row->user_id</td>";
        echo "<td>$row->username</td>";
        echo "<td>$row->email</td>";
        echo "<td>$row->phone</td>";
        echo "<td>$row->role</td>";
        echo "<td>$row->created_at</td>";
        echo "<td>$row->updated_at</td>";
        echo "</tr>";
    }
    
    // Pagination controls
    echo "<tr>";
    echo "<td colspan=\"7\" style=\"text-align: center;\">";
    echo "Showing $startRecord-$endRecord of $totalRecords records | ";
    
    // Previous page link
    if ($page > 1) {
        echo "<a href='#' class='pagination-link' data-page='" . ($page - 1) . "'>Previous</a> ";
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page) {
            echo "<strong>$i</strong> ";
        } else {
            echo "<a href='#' class='pagination-link' data-page='$i'>$i</a> ";
        }
    }
    
    // Next page link
    if ($page < $totalPages) {
        echo "<a href='#' class='pagination-link' data-page='" . ($page + 1) . "'>Next</a>";
    }
    
    echo "</td></tr>";
} else {
    echo "<tr>";
    echo "<td colspan=\"7\" style=\"text-align: center;\">NO ENTRY(s) FOUND</td>";
    echo "</tr>";
}
