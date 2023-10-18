jQuery(document).ready(function ($) {
    var widgets = {
        'binance-live-widget': $(".cmb2-id-binance-live-widget"),
        'accordion-block': $(".cmb2-id-accordion-block"),
        'price-block': $(".cmb2-id-price-block"),
        'price-card': $(".cmb2-id-price-card"),
        'slider-widget': $(".cmb2-id-slider-widget"),
        'chart': $(".cmb2-id-chart"),
        'calculator': $(".cmb2-id-calculator"),
        'rss-feed': $(".cmb2-id-rss-feed"),
        'technical-analysis': $(".cmb2-id-technical-analysis"),
        'coingecko-widget': $(".cmb2-id-coingecko-widget"),
    };

    function hideAllWidgets() {
        Object.values(widgets).forEach(function (widget) {
            widget.hide();
        });
    }

    function showSelectedWidget(widgetType) {
        hideAllWidgets();
        if (widgets[widgetType]) {
            widgets[widgetType].show();
        }
    }

    // Hide all widgets on page load
    hideAllWidgets();

    var binance_live_widget = $(".cmb2-id-type select#type").val();
    showSelectedWidget(binance_live_widget);

    $(".cmb2-id-type select#type").on('change', function () {
        var widgetType = $(this).val();
        showSelectedWidget(widgetType);
    });
    
    $("#show-coins option:last").prop("disabled", true);

       var currentPagePath = window.location.href;
            var segments = currentPagePath.split('/');
            var pageName = segments.pop();
          if(pageName="admin.php?page=ccpw_get_started"){
            $("#submit-cmb").hide();
          }
});



