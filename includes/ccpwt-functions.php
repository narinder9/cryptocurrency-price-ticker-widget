<?php
	// Register Custom Post Type of Crypto Widget
	function ccpw_post_type() {

		$labels = array(
			'name'                  => _x( 'CryptoCurrency Price Widget', 'Post Type General Name', 'ccpw' ),
			'singular_name'         => _x( 'CryptoCurrency Price Widget', 'Post Type Singular Name', 'ccpw' ),
			'menu_name'             => __( 'Crypto Widget', 'ccpw' ),
			'name_admin_bar'        => __( 'Post Type', 'ccpw' ),
			'archives'              => __( 'Item Archives', 'ccpw' ),
			'attributes'            => __( 'Item Attributes', 'ccpw' ),
			'parent_item_colon'     => __( 'Parent Item:', 'ccpw' ),
			'all_items'             => __( 'All Shortcodes', 'ccpw' ),
			'add_new_item'          => __( 'Add New Shortcode', 'ccpw' ),
			'add_new'               => __( 'Add New', 'ccpw' ),
			'new_item'              => __( 'New Item', 'ccpw' ),
			'edit_item'             => __( 'Edit Item', 'ccpw' ),
			'update_item'           => __( 'Update Item', 'ccpw' ),
			'view_item'             => __( 'View Item', 'ccpw' ),
			'view_items'            => __( 'View Items', 'ccpw' ),
			'search_items'          => __( 'Search Item', 'ccpw' ),
			'not_found'             => __( 'Not found', 'ccpw' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'ccpw' ),
			'featured_image'        => __( 'Featured Image', 'ccpw' ),
			'set_featured_image'    => __( 'Set featured image', 'ccpw' ),
			'remove_featured_image' => __( 'Remove featured image', 'ccpw' ),
			'use_featured_image'    => __( 'Use as featured image', 'ccpw' ),
			'insert_into_item'      => __( 'Insert into item', 'ccpw' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'ccpw' ),
			'items_list'            => __( 'Items list', 'ccpw' ),
			'items_list_navigation' => __( 'Items list navigation', 'ccpw' ),
			'filter_items_list'     => __( 'Filter items list', 'ccpw' ),
		);
		$args = array(
			'label'                 => __( 'CryptoCurrency Price Widget', 'ccpw' ),
			'description'           => __( 'Post Type Description', 'ccpw' ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'taxonomies'            => array(''),
			'hierarchical'          => false,
			'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
			'show_ui'               => true,
			'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive' => false,  // it shouldn't have archive page
			'rewrite' => false,  // it shouldn't have rewrite rules
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			 'menu_icon'           => 'dashicons-chart-area',
			'capability_type'       => 'page',
		);
		register_post_type( 'ccpw', $args );

	}

	/**
	 * Define the metabox and field configurations.
	 */
	function cmb2_ccpw_metaboxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ccpw_';

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'generate_shortcode',
        'title'         => __( 'Generate Shortcode', 'cmb2' ),
        'object_types'  => array( 'ccpw'), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
    ) );
    $cmb->add_field( array(
    'name'    => 'Type<span style="color:red;">*</span>',
    'id'      => 'type',
    'type'    => 'radio_inline',
    'options' => array(
        'ticker' => __( 'Ticker', 'cmb2' ),
        'list-widget'   => __( 'List Widget', 'cmb2' ),
    ),
    'default' => 'ticker',
    ) );


    $cmb->add_field( array(
    'name'    => 'Display Currencies<span style="color:red;">*</span>',
    'desc'    => '',
    'id'      => 'display_currencies',
    'type'    => 'multicheck',
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
                     'ethereum-classic'=>'Ethereum Classic',
                     'lisk'=>'Lisk',
                      'tron'=>'Tron',
                    'vechain'=>'VeChain',
                     'qtum'=>'Qtum',
                      'bitcoin-gold'=>'Bitcoin Gold',
                       'tether'=>'Tether',
                       'omisego'=>'OmiseGO'
        ),
    ) );

    $cmb->add_field( array(
    'name' => 'Display Logos? (Optional)',
    'desc' => 'Select if you want to display Currency logos',
    'id'   => 'display_logo',
    'type' => 'checkbox',
        ) );
    $cmb->add_field( array(
    'name' => 'Display 24 Hours changes? (Optional)',
    'desc' => 'Select if you want to display Currency changes in price',
    'id'   => 'display_changes',
    'type' => 'checkbox',
    ) );
    $cmb->add_field( array(
   'name'    => 'Where Do You Want to Display Ticker? (Optional)',
    'desc'    => '<br>Select the option where you want to display ticker',
   'id'      => 'ticker_position',
   'type'    => 'radio_inline',
   'options' => array(
       'header'   => __( 'Header', 'cmb2' ),
       'footer'   => __( 'Footer', 'cmb2' ),
        'shortcode' => __( 'Anywhere', 'cmb2' ),
   ),
   'default' => 'no',
   ) );
   
   $cmb->add_field( array(
   'name'    => 'Ticker Position(Top)',
    'desc'    => 'Specify Top position value of ticker(in px)',
   'id'      => 'header_ticker_position',
   'type'    => 'text',
   'default' => '33',
   ) );
   
  $cmb->add_field( array(
   'name'    => 'Speed of Ticker',
    'desc'    => 'Enter the speed of ticker (between 20-100)',
   'id'      => 'ticker_speed',
   'type'    => 'text',
   'default' => '30',
   ) );
   
  $cmb->add_field( array(
   'name'    => 'Background Color',
   'desc'    => 'Select background color',
   'id'      => 'back_color',
   'type'    => 'colorpicker',
   'default' => '#eee',
   ) );
   
    $cmb->add_field( array(
   'name'    => 'Font Color',
   'desc'    => 'Select font color',
   'id'      => 'font_color',
   'type'    => 'colorpicker',
   'default' => '#000',
   ) );
   
   $cmb->add_field( array(
   'name'    => 'Custom CSS',
   'desc'    => 'Enter custom CSS',
   'id'      => 'custom_css',
   'type'    => 'textarea',
  
   ) );
   $cmb->add_field( array(
  'name' => '',
  'desc' => '
  <h1>Cryptocurrency Market Capitalizations PRO Plugin</h1>
  <div class="cmc_pro">
  <a target="_blank" href="https://coinmarketcapinfo.com/plugin/"><img  src="http://res.cloudinary.com/cooltimeline/image/upload/v1519113575/coin-market-cap-info_kttmk2.png"></a>
  </div>
    <div class="cmc_pro">
   <a target="_blank" href="https://coinmarketcapinfo.com/plugin/">    <img   src="http://res.cloudinary.com/cooltimeline/image/upload/v1519113575/coin-single-page_ibkup6.png"></a> </div>',
  'type' => 'title',
  'id'   => 'cmc_title'
) );
    // Add other metaboxes as needed

	}
	/*
	Added meta boxes for shortcode
	*/

	function register_ccpw_meta_box()
	{
	    add_meta_box( 'ccpw-shortcode', 'Crypto Currency Price shortcode','p_shortcode_meta', 'ccpw', 'side', 'high' );
	}

	function p_shortcode_meta()
		{ 
	    $id = get_the_ID();
	    $dynamic_attr='';
	    _e('<p>Paste this shortcode in anywhere (page/post)</p>','ccpw'); 

	   $element_type = get_post_meta( $id, 'pp_type', true );
	   $dynamic_attr.="[ccpw id=\"{$id}\"";
	   $dynamic_attr.=']';
	    ?>
	    <input type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr) ;?>" readonly/>
	   	
	    <?php 
	}


	function ccpw_add_meta_boxes( $post){
		 add_meta_box(
                'ccpw-feedback-section',
                __( 'Hopefully you are Happy with our plugin','ccpw'),
                'ccpw_right_section',
                'ccpw',
                'side',
                'low'
            );
	}
	function ccpw_right_section($post, $callback){
        global $post;
        $pro_add=''; 
        $pro_add .=
        __('May I ask you to give it a 5-star rating on <strong><a target="_blank" href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post">'.__('WordPress.ORG','ccpw').'</a></strong>?','ccpw').'.<br/>'.
         __('This will help to spread its popularity and to make this plugin a better one on  ','ccpw').
        '<strong><a target="_blank" href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post">'.__('WordPress.ORG','ccpw').'</a></strong><br/>
        <a target="_blank" href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post"><img src="https://res.cloudinary.com/cooltimeline/image/upload/v1504097450/stars5_gtc1rg.png"></a>
        <hr>
         <div>
        <h3>Pro Features:-</h3>
      <ol style="list-style:disc;"><li> <strong>It supports both COINMARKETCAP & CYPTOCOMPARE apis. </strong></li> 
		<li>  You can display live price changes in pro version.</li> 
		<li>  It supports <strong>100+ top coins</strong>.</li> 
		<li>  You can create beautiful price label and crypto price card designs.</li> 
    <li>  You can create beautiful single page with Price chart and coin details.</li> 
		<li>  Different currency support -<strong> USD, INR, Pound, Euro, Yen, WON</strong></li> 
		<li>  Display market cap and volume of virtual crypto coins.</li> 
		<li>  Many advanced design options.</li> 
		<li><a target="_blank" href="http://cryptowidgetpro.coolplugins.net/">'.__('View Demos','ccpw').'</a></li>

		<li>
		<h3><a target="_blank" href="https://codecanyon.net/item/cryptocurrency-price-ticker-widget-pro-wordpress-plugin/21269050?ref=CoolHappy">'.__('Buy Now','ccpw').'</a></h3></li>
		</ol>
		</div>';
        echo $pro_add ;

    }