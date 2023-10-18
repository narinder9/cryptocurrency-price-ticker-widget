jQuery(document).ready(function ($) {

    var url = window.location.href;
    if (url.indexOf('?page=openexchange-api-settings') > 0) {
        $('[href=\"admin.php?page=openexchange-api-settings\"]').parent('li').addClass('current');
    }
    var data = $('#adminmenu #toplevel_page_cool-crypto-plugins ul li a[href=\"admin.php?page=openexchange-api-settings\"]')
    data.each(function (e) {
        if ($(this).is(':empty')) {
            $(this).hide();
        }
    });
    $('#ccpw_dismiss_notice button.notice-dismiss').on('click', function (event) {
        var notice_data = $('#ccpw_dismiss_notice');
        $(notice_data).slideUp();

    });

});