$(() => {
    const savedSearch = localStorage.getItem('memberSearchParams');
    const savedPage = localStorage.getItem('memberCurrentPage') || 1;

    if (savedSearch) {
        $('#member_parameters').val(savedSearch);
        loadMembers(savedPage);
    }

    $("#searchMember").on("click", function (e) {
        e.preventDefault();
        console.log("clicked");
        loadMembers(savedPage);
    });

    $("#reset").on("click", function (e) {
        e.preventDefault();
        saveSearchState('',1);
        document.getElementById("member_parameters").value = '';
        loadMembers();
    });

    $("#filter_name").on("click", function (e) {
        e.preventDefault();
        document.getElementById("member_parameters").value += " username:";
        focusInput();
    });

    $("#filter_email").on("click", function (e) {
        e.preventDefault();
        document.getElementById("member_parameters").value += " email:";
        focusInput();
    });

    $("#filter_phone").on("click", function (e) {
        e.preventDefault();
        document.getElementById("member_parameters").value += " phone:";
        focusInput();
    });

    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadMembers(page);
    });

    $(document).on('click', '.clickable-row', function (e) {
        e.preventDefault();
        const id = $(this).data('userid');
        window.location.href = `/page/admin/member_detailed.php?id=${id}`;
    });

});

function onSubmit() {
    document.getElementById('form_account_register').submit();
}

function focusInput() {
    $("form :input:not(button):first").focus();
    $(".err:first").prev().focus();
    $(".err:first").prev().find(":input:first").focus();
}

function saveSearchState(params, page) {
    localStorage.setItem('memberSearchParams', params);
    localStorage.setItem('memberCurrentPage', page);
}

function loadMembers(page = 1) {
    const $searchInput = $('#member_parameters').val().trim();
    saveSearchState($searchInput, page);

    $.ajax({
        url: 'fetchMembers.php',
        method: 'POST',
        data: {
            search: $searchInput,
            page: page
        },
        success: function (response) {
            $('#memberSearchResults').html(response);
        },
    });
}

