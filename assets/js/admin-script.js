jQuery(document).ready(function ($) {
    const $deleteCacheBtn = $("#ccpw_delete_cache");
    const ajaxUrl = $deleteCacheBtn.data('ajax-url');
    const nonce = $deleteCacheBtn.data('ccpw-nonce');

    $deleteCacheBtn.prop("disabled", false).on("click", function (e) {
        e.preventDefault();
        $(this).text('Purging...').prop("disabled", true);
        const requestData = {
            action: 'ccpw_delete_transient',
            nonce: nonce
        };
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: requestData,
            success: function (response) {
                if (response !== undefined && response.success == true) {
                    $deleteCacheBtn.text('Purged Cache').prop("disabled", true);
                }
            },
            error: function (error) {
                console.log(error);
            }
        });

    });

    //added current class in settings menu.
    const url = window.location.href;
    if (url.includes('?page=ccpw_options')) {
        $('[href="admin.php?page=ccpw_options"]').parent('li').addClass('current');
    }

    const cmcData = $('#adminmenu #toplevel_page_cool-crypto-plugins ul li a[href="admin.php?page=ccpw_options"]');

    cmcData.each(function () {
        if ($(this).is(':empty')) {
            $(this).hide();
        }
    });

});