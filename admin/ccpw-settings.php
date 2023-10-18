<?php


/**
 * Define the metabox and field configurations.
 */
function cmb2_ccpw_metaboxes() {
	// Start with an underscore to hide fields from custom fields list
	$prefix = 'ccpw_';

	$ccpw_nonce     = wp_create_nonce( 'ccpw-nonce' );
	$ajax_url       = admin_url( 'admin-ajax.php' );
	
	$currencies_arr = array(
		'USD' => 'USD',
		'GBP' => 'GBP',
		'EUR' => 'EUR',
		'INR' => 'INR',
		'JPY' => 'JPY',
		'CNY' => 'CNY',
		'ILS' => 'ILS',
		'KRW' => 'KRW',
		'RUB' => 'RUB',
		'DKK' => 'DKK',
		'PLN' => 'PLN',
		'AUD' => 'AUD',
		'BRL' => 'BRL',
		'MXN' => 'MXN',
		'SEK' => 'SEK',
		'CAD' => 'CAD',
		'HKD' => 'HKD',
		'MYR' => 'MYR',
		'SGD' => 'SGD',
		'CHF' => 'CHF',
		'HUF' => 'HUF',
		'NOK' => 'NOK',
		'THB' => 'THB',
		'CLP' => 'CLP',
		'IDR' => 'IDR',
		'NZD' => 'NZD',
		'TRY' => 'TRY',
		'PHP' => 'PHP',
		'TWD' => 'TWD',
		'CZK' => 'CZK',
		'PKR' => 'PKR',
		'ZAR' => 'ZAR',
	);
	/**
	 * Initiate the metabox
	 */

	$cmb2 = new_cmb2_box(
		array(
			'id'           => 'live_preview',
			'title'        => __( 'Crypto Widget Live Preview', 'cmb2' ),
			'object_types' => array( 'ccpw' ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
		)
	);
	$cmb = new_cmb2_box(
		array(
			'id'           => 'generate_shortcode',
			'title'        => __( 'Crypto Widget Settings', 'cmb2' ),
			'object_types' => array( 'ccpw' ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
		)
	);

	$cmb->add_field(
		array(
			'name'    => 'Widget Type<span style="color:red;">*</span>',
			'id'      => 'type',
			'type'    => 'select',
			'default' => 'table-widget',
			'options' => array(
				'table-widget'       => __( 'Advanced Table', 'cmb2' ),
				'list-widget'        => __( 'Simple List', 'cmb2' ),
				'ticker'             => __( 'Ticker / Marquee', 'cmb2' ),
				'multi-currency-tab' => __( 'Multi Currency Tabs', 'cmb2' ),
				'price-label'        => __( 'Price Label', 'cmb2' ),
				// widget pro widget list
				'accordion-block' => __('Accordion Block (Pro)', 'cmb2'),
				'price-block' => __('Price Block (Pro)', 'cmb2'),
				'price-card'   => __( 'Price Card (Pro) ', 'cmb2' ),
				'slider-widget' => __('Slider Widget (Pro)', 'cmb2'),
				'chart' => __('Chart (Pro)', 'cmb2'),
				'calculator' => __('Crypto Convertor (Pro)', 'cmb2'),
				'rss-feed' => __('News Feed (Pro)', 'cmb2'),
				'technical-analysis' => __('Technical Analysis (Pro)','cmb2'),
				'coingecko-widget' => __('CoinGecko Widget (Pro)','cmb2'), 
				'binance-live-widget' => __('Binance Live Widget (Pro)', 'cmb2'),    
			),
		)
	);

	$cmb->add_field(
		array(
			'name'    => 'Show Coins <span style="color:red;">*</span>',
			'id'      => 'show-coins',
			'default'          => '10',
			'type'    => 'select',
			'options' => array(
				'custom' => 'Custom List',
				10       => 'Top 10',
				50       => 'Top 50',
				100      => 'Top 100',
				200      => 'Top 200',
				250      => 'All (250)',
				2500     => 'Top 2500 (Pro)',
			),
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'price-label', 'list-widget', 'ticker', 'table-widget' ,'multi-currency-tab') ),
			),
		)
	);
	$cmb->add_field(
		array(
			'name'       => 'Select Coins<span style="color:red;">*</span>',
			'id'         => 'display_currencies',
			'desc'       => 'Select CryptoCurrencies (Press CTRL key to select multiple)',
			'type'       => 'pw_multiselect',
			'options'    => ccpw_get_all_coin_ids(),
			'attributes' => array(
				'required'               => true,
				'data-conditional-id'    => 'show-coins',
				'data-conditional-value' => json_encode( array( 'custom' ) ),
			),
		)
	);

	// select currency
	$cmb->add_field(
		array(
			'name'             => 'Select Fiat Currency',
			'desc'             => 'Show cryptocurrencies prices in selected fiat currency.'.select_fiat_currency(),
			'id'               => 'currency',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => $currencies_arr,		
			'default'          => 'USD',
			'attributes'       => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'price-label', 'list-widget', 'ticker', 'table-widget' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Records Per Page',
			'id'         => 'pagination_for_table',
			'type'       => 'select',
			'options'    => array(
				'10'  => '10',
				'25'  => '25',
				'50'  => '50',
				'100' => '100',
			),
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'table-widget' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Enable Formatting',
			'desc'       => 'Select if you want to display marketcap, volume and supply in <strong>(Million/Billion)</strong>',
			'id'         => 'enable_formatting',
			'type'       => 'checkbox',
			'default'    => ccpw_set_checkbox_default_for_new_post( true ),
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'table-widget' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => '24 Hours % Changes',
			'desc'       => 'Select to show <b>24-hour percentage price changes</b> for cryptocurrencies.',
			'default'    => ccpw_set_checkbox_default_for_new_post( true ),
			'id'         => 'display_changes',
			'type'       => 'checkbox',
			'attributes' => array(
				// 'required' => true,
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'price-label', 'list-widget', 'multi-currency-tab', 'ticker' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Where Do You Want to Display Ticker? (Optional)',
			'desc'       => '<br>Select the option where you want to display ticker.<span class="warning">Important: Do not add shortcode in a page if Header/Footer position is selected.</span>',
			'id'         => 'ticker_position',
			'type'       => 'radio_inline',
			'options'    => array(
				'header'    => __( 'Header', 'cmb2' ),
				'footer'    => __( 'Footer', 'cmb2' ),
				'shortcode' => __( 'Anywhere', 'cmb2' ),
			),
			'default'    => 'shortcode',

			'attributes' => array(
				// 'required' => true,
				'data-conditional-id'    => 'type',
				'data-conditional-value' => 'ticker',
			),

		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Ticker Position(Top)',
			'desc'       => 'Specify Top Margin (in px) - Only For Header Ticker',
			'id'         => 'header_ticker_position',
			'type'       => 'text',
			'default'    => '33',
			'attributes' => array(
				// 'required' => true,
				'data-conditional-id'    => 'type',
				'data-conditional-value' => 'ticker',
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Speed of Ticker',
			'desc'       => 'Low value = high speed. (Best between 10 - 60) e.g 10*1000 = 10000 miliseconds',
			'id'         => 'ticker_speed',
			'type'       => 'text',
			'default'    => '35',
			'attributes' => array(
				// 'required' => true,
				'data-conditional-id'    => 'type',
				'data-conditional-value' => 'ticker',
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Background Color',
			'desc'       => 'Select background color',
			'id'         => 'back_color',
			'type'       => 'colorpicker',
			'default'    => '#eee',
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'multi-currency-tab', 'list-widget', 'ticker' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Font Color',
			'desc'       => 'Select font color',
			'id'         => 'font_color',
			'type'       => 'colorpicker',
			'default'    => '#000',
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'multi-currency-tab', 'list-widget', 'ticker' ) ),
			),
		)
	);

	$cmb->add_field(
		array(
			'name' => 'Custom CSS',
			'desc' => 'Enter custom CSS',
			'id'   => 'custom_css',
			'type' => 'textarea',
			'attributes' => array(
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'price-label', 'list-widget', 'ticker', 'table-widget' ,'multi-currency-tab') ),
			),
		)
		
	);

	$cmb->add_field(
		array(
			'name'       => 'Show API Credits',
		     'desc'       => show_api_credits(),
			'id'         => 'ccpw_coinexchangeprice_credits',
			'default'    => ccpw_set_checkbox_default_for_new_post( false ),
			'type'       => 'checkbox',
			'attributes' => array(
				// 'required' => true,
				'data-conditional-id'    => 'type',
				'data-conditional-value' => json_encode( array( 'ticker', 'price-label', 'list-widget', 'multi-currency-tab', 'table-widget' ) ),
			),

		)
	);


	$cmb->add_field(array(
		'name'       => 'Accordion Block (Pro)',
		'id'         => 'accordion-block',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-price-accordion-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-price-accordion-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\accordion-block.png" style="max-width:100%;"></a>',
						));
    $cmb->add_field(array(
		'name'       => 'Price Block (Pro)',
		'id'         => 'price-block',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-price-block-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-price-block-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\price-block.png" style="max-width:100%;"></a>',
						));
	$cmb->add_field(array(
		'name'       => 'Price Card (Pro)',
		'id'         => 'price-card',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-price-card-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-price-card-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\price-card.png" style="max-width:100%;"></a>',
						));
	$cmb->add_field(array(
		'name'       => 'Slider Widget (Pro)',
		'id'         => 'slider-widget',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-price-slider-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-price-slider-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\slider-widget.png" style="max-width:100%;"></a>',
						));	
	$cmb->add_field(array(
		'name'       => 'Historical Price Chart (Pro)',
		'id'         => 'chart',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-price-chart-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-price-chart-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\chart.png" style="max-width:100%;"></a>',
						));
	$cmb->add_field(array(
		'name'       => 'Crypto Convertor (Pro)',
		'id'         => 'calculator',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-calculator-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-calculator-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\calculator.png" style="max-width:100%;"></a>',
						));
	$cmb->add_field(array(
		'name'       => 'News Feed (Pro)',
		'id'         => 'rss-feed',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'crypto-news-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'crypto-news-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\rss-feed.png" style="max-width:100%;"></a>',
						));	
	$cmb->add_field(array(
		'name'       => 'Technical Analysis (Pro)',
		'id'         => 'technical-analysis',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a><br/><hr>
		<img src="' . CCPWF_URL . 'assets\image\technical-analysis.png" style="max-width:100%;">',
						));	
	$cmb->add_field(array(
		'name'       => 'Coingecko Widget (Pro)',
		'id'         => 'coingecko-widget',
		'type'       => 'title',
		'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'coingecko-widgets/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'coingecko-widgets/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\coingecko-widget.png" style="max-width:100%;"></a>',
						));
	$cmb->add_field(array(
        'name'       => 'Binance Live Widget (Pro)',
        'id'         => 'binance_live_widget',
        'type'       => 'title',
        'desc'       => '<a class="button button-primary" target="_blank" href="' . CCPWF_PRO_URL . '&utm_content=add_new_widget">' . __('Buy Now', 'ccpwx') . '</a>
		<a class="button button-secondary" target="_blank" href="' . CCPWF_DEMO_URL . 'binance-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget">' . __('VIEW DEMO', 'ccpwx') . '</a><br/><hr>
		<a href="' . CCPWF_DEMO_URL . 'binance-widget/' . CCPWF_DEMO_UTM . '&utm_content=add_new_widget"><img src="' . CCPWF_URL . 'assets\image\binance-live-widget.png" style="max-width:100%;"></a>',
		   				));										

	$cmb2->add_field(
		array(
			'name' => '',
			'desc' => display_live_preview(),
			'type' => 'title',
			'id'   => 'live_preview',
		)
	);

	// Option page settings

	$cmb3 = new_cmb2_box(
		array(
			'id'           => 'api_settings_page',
			'title'        => esc_html__( 'Cryptocurrency Widgets - API Settings', 'ccpw' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'ccpw_options', // The option key and admin menu page slug.
			'icon_url'     => 'false', // Menu icon. Only applicable if 'parent_slug' is left empty.
			'menu_title'   => false, // Falls back to 'title' (above).
			'parent_slug'  => 'cool-crypto-plugins', // Make options page a submenu item of the themes menu.
			'tab_group'    => 'ccpw_Coin_Settings',
			'tab_title'    => 'Settings',
		)
	);

	$cmb3->add_field(
		array(
			'name'    => 'Select API',
			'id'      => 'select_api',
			'desc'    => 'Pick the source for fetching crypto price data using an API.',
			'type'    => 'select',
			'default' => 'coin_gecko',
			'options' => array(
				'coin_gecko'   => __( 'CoinGecko API', 'cmb2' ),
				'coin_paprika' => __( 'Coinpaprika API', 'cmb2' ),
			),
		)
	);
	$cmb3->add_field(
		array(
			'name'    => 'Select Cache Time',
			'id'      => 'select_cache_time',
			'desc'    => 'Trigger the API after that interval to load the most recent prices.',
			'type'    => 'select',
			'default' => '5 Minute',
			'options' => array(
				'5 Minute'  => __( '5 Minutes', 'cmb2' ),
				'10 Minute' => __( '10 Minutes', 'cmb2' ),
				'15 Minute' => __( '15 Minutes', 'cmb2' ),
			),
		)
	);

	$cmb3->add_field(
		array(
			'id'   => 'Delete Cache',
			'type' => 'title',
			'name' => 'Purge API Data Cache',
			'desc' => '
        <button class="button button-secondary" data-ccpw-nonce="' . esc_attr( $ccpw_nonce ) . '" data-ajax-url="' . esc_url( $ajax_url ) . '" id="ccpw_delete_cache">' . __( 'Purge Cache', 'ccpw' ) . '</button>',
		)
		);

	$cmb4 = new_cmb2_box(
		array(
			'id'           => 'settings_page_option',
			'title'        => esc_html__( 'Cryptocurrency Widgets - Get Started', 'ccpw' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'ccpw_get_started', 
			'menu_title'   => '', 
			'parent_slug'  => 'cool-crypto-plugins', 
			'tab_group'    => 'ccpw_Coin_Settings',
			'tab_title'    => 'Get Started ',
		)

	);

	$cmb4->add_field( array(
		'name'       => '<h2>Create a Cryptocurrency Price Widget</h2>',
		'id'         => 'get_started',
		'type'       => 'title',
		'desc'       => '<div class="ccpw-get-started">						
						<div class="ccpw-get-started-left">
						
						<p>Ready to make your own cryptocurrency price widget for your website using the <a href="https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=get-started" target="_blank">Cryptocurrency Widgets</a> plugin by <a href="https://coolplugins.net?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=coolplugins&utm_content=get-started" target="_blank">Cool Plugins</a>? This step-by-step guide will walk you through the process, making it easy to add a crypto widget to your site.</p>
						<ol>
						<li>After successfully installing and activating the plugin, locate the "<b><a href="' . esc_url(get_admin_url(null, "admin.php?page=cool-crypto-plugins")) . '">Crypto Plugins</a></b>" menu in your WordPress admin section.</li>
						<li>Now, navigate to <b>wp-admin >> Crypto Plugins >> Crypto Widgets >> <a href="' . esc_url(get_admin_url(null, "post-new.php?post_type=ccpw")) . '">↳ Add New Widget</b></a>.</li>
						<li>Here, you\'ll find various options to create a widget. Choose the widget type you want, such as ticker, list, table, or price label. Also, select the crypto coins you wish to display.</li>
						<li>Publish the widget. On this page, you\'ll see a shortcode inside the "<b>Crypto Widget Shortcode</b>" box. Copy this shortcode and paste it into any page or post to showcase the crypto widget on your website.</li>
						</ol>
						
						<h3>Key Notes:</h3>
						<ol>
						<li>If you want to display cryptocurrency prices in your local fiat currency other than USD - like EUR, GBP, AUD, INR, and more - remember to <a href="' . esc_url(get_admin_url(null, "admin.php?page=openexchange-api-settings")) . '">include the free API key from Openexchangerates.org</a>. This key helps convert the USD price of any cryptocurrency into your chosen fiat currencies. You can <a href="https://openexchangerates.org/signup/free" target="_blank">obtain the free API key from the Openexchangerates.org</a> website.</li>
						<li>This plugin allows you to create a price widget for the top 250 cryptocurrencies. It pulls data from CoinGecko & Coinpaprika APIs, giving you the option to choose a data source from <b>wp-admin >> Crypto Plugins >> Crypto Widgets >> <a href="' . esc_url(get_admin_url(null, "admin.php?page=ccpw_options")) . '">↳ Settings</a></b>. You can also set a cache interval time, which determines how often your website fetches the latest price data by hitting the API.</li>
						</ol>
						
						<h3>Crypto Widgets Demos:</h3>
						<p>Explore various demos of cryptocurrency widgets that you can integrate into your website using this plugin. These demos include Tickers, Price Lists, Price Labels, Tables, Price Blocks, Price Cards, Accordion Blocks, Binance Live Widgets, CoinGecko Widgets, Crypto Calculators, Multicurrency Widgets, Historical Charts, Price Sliders, and News Feed Widgets.</p>
						<a href="https://cryptocurrencyplugins.com/demo/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=demo&utm_content=get-started" class="button button-secondary" target="_blank">View All Demos</a> <a href="' . esc_url(get_admin_url(null, "post-new.php?post_type=ccpw")) . '" class="button button-primary">↳ Add New Widget</b></a>

						</div>
						<div class="ccpw-get-started-right">

						<h2>Compare Free v/s Pro</h2>
						<table class="ccpw-table">							
							<thead>
							<tr>
								<th class="ccpw-th">Features</th>
								<th class="ccpw-th">Free</th>
								<th class="ccpw-th">Pro</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>Number of Coins</td> <td>250</td> <td>2500</td>
							</tr>
							<tr>
								<td>Live Price Changes</td> <td>&#10060;</td> <td>&#9989;</td>
							</tr>
							<tr>
								<td>Historical Charts</td> <td>&#10060;</td> <td>&#9989;</td>
							</tr>
							<tr>
								<td>Crypto Calculator</td> <td>&#10060;</td> <td>&#9989;</td>
							</tr>
							<tr>
								<td>Moving Price Tickers</td> <td>&#10060;<br>(1 Style)</td> <td>&#9989;<br>(4 Style)</td>
							</tr>
							<tr>
								<td>Advanced Coins Table</td>  <td>&#9989;<br>(Supports 200 Coins)</td> <td>&#9989;<br>(Supports 2500 Coins)</td>
							</tr>
							<tr>
								<td>Advanced Widgets:
								<ol><li>Price Card & Block</li>
								<li>Accordion Block</li>
								<li>Slider Widget</li>
								<li>CoinGecko Widgets</li>
								<li>Binance Live Widget</li>
								<li>News Feed Widget</li>
								<li>more...</li></ol></td>
								<td>&#10060;</td>
								<td>&#9989;</td>
							</tr>
							<tr>
								<td>Premium Support</td> <td>&#10060;<br>WP Free Forum Support<br>(7 – 10 days)</td> <td>&#9989;<br>Quick Email Support<br>(24 – 48 Hours)</td>
								
							</tr>
							</tbody>
						</table>
						<br/>
						<a href="https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=get-started" class="button button-primary" target="_blank">Upgrade to Cryptocurrency Widgets Pro</a>
							
						</div>	
						</div>',
					));
}

function select_fiat_currency(){
	$api_option = get_option('openexchange-api-settings');

	if (empty($api_option)) {
		$message = '<br/>(<span style="color: red;">Remember to add <a href="admin.php?page=openexchange-api-settings" target="blank">
		Openexchangerates.org free API</a> key for crypto to fiat price conversions.</span>)';
		return $message;
	}
}
 function show_api_credits()
{	
		 $api = get_option('ccpw_options');
		 $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];
		 $data = ($api == 'coin_paprika')?'Coinpaprika':'CoinGecko';
	 	return 'Link back or a mention of ‘<strong>Powered by '.$data.'</strong>’ would be appreciated!';
	
}
	

	
function ccpw_custom_javascript_for_cmb2()
{
    wp_enqueue_script('jquery');
    $script = "
	<script>
	jQuery(document).ready(function($){
        var url = window.location.href;
		if (url.indexOf('?page=ccpw_get_started') > 0) {
       $('[href=\"admin.php?page=ccpw_options\"]').parent('li').addClass('current');
       
        }
         let cmc_data=$('#adminmenu #toplevel_page_cool-crypto-plugins ul li a[href=\"admin.php?page=ccpw_get_started\"]')
             
         cmc_data.each(function(e){
                    if($(this).is(':empty')){
                        $(this).hide();
                    }
                });
	});
	</script>
	";

    echo $script;
	
}
add_action('admin_head', 'ccpw_custom_javascript_for_cmb2', 100);




