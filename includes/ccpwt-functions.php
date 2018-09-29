<?php

	// creating coins array for settings
function ccpwt_coin_arr($limit = 200, $fiat_curr = "USD", $type = "all")
{
  $c_list = array();
  $coins_data = array();

  $all_coins =get_api_data($limit, $fiat_curr);
  if (!empty($all_coins) && is_array($all_coins)) {
    foreach ($all_coins as $coin) {
      $c_list[$coin->id] = $coin->name;
      $coins_data[$coin->id] = $coin;
    }
    if ($type == "all") {
      return $coins_data;
    } else {
      return $c_list;
    }

  }
}

/**
 * Fetching Coins data from coin market cap and creating cache for 1 hours
 */

function get_api_data($limit, $old_currency)
{
		  //check if cmc cache not exists
  if (false === ($value = get_transient('ccpw-coins-' . strtolower($old_currency)))) {
    $request = wp_remote_get('https://api.coinmarketcap.com/v1/ticker/?limit=' . $limit . '&convert=' . $old_currency);
    if (is_wp_error($request)) {
      return false;
    }
    $body = wp_remote_retrieve_body($request);
    $coinslist = json_decode($body);
    if (!empty($coinslist)) {
      set_transient('ccpw-coins-' . strtolower($old_currency), $coinslist, 10 * MINUTE_IN_SECONDS);
    }
    return $coinslist;
  } else {
			// if cmc cache exists use just use it.
    return $coinslist = get_transient('ccpw-coins-' . strtolower($old_currency));
  }

}	


/*
 Adding coins SVG logos
*/

function ccpw_get_coin_logo($coin_id,$size){
  $logo_html='';
    $coin_svg=Crypto_Currency_Price_Widget_PATH.'/assets/coin-logo/'.strtolower($coin_id).'.svg';

    if (file_exists($coin_svg)) {
      $coin_svg=Crypto_Currency_Price_Widget_URL.'/assets/coin-logo/'.strtolower($coin_id).'.svg';
       $logo_html='<img id="'.$coin_id.'" alt="'.$coin_id.'" src="'.$coin_svg.'">';
        }
    return $logo_html;
}


/* USD conversions */

  function ccpwt_usd_conversions($currency){
      $conversions= get_transient('ccpwt_usd_conversions');
    if( empty($conversions) || $conversions==="" ) {
          $request = wp_remote_get( 'https://us-central1-usd-conversion-data.cloudfunctions.net/conversions/');
        if( is_wp_error( $request ) ) {
          return false;
        }
        $currency_ids = array("EUR","GBP","AUD","JPY");

        $body = wp_remote_retrieve_body( $request );
        $conversion_data= json_decode( $body );
        $conversion_data=(array) $conversion_data;
        if(is_array($conversion_data) && count($conversion_data)>0) {
          foreach($conversion_data as $key=> $currency_price){
              if(in_array($key,$currency_ids)){
                $conversions[$key]=$currency_price;
              }     
            
          }
      
        uksort($conversions, function($key1, $key2) use ($currency_ids) {
            return (array_search($key1, $currency_ids) > array_search($key2, $currency_ids));
        });
      
      set_transient('ccpwt_usd_conversions',$conversions, 12* HOUR_IN_SECONDS);
        }
      }

      if($currency=="all"){
        
        return $conversions;

      }else{
        if(isset($conversions[$currency])){
          return $conversions[$currency];
        }
      }
  }

  function ccpwt_format_number($n){

  if($n < 0.50){
  return  $formatted = number_format($n, 6, '.', ',');
  }
  else{
  return  $formatted = number_format($n, 2, '.', ',');
    }
  }

  // object to array conversion 
  function ccpwt_objectToArray($d)
  {
    if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
      $d = get_object_vars($d);
    }

    if (is_array($d)) {
            /*
       * Return array converted to object
       * Using __FUNCTION__ (Magic constant)
       * for recursive call
       */
      return array_map(__FUNCTION__, $d);
    } else {
            // Return array
      return $d;
    }
  }
	/*
	Added meta boxes for shortcode
	*/

	function register_ccpw_meta_box()
	{
	    add_meta_box( 'ccpw-shortcode', 'Crypto Currency Price Shortcode','p_shortcode_meta', 'ccpw', 'side', 'high' );
	}

  /*
    Plugin Shortcode meta section 
  */
	function p_shortcode_meta()
		{ 
	    $id = get_the_ID();
	    $dynamic_attr='';
	    _e(' <p>Paste this shortcode in anywhere (page/post)</p>','ccpw'); 

	   $element_type = get_post_meta( $id, 'pp_type', true );
	   $dynamic_attr.="[ccpw id=\"{$id}\"";
	   $dynamic_attr.=']';
	    ?>
	    <input type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr) ;?>" readonly/>
      <div>
        <br/>
	   	<a class="button button-secondary red" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844">How to create CoinMarketCap.com clone?</a>
     </div>
	    <?php 
	}


	function ccpw_add_meta_boxes( $post){
		 add_meta_box(
                'ccpw-feedback-section',
                __( 'Hopefully you are happy with our COOL crypto widget plugin','ccpw'),
                'ccpw_right_section',
                'ccpw',
                'side',
                'low'
            );
	}

  /*
    Admin notice for plugin feedback
  */
	function ccpw_right_section($post, $callback){
        global $post;
        $pro_add=''; 
        $pro_add .=
        __('May I ask you to give it a 5-star rating on WordPress.org. This will help to spread its popularity and to make this plugin a better one.  ','ccpw').
        '<br/><br/><a href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post" class="button button-primary" target="_blank">Submit Review ★★★★★</a>
        <hr>
         <div>
        <h3>Crypto Widgets Pro Features:-</h3>
      <ol style="list-style:disc;"><li> You can display real time live price changes. - <a href="http://cryptowidgetpro.coolplugins.net/list-widget/#live-changes-demo" target="_blank">DEMO</a></li> 
		<li>  Create widgets for 1500+ crypto coins in pro version while free version only supports top 50 crypto coins.</li> 
		<li>  Create historical price charts & tradingview candlestick charts. - <a href="http://cryptowidgetpro.coolplugins.net/coin-price-chart/" target="_blank">DEMO</a></li> 
		<li>  You can create beautiful price label and crypto price card designs.</li> 
    <li>  Display latest crypto news feed from popular websites. - <a href="http://cryptowidgetpro.coolplugins.net/news-feed/" target="_blank">DEMO</a></li> 
		<li>  Display market cap and volume of virtual crypto coins.</li> 
		<li>  32+ fiat currencies support - USD, GBP, EUR, INR, JPY, CNY, ILS, KRW, RUB, DKK, PLN, AUD, BRL, MXN, SEK, CAD, HKD, MYR, SGD, CHF, HUF, NOK, THB, CLP, IDR, NZD, TRY, PHP, TWD, CZK, PKR, ZAR.</li> 
		<li><a target="_blank" href="http://cryptowidgetpro.coolplugins.net/">'.__('VIEW ALL DEMOS','ccpw').'</a></li>

		</ol>
		<a class="button button-secondary" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050s">'.__('Buy Now','ccpw').' ($29)</a>
		</div>';
        echo $pro_add ;

    }


  // currencies symbol
  function ccpwt_currency_symbol($name)
  {
    $cc = strtoupper($name);
    $currency = array(
      "USD" => "&#36;", //U.S. Dollar
	  "CLP" => "&#36;", //CLP Dollar
	  "SGD" => "S&#36;", //Singapur dollar
      "AUD" => "&#36;", //Australian Dollar
      "BRL" => "R&#36;", //Brazilian Real
      "CAD" => "C&#36;", //Canadian Dollar
      "CZK" => "K&#269;", //Czech Koruna
      "DKK" => "kr", //Danish Krone
      "EUR" => "&euro;", //Euro
      "HKD" => "&#36", //Hong Kong Dollar
      "HUF" => "Ft", //Hungarian Forint
      "ILS" => "&#x20aa;", //Israeli New Sheqel
      "INR" => "&#8377;", //Indian Rupee
	  "IDR" => "Rp", //Indian Rupee
	  "KRW" => "&#8361;", //WON
	  "CNY" => "&#165;", //CNY
      "JPY" => "&yen;", //Japanese Yen 
      "MYR" => "RM", //Malaysian Ringgit 
      "MXN" => "&#36;", //Mexican Peso
      "NOK" => "kr", //Norwegian Krone
      "NZD" => "&#36;", //New Zealand Dollar
      "PHP" => "&#x20b1;", //Philippine Peso
      "PLN" => "&#122;&#322;",//Polish Zloty
      "GBP" => "&pound;", //Pound Sterling
      "SEK" => "kr", //Swedish Krona
      "CHF" => "Fr", //Swiss Franc
      "TWD" => "NT&#36;", //Taiwan New Dollar 
	  "PKR" => "Rs", //Rs 
      "THB" => "&#3647;", //Thai Baht
      "TRY" => "&#8378;", //Turkish Lira
	  "ZAR" => "R", //zar
	  "RUB" => "&#8381;" //rub
    );

    if (array_key_exists($cc, $currency)) {
      return $currency[$cc];
    }
  }


  	// Register Custom Post Type of Crypto Widget
  function ccpw_post_type()
  {

    $labels = array(
      'name' => _x('CryptoCurrency Price Widget', 'Post Type General Name', 'ccpw'),
      'singular_name' => _x('CryptoCurrency Price Widget', 'Post Type Singular Name', 'ccpw'),
      'menu_name' => __('Crypto Widgets', 'ccpw'),
      'name_admin_bar' => __('Post Type', 'ccpw'),
      'archives' => __('Item Archives', 'ccpw'),
      'attributes' => __('Item Attributes', 'ccpw'),
      'parent_item_colon' => __('Parent Item:', 'ccpw'),
      'all_items' => __('All Shortcodes', 'ccpw'),
      'add_new_item' => __('Add New Shortcode', 'ccpw'),
      'add_new' => __('Add New', 'ccpw'),
      'new_item' => __('New Item', 'ccpw'),
      'edit_item' => __('Edit Item', 'ccpw'),
      'update_item' => __('Update Item', 'ccpw'),
      'view_item' => __('View Item', 'ccpw'),
      'view_items' => __('View Items', 'ccpw'),
      'search_items' => __('Search Item', 'ccpw'),
      'not_found' => __('Not found', 'ccpw'),
      'not_found_in_trash' => __('Not found in Trash', 'ccpw'),
      'featured_image' => __('Featured Image', 'ccpw'),
      'set_featured_image' => __('Set featured image', 'ccpw'),
      'remove_featured_image' => __('Remove featured image', 'ccpw'),
      'use_featured_image' => __('Use as featured image', 'ccpw'),
      'insert_into_item' => __('Insert into item', 'ccpw'),
      'uploaded_to_this_item' => __('Uploaded to this item', 'ccpw'),
      'items_list' => __('Items list', 'ccpw'),
      'items_list_navigation' => __('Items list navigation', 'ccpw'),
      'filter_items_list' => __('Filter items list', 'ccpw'),
    );
    $args = array(
      'label' => __('CryptoCurrency Price Widget', 'ccpw'),
      'description' => __('Post Type Description', 'ccpw'),
      'labels' => $labels,
      'supports' => array('title'),
      'taxonomies' => array(''),
      'hierarchical' => false,
      'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
      'show_ui' => true,
      'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
      'menu_position' => 5,
      'show_in_admin_bar' => true,
      'show_in_nav_menus' => true,
      'can_export' => true,
      'has_archive' => false,  // it shouldn't have archive page
      'rewrite' => false,  // it shouldn't have rewrite rules
      'exclude_from_search' => true,
      'publicly_queryable' => true,
      'menu_icon' => Crypto_Currency_Price_Widget_URL.'/assets/ccpw-icon.png',
      'capability_type' => 'page',
    );
    register_post_type('ccpw', $args);

  }

  /**
   * Define the metabox and field configurations.
   */
  function cmb2_ccpw_metaboxes()
  {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ccpw_';
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
    $cmb = new_cmb2_box(array(
      'id' => 'generate_shortcode',
      'title' => __('Settings', 'cmb2'),
      'object_types' => array('ccpw'), // Post type
      'context' => 'normal',
      'priority' => 'high',
      'show_names' => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
    ));
    $cmb->add_field(array(
      'name' => 'Type<span style="color:red;">*</span>',
      'id' => 'type',
      'type' => 'radio_inline',
      'options' => array(
        'ticker' => __('Ticker', 'cmb2'),
        'list-widget' => __('List Widget', 'cmb2'),
        'multi-currency-tab' => __('Multi Currency Tabs', 'cmb2'),
        'price-label' => __('Price Label', 'cmb2'),
      ),
      'default' => 'ticker',
    ));


    $cmb->add_field(array(
      'name' => 'Display CryptoCurrencies<span style="color:red;">*</span>',
      'id' => 'display_currencies',
      'desc' => 'Select CryptoCurrencies (Press CTRL key to select multiple)',
      'type' => 'pw_multiselect',
      'options' => array(
        'bitcoin' => 'Bitcoin',
        'ethereum' => 'Ethereum',
        'bitcoin-cash' => 'Bitcoin Cash',
        'ripple' => 'Ripple',
        'litecoin' => 'Litecoin',
        'cardano' => 'Cardano',
        'iota' => 'IOTA',
        'dash' => 'Dash',
        'nem' => 'NEM',
        'monero' => 'Monero',
        'stellar' => 'Stellar',
        'eos' => 'EOS',
        'ethereum-classic' => 'Ethereum Classic',
        'lisk' => 'Lisk',
        'tron' => 'Tron',
        'neo' => 'NEO',
        'vechain' => 'VeChain',
        'qtum' => 'Qtum',
        'bitcoin-gold' => 'Bitcoin Gold',
        'tether' => 'Tether',
        'omisego' => 'OmiseGO',
        'icon' => 'ICON',
        'binance-coin' => 'Binance Coin',
        'nano' => 'Nano',
        'verge' => 'Verge',
        'bytecoin-bcn' => 'Bytecoin',
        'zcash' => 'Zcash',
        'ontology' => 'Ontology',
        'aeternity' => 'Aeternity',
        'steem' => 'Steem',
        'wanchain' => 'Wanchain',
        'siacoin' => 'Siacoin',
        'bitshares' => 'BitShares',
        'bytom' => 'Bytom',
        'zilliqa' => 'Zilliqa',
        'populous' => 'Populous',
        'bitcoin-diamond' => 'Bitcoin Diamond',
        'bitcoin-private' => 'Bitcoin Private',
        '0x' => '0x',
        'stratis' => 'Stratis',
        'waves' => 'Waves',
        'rchain' => 'RChain',
        'golem-network-tokens' => 'Golem',
        'maker' => 'Maker',
        'dogecoin' => 'Dogecoin',
        'hshare' => 'Hshare',
        'status' => 'Status',
        'digixdao' => 'DigixDAO',
        'loopring' => 'Loopring',
        'iostoken' => 'IOStoken',
        'aion' => 'Aion',
        'waltonchain' => 'Waltonchain',

      ),
      'attributes' => array(
          'required' => true
      )
    ));

   //select currency
    $cmb->add_field(array(
      'name' => 'Select Currency',
      'desc' => '',
      'id' => 'currency',
      'type' => 'select',
      'show_option_none' => false,
      'default' => 'custom',
      'options' => $currencies_arr,
      'default' => 'USD',
      'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('price-label', 'list-widget', 'ticker')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Display Logos? (Optional)',
      'desc' => 'Select if you want to display Currency logos',
      'id' => 'display_logo',
      'type' => 'checkbox',
    ));
    $cmb->add_field(array(
      'name' => 'Display 24 Hours changes? (Optional)',
      'desc' => 'Select if you want to display Currency changes in price',
      'id' => 'display_changes',
      'type' => 'checkbox',
    ));
    $cmb->add_field(array(
      'name' => 'Where Do You Want to Display Ticker? (Optional)',
      'desc' => '<br>Select the option where you want to display ticker',
      'id' => 'ticker_position',
      'type' => 'radio_inline',
      'options' => array(
        'header' => __('Header', 'cmb2'),
        'footer' => __('Footer', 'cmb2'),
        'shortcode' => __('Anywhere', 'cmb2'),
      ),
      'default' => 'no',

      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )

    ));

    $cmb->add_field(array(
      'name' => 'Ticker Position(Top)',
      'desc' => 'Specify Top Margin (in px) - Only For Header Ticker',
      'id' => 'header_ticker_position',
      'type' => 'text',
      'default' => '33',
      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )
    ));

    $cmb->add_field(array(
      'name' => 'Speed of Ticker',
      'desc' => 'Enter the speed of ticker (between 10-80)',
      'id' => 'ticker_speed',
      'type' => 'text',
      'default' => '30',
      'attributes' => array(
         // 'required' => true,        
        'data-conditional-id' => 'type',
        'data-conditional-value' => 'ticker',
      )
    ));

    $cmb->add_field(array(
      'name' => 'Background Color',
      'desc' => 'Select background color',
      'id' => 'back_color',
      'type' => 'colorpicker',
      'default' => '#eee',
	  'attributes' => array(
        'data-conditional-id' => 'type',
        'data-conditional-value' => json_encode(array('multi-currency-tab', 'list-widget', 'ticker')),
      )
    ));

    $cmb->add_field(array(
      'name' => 'Font Color',
      'desc' => 'Select font color',
      'id' => 'font_color',
      'type' => 'colorpicker',
      'default' => '#000',
    ));

    $cmb->add_field(array(
      'name' => 'Custom CSS',
      'desc' => 'Enter custom CSS',
      'id' => 'custom_css',
      'type' => 'textarea',

    ));
    $cmb->add_field(array(
      'name' => '',
      'desc' => '
  <h3>Check Our Cool Premium Crypto Plugins - Now Create Website Similar Like CoinMarketCap.com<br/></h3>
  <div class="cmc_pro">
  <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050"><img style="max-width:100%;" src="https://res.cloudinary.com/coolplugins/image/upload/v1530694709/crypto-exchanges-plugin/banner-crypto-widget-pro.png"></a>
  </div><hr/>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530695051/crypto-exchanges-plugin/banner-coinmarketcap.png"></a>
   </div><hr/>
    <div class="cmc_pro">
   <a target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-exchanges-list-pro-wordpress-plugin%2F22098669"><img style="max-width:100%;"src="https://res.cloudinary.com/coolplugins/image/upload/v1530694721/crypto-exchanges-plugin/banner-crypto-exchanges.png"></a> </div>',
      'type' => 'title',
      'id' => 'cmc_title'
    ));
    // Add other metaboxes as needed

  }



