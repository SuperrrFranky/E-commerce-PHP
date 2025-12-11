<?php
require '../../_base.php';

$_title = 'View Members';
$extraScripts = [
    '<link rel="stylesheet" href="/css/base.css">',
    '<script src="/js/admin.js"></script>'
];

include '../../_head.php'
?>

<?=html_button('back','back','back','btn','onclick=document.location="/page/adminPageSelector.php"') ?>
<div>
    <form action="javascript:void(0)">
        <?= html_text('member_parameters') ?>
        <button type="button" id="searchMember" class="btn">Search</button>
        <button type="reset" id="reset" class="btn">Reset</button>
        <button type="button" id="filter_name" class="btn">Filter Name</button>
        <button type="button" id="filter_email" class="btn">Filter E-mail</button>
        <button type="button" id="filter_phone" class="btn">Filter Phone</button>
    </form>
</div>
<div>
    <table class="table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody id="memberSearchResults">
            <tr>
                <td colspan="7" style="text-align: center;">
                    NO ENTRY(s) FOUND
                </td>
            </tr>
        <tbody>
    </table>
</div>
</body>

<?php
include '../../_foot.php';
