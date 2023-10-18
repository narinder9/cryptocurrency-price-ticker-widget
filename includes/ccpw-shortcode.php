<?php

class CPTW_Shortcode
{

    public function __construct()
    {
        // main plugin shortcode for list widget
        add_shortcode('ccpw', array($this, 'ccpw_shortcode'));
    }

    public function get_cmc_single_page_slug()
    {

        // Initialize cmb2 for cmc links
        $cmc_slug = '';
        if (function_exists('cmc_extra_get_option')) {
            $cmc_slug = cmc_extra_get_option('single-page-slug');
            if (empty($cmc_slug)) {
                $cmc_slug = 'currencies';
            }
        } else {
            $cmc_slug = 'currencies';
        }
        return $cmc_slug;
    }
    /*
    |--------------------------------------------------------------------------
    | Crypto Widget Main Shortcode
    |--------------------------------------------------------------------------
     */
    public function ccpw_shortcode($atts, $content = null)
    {
        $atts = shortcode_atts(
            array(
                'id' => '',
                'class' => '',
            ),
            $atts,
            'ccpw'
        );

        $post_id = $atts['id'];
        wp_enqueue_style('ccpw-styles', CCPWF_URL . 'assets/css/ccpw-styles.css', array(), CCPWF_VERSION, 'all');

        /*
         *  Return if post status is anything other than 'publish'
         */
        if (get_post_status($post_id) != 'publish') {
            return;
        }
        $preview_notice = '';

        if (!function_exists('is_plugin_active')) {
            // require only if needed
            require ABSPATH . 'wp-admin/includes/plugin.php';
        }

        /*
        if (is_plugin_active( 'elementor/elementor.php' ) ) {
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
        $preview_notice =  '</div class="ccpw-preview-notice"><strong>'.__('Kindly update/publish the page and check the actual Widget on front-end.','ccpw').'</strong></div>';
        }
        } */

        // fetch data if required
        // $api_obj = new CCPW_api_data();
        // $api_obj->ccpw_get_coin_gecko_data();

            $api_obj = new CCPW_api_data();
            $api = get_option('ccpw_options');
            $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];


        // Grab the metadata from the database
        $type = get_post_meta($post_id, 'type', true);
        if ($type == 'table-widget') {
            // update old settings
            update_tbl_settings($post_id);
        }

        $display_currencies = array();

        $show_coins = get_post_meta($post_id, 'show-coins', true);
        $display_currencies = get_post_meta($post_id, 'display_currencies', true);  



        $getData = (!empty($show_coins)) ? $show_coins : 'custom';

        $currency = get_post_meta($post_id, 'currency', true);
        $enable_formatting = get_post_meta($post_id, 'enable_formatting', true);
        $show_credit = get_post_meta($post_id, 'ccpw_coinexchangeprice_credits', true);
        $api_by = ($api == 'coin_paprika')?'Coinpaprika':'CoinGecko';
        $credit_html = '<div class="ccpw-credits"><a href="https://www.'.$api_by.'.com/?utm_source=crypto-widgets-plugin&utm_medium=api-credits" target="_blank" rel="nofollow">' . __('Powered by '.$api_by.' API', 'ccpw') . '</a></div>';
        $fiat_currency = $currency ? $currency : 'USD';
        $ticker_position = get_post_meta($post_id, 'ticker_position', true);
        $header_ticker_position = get_post_meta($post_id, 'header_ticker_position', true);
        $ticker_speed = (int) get_post_meta($post_id, 'ticker_speed', true);
        $t_speed = $ticker_speed * 1000;

        $cmc_slug = $this->get_cmc_single_page_slug();

        $output = '';
        $cls = '';
        $crypto_html = '';
        $display_changes = get_post_meta($post_id, 'display_changes', true);
        $back_color = get_post_meta($post_id, 'back_color', true);
        $font_color = get_post_meta($post_id, 'font_color', true);
        $custom_css = get_post_meta($post_id, 'custom_css', true);
        $id = 'ccpw-ticker' . $post_id . rand(1, 20);
        $is_cmc_enabled = get_option('cmc-dynamic-links');

        $this->ccpw_enqueue_assets($type, $post_id);

        /* dynamic styles */
        $dynamic_styles = '';
        $styles = '';
        $dynamic_styles_list = '';
        $dynamic_styles_multicurrency = '';
        $bg_color = !empty($back_color) ? 'background-color:' . $back_color . ';' : 'background-color:#fff;';
        $bg_coloronly = !empty($back_color) ? ':' . $back_color . 'd9;' : ':#ddd;';
        $fnt_color = !empty($font_color) ? 'color:' . $font_color . ';' : 'color:#000;';
        $fnt_coloronly = !empty($font_color) ? ':' . $font_color . '57;' : ':#666;';
        $fnt_colorlight = !empty($font_color) ? ':' . $font_color . '1F;' : ':#eee;';
        $ticker_top = !empty($header_ticker_position) ? 'top:' . $header_ticker_position . 'px !important;' : 'top:0px !important;';

        $usd_conversions = array();

        if ($type != 'table-widget') {
            if (!empty($getData) && is_numeric($getData)) {
                // fetch data from db
                $all_coin_data = ccpw_get_top_coins_data($getData);
            } else {
                // fetch data from db
                if ($api == "coin_paprika") {
                   
                    if (is_array($display_currencies) && count($display_currencies) > 0) {
                    foreach ($display_currencies as $key => $value) {
                        $display_currencies[] = ccpw_coin_array($value, true);
                    }
                    }
                }


                if (is_array($display_currencies) && count($display_currencies) > 0) {
                    $all_coin_data = ccpw_get_coins_data($display_currencies);
                    
                } else {
                    return $error = __('You have not selected any currencies to display', 'ccpw');
                }
            }
            // create coin id based index for later use
            if (is_array($all_coin_data) && count($all_coin_data) > 0) {
                $selected_coins = array();
                $usd_conversions = (array) $api_obj->ccpw_usd_conversions('all');
                foreach ($all_coin_data as $currency) {
                    // gather data from database
                    if ($currency != false) {
                        $coin_id = $currency['coin_id'];
                        $selected_coins[$coin_id] = $currency;

                        // generate html according to the coin selection
                        if (isset($currency['coin_id']) && is_array($currency)) {
                            $coin = $currency;

                            require CCPWF_DIR . '/includes/ccpw-generate-html.php';
                            $crypto_html .= $coin_html;
                        }
                    }
                }
            } else {
                $error = __('You have not selected any currencies to display', 'ccpw');
                return $error . '<!-- Cryptocurrency Widget ID: ' . esc_attr($post_id) . ' !-->';
            }
        }

        switch ($type) {
            case 'ticker':
                $id = 'ccpw-ticker-widget-' . esc_attr($post_id);
                $dynamictikr_styles = '<style>.tickercontainer #' . $id . '{' . $bg_color . '}
            .tickercontainer #' . $id . ' span.name,
            .tickercontainer #' . $id . ' .ccpw-credits a {' . $fnt_color . '}
            .tickercontainer #' . $id . ' span.coin_symbol {' . $fnt_color . '}
            .tickercontainer #' . $id . ' span.price {' . $fnt_color . '} .tickercontainer .price-value{' . $fnt_color . '}
            .ccpw-header-ticker-fixedbar{' . $ticker_top . '}</style>';

                if ($ticker_position == 'footer' || $ticker_position == 'header') {
                    $cls = 'ccpw-sticky-ticker';
                    if ($ticker_position == 'footer') {
                        $container_cls = 'ccpw-footer-ticker-fixedbar';
                    } else {
                        $container_cls = 'ccpw-header-ticker-fixedbar';
                    }
                } else {
                    $cls = 'ccpw-ticker-cont';
                    $container_cls = '';
                }

                $output .= '<div style="display:none" class="ccpw-container ccpw-ticker-cont ' . esc_attr($container_cls) . '">';
                $output .= '<div  class="tickercontainer" style="height: auto; overflow: hidden;">';
                $output .= '<ul   data-tickerspeed="' . esc_attr($t_speed) . '" id="' . esc_attr($id) . '">';
                $output .= $crypto_html;
                if ($show_credit) {
                    $output .= '<li ="ccpw-ticker-credit">' . $credit_html . '</li>';
                }
                $output .= '</ul></div></div>';
                $output .= $dynamictikr_styles;
                break;
            case 'price-label':
                $id = 'ccpw-label-widget-' . esc_attr($post_id);
                $dynamic_styles .= '#' . $id . '.ccpw-price-label li a , #' . $id . '.ccpw-price-label li{' . $fnt_color . '}';
                $id = 'ccpw-label-widget-' . esc_attr($post_id);
                $output .= '<div id="' . esc_attr($id) . '" class="ccpw-container ccpw-price-label"><ul class="lbl-wrapper">';
                $output .= $crypto_html;
                $output .= '</ul></div>';
                if ($show_credit) {
                    $output .= $credit_html;
                }
                break;
            case 'list-widget';
                $id = 'ccpw-list-widget-' . esc_attr($post_id);
                $dynamic_styles .= '
            #' . $id . '.ccpw-widget .ccpw_table tr{' . $bg_color . $fnt_color . '}
            #' . $id . '.ccpw-widget .ccpw_table tr th, #' . $id . '.ccpw-widget .ccpw_table tr td,
            #' . $id . '.ccpw-widget .ccpw_table tr td a{' . $fnt_color . '}';

                $cls = 'ccpw-widget';
                $output .= '<div id="' . esc_attr($id) . '" class="' . esc_attr($cls) . '"><table class="ccpw_table" style="border:none!important;"><thead>
            <th>' . __('Name', 'ccpw') . '</th>
            <th>' . __('Price', 'ccpw') . '</th>';
                if ($display_changes) {
                    $output .= '<th>' . __('24H (%)', 'ccpw') . '</th>';
                }
                $output .= '</thead><tbody>';
                $output .= $crypto_html;
                $output .= '</tbody></table></div>';

                if ($show_credit) {
                    $output .= $credit_html;
                }
                break;
            case 'multi-currency-tab';
                $id = 'ccpw-multicurrency-widget-' . esc_attr($post_id);
                $dynamic_styles .= '.currency_tabs#' . $id . ',.currency_tabs#' . $id . ' ul.multi-currency-tab li.active-tab{' . $bg_color . '}
            .currency_tabs#' . $id . ' .mtab-content, .currency_tabs#' . $id . ' ul.multi-currency-tab li, .currency_tabs#' . $id . ' .mtab-content a{' . $fnt_color . '}';
                $usd_conversions = (array) $api_obj->ccpw_usd_conversions('all');

                $output .= '<div class="currency_tabs" id="' . esc_attr($id) . '">
                <ul class="multi-currency-tab">
                    <li data-currency="usd" class="active-tab">' . __('USD', 'ccpwx') . '</li>
                    <li data-currency="eur">' . __('EUR', 'ccpwx') . '</li>
                    <li data-currency="gbp">' . __('GPB', 'ccpwx') . '</li>
                    <li data-currency="aud">' . __('AUD', 'ccpwx') . '</li>
                    <li data-currency="jpy">' . __('JPY', 'ccpwx') . '</li>
                 </ul>';
                $output .= '<div><ul class="multi-currency-tab-content">';
                $output .= $crypto_html;
                $output .= '</ul></div>
            </div>';
                if ($show_credit) {
                    $output .= $credit_html;
                }
                break;
            case 'table-widget';
                $cls = 'ccpw-coinslist_wrapper';
                $preloader_url = CCPWF_URL . 'assets/chart-loading.svg';
                $ccpw_prev_coins = __('Previous', 'ccpw');
                $ccpw_next_coins = __('Next', 'ccpw');
                $coin_loading_lbl = __('Loading...', 'ccpw');
                $ccpw_no_data = __('No Coin Found', 'ccpw');
                $getRecords = '';
                $id = 'ccpw-coinslist_wrapper';
                $datatable_pagination = get_post_meta($post_id, 'pagination_for_table', true);
                $old_settings = get_post_meta($post_id, 'display_currencies_for_table', true);
                $r_type = 'top';
                $c_id_arr = array();
                // new settings top values
                if (!empty($getData) && is_numeric($getData)) {
                    // fetch data from db
                    $getRecords = $getData;
                    $r_type = 'top';
                } elseif ($getData == 'custom') {
                         if ($api == "coin_paprika") {
                            if (is_array($display_currencies) && count($display_currencies) > 0) {
                    foreach ($display_currencies as $key => $value) {
                        $display_currencies[] = ccpw_coin_array($value, true);
                    }
                }
                }
                    if (is_array($display_currencies) && count($display_currencies) > 0) {
                        $getRecords = count($display_currencies);
                        $c_id_arr = $display_currencies;
                    } else {
                        return $error = __('You have not selected any currencies to display', 'ccpw');
                    }
                    $r_type = 'custom';
                } else {
                    $getRecords = 10;
                    $r_type = 'top';
                }

                if ($getRecords > $datatable_pagination) {
                    $limit = $datatable_pagination;
                } else {
                    $limit = $getRecords;
                }

                $coins_list = json_encode($c_id_arr);
                $output .= '<div id="' . esc_attr($id) . '" class="' . esc_attr($cls) . '">

            <table id="ccpw-datatable-' . esc_attr($post_id) . '"
            data-rtype="' . esc_attr($r_type) . '"
            data-coin-list="' . esc_attr($coins_list) . '"
            data-currency-type="' . esc_attr($fiat_currency) . '"
            data-next-coins="' . esc_attr($ccpw_next_coins) . '"
            data-loadinglbl="' . esc_attr($coin_loading_lbl) . '"
            data-prev-coins="' . esc_attr($ccpw_prev_coins) . '"
            data-dynamic-link="' . esc_attr($is_cmc_enabled) . '"
            data-currency-slug="' . esc_url(home_url($cmc_slug)) . '"
            data-required-currencies="' . esc_attr($getRecords) . '"
            data-zero-records="' . esc_attr($ccpw_no_data) . '"
            data-pagination="' . esc_attr($limit) . '"
            data-number-formating="' . esc_attr($enable_formatting) . '"
            data-currency-symbol="' . ccpw_currency_symbol($fiat_currency) . '"
            data-currency-rate="' . $api_obj->ccpw_usd_conversions($fiat_currency) . '"
            class="display ccpw_table_widget table-striped table-bordered no-footer"
            style="border:none!important;">

            <thead data-preloader="' . esc_url($preloader_url) . '">
            <th data-classes="desktop ccpw_coin_rank" data-index="rank">' . __('#', 'ccpw') . '</th>
            <th data-classes="desktop ccpw_name" data-index="name">' . __('Name', 'ccpw') . '</th>
            <th data-classes="desktop ccpw_coin_price" data-index="price">' . __('Price', 'ccpw') . '</th>
            <th data-classes="desktop ccpw_coin_change24h" data-index="change_percentage_24h">' . __('Changes 24h', 'ccpw') . '</th>
            <th data-classes="desktop ccpw_coin_market_cap" data-index="market_cap">' . __('Market CAP', 'ccpw') . '</th>';
            if ($api === "coin_gecko") {
                
                $output .= '<th data-classes="ccew_coin_total_volume" data-index="total_volume">' . esc_html__('Volume', 'ccpw') . '</th>';
            }
                $output .= '<th data-classes="ccpw_coin_supply" data-index="supply">' . __('Supply', 'ccpw') . '</th>';

                $output .= '</tr></thead><tbody>';
                $output .= '</tbody><tfoot>
                    </tfoot></table>';

                if ($show_credit) {
                    $output .= $credit_html;
                }

                $output .= '</div>';
                break;
        }

        $ccpwcss = $dynamic_styles . $custom_css;

        wp_add_inline_style('ccpw-styles', $ccpwcss);

        $ccpwv = '<!-- Cryptocurrency Widgets - Version:- ' . CCPWF_VERSION . ' By Cool Plugins (CoolPlugins.net) -->';
        return $ccpwv . $output . $preview_notice;
    }

    /*
    |--------------------------------------------------------------------------
    | loading required assets according to the widget type
    |--------------------------------------------------------------------------
     */
    public function ccpw_enqueue_assets($type, $post_id)
    {
        if (is_admin() && ccpw_get_post_type_page() != 'ccpw') {
            return;
        }

        if (!wp_script_is('jquery', 'done')) {
            wp_enqueue_script('jquery');
        }
        wp_enqueue_style('ccpw-bootstrap', CCPWF_URL . 'assets/css/bootstrap.min.css', array(), CCPWF_VERSION, 'all');
        wp_enqueue_style('ccpw-custom-icons', CCPWF_URL . 'assets/css/ccpw-icons.css', array(), CCPWF_VERSION, 'all');
        // ccpw main styles file
        wp_enqueue_style('ccpw-styles', CCPWF_URL . 'assets/css/ccpw-styles.css', array(), CCPWF_VERSION, 'all');

        // loading Scripts for ticker widget
        switch ($type) {
            case 'ticker':
                $ticker_id = 'ccpw-ticker-widget-' . esc_attr($post_id);
                wp_enqueue_script('ccpw_bxslider_js', CCPWF_URL . 'assets/js/ccpw-bxslider.js', array('jquery'), CCPWF_VERSION, true);
                wp_add_inline_script(
                    'ccpw_bxslider_js',
                    'jQuery(document).ready(function($){
				$(".ccpw-ticker-cont #' . $ticker_id . '").each(function(index){
					var tickerCon=$(this);
					var ispeed=Number(tickerCon.attr("data-tickerspeed"));
					$(this).bxSlider({
						ticker:true,
						minSlides:1,
						maxSlides:12,
						slideWidth:"auto",
						tickerHover:true,
						wrapperClass:"tickercontainer",
						speed: ispeed+ispeed,
						infiniteLoop:true
					});
				});
			});'
                );

                break;

            case 'multi-currency-tab':
                wp_enqueue_script('ccpw_script', CCPWF_URL . 'assets/js/ccpw-script.js', array('jquery'), CCPWF_VERSION, true);
                break;

            case 'table-widget':
                // loads advance table scripts and styles
                wp_enqueue_script('ccpw-datatable', CCPWF_URL . 'assets/js/jquery.dataTables.min.js', array('jquery'), CCPWF_VERSION, true);
                wp_enqueue_script('ccpw-headFixer', CCPWF_URL . 'assets/js/tableHeadFixer.js', array('jquery'), CCPWF_VERSION, true);
                wp_enqueue_style('ccpw-custom-datatable-style', CCPWF_URL . 'assets/css/ccpw-custom-datatable.css', array(), CCPWF_VERSION, 'all');
                wp_enqueue_script('ccpw-table-script', CCPWF_URL . 'assets/js/ccpw-table-widget.js', array('jquery'), CCPWF_VERSION, true);
                wp_localize_script(
                    'ccpw-table-script',
                    'ccpw_js_objects',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'wp_nonce' => wp_create_nonce('ccpwf-tbl-widget'),
                    )
                );
                wp_enqueue_script('ccpw-numeral', CCPWF_URL . 'assets/js/numeral.min.js', array('jquery', 'ccpw-table-script'), CCPWF_VERSION, true);
                wp_enqueue_script('ccpw-table-sort', CCPWF_URL . 'assets/js/tablesort.min.js', array('jquery', 'ccpw-table-script'), CCPWF_VERSION, true);

                break;
        }
    }
}
