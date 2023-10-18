<?php

class CPTW_Posttype {

	public function __construct() {
		 // creating posttype for plugin settings panel
		add_action( 'init', array( $this, 'ccpw_post_type' ) );

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'register_ccpw_meta_box' ) );
			add_action( 'add_meta_boxes_ccpw', array( $this, 'ccpw_add_meta_boxes' ) );
			add_filter( 'manage_ccpw_posts_columns', array( $this, 'set_custom_edit_ccpw_columns' ) );
			add_action( 'manage_ccpw_posts_custom_column', array( $this, 'custom_ccpw_column' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_ccpw_shortcode' ), 10, 3 );
		}
		require_once CCPWF_DIR . 'admin/ccpw-settings.php';
		// integrating cmb2 metaboxes in post type
		add_action( 'cmb2_admin_init', 'cmb2_ccpw_metaboxes' );
		add_action( 'cmb2_save_options-page_fields', array( $this, 'Settings_callback' ) );

	}
	public function Settings_callback() {
		   $old_api = get_option( 'ccpw_old_api' );

			  $api = get_option( 'ccpw_options' );
			$api   = ( ! isset( $api['select_api'] ) && empty( $api['select_api'] ) ) ? 'coin_gecko' : $api['select_api'];
		if ( $api != $old_api ) {
			$db = new ccpw_database();
			// $db->truncate_table();
			$db->drop_table();
			// $db->create_table();
			delete_transient( 'ccpw-saved-coindata' );
			delete_option( 'ccpw_data_save' );
			$db->create_table();
			$api     = get_option( 'ccpw_options' );
			$api     = ( ! isset( $api['select_api'] ) && empty( $api['select_api'] ) ) ? 'coin_gecko' : $api['select_api'];
			$api_obj = new CCPW_api_data();

			$data = ( $api == 'coin_gecko' ) ? $api_obj->ccpw_get_coin_gecko_data() : $api_obj->ccpw_get_coin_paprika_data();

			update_option( 'ccpw_old_api', $api );

		}

	}

	/*
	|--------------------------------------------------------------------------
	| Register Custom Post Type of Crypto Widget
	|--------------------------------------------------------------------------
	*/
	function ccpw_post_type() {
		$labels = array(
			'name'                  => _x( 'Cryptocurrency Widgets', 'Post Type General Name', 'ccpwx' ),
			'singular_name'         => _x( 'Cryptocurrency Widget', 'Post Type Singular Name', 'ccpwx' ),
			'menu_name'             => __( 'Crypto Widgets', 'ccpwx' ),
			'name_admin_bar'        => __( 'Post Type', 'ccpwx' ),
			'archives'              => __( 'Item Archives', 'ccpwx' ),
			'attributes'            => __( 'Item Attributes', 'ccpwx' ),
			'parent_item_colon'     => __( 'Parent Item:', 'ccpwx' ),
			'all_items'             => __( 'All Shortcodes', 'ccpwx' ),
			'add_new_item'          => __( 'Add New Shortcode', 'ccpwx' ),
			'add_new'               => __( 'Add New', 'ccpwx' ),
			'new_item'              => __( 'New Item', 'ccpwx' ),
			'edit_item'             => __( 'Edit Item', 'ccpwx' ),
			'update_item'           => __( 'Update Item', 'ccpwx' ),
			'view_item'             => __( 'View Item', 'ccpwx' ),
			'view_items'            => __( 'View Items', 'ccpwx' ),
			'search_items'          => __( 'Search Item', 'ccpwx' ),
			'not_found'             => __( 'Not found', 'ccpwx' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'ccpwx' ),
			'featured_image'        => __( 'Featured Image', 'ccpwx' ),
			'set_featured_image'    => __( 'Set featured image', 'ccpwx' ),
			'remove_featured_image' => __( 'Remove featured image', 'ccpwx' ),
			'use_featured_image'    => __( 'Use as featured image', 'ccpwx' ),
			'insert_into_item'      => __( 'Insert into item', 'ccpwx' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'ccpwx' ),
			'items_list'            => __( 'Items list', 'ccpwx' ),
			'items_list_navigation' => __( 'Items list navigation', 'ccpwx' ),
			'filter_items_list'     => __( 'Filter items list', 'ccpwx' ),
		);
		$args   = array(
			'label'               => __( 'Coolmetamask', 'ccpwx' ),
			'description'         => __( 'Post Type Description', 'ccpwx' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'taxonomies'          => array( '' ),
			'hierarchical'        => false,
			'public'              => false, // it's not public, it shouldn't have it's own permalink, and so on
			'show_ui'             => true,
			'show_in_nav_menus'   => true, // you shouldn't be able to add it to menus
			'menu_position'       => 9,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => false,
			'can_export'          => true,
			'has_archive'         => false, // it shouldn't have archive page
			'rewrite'             => false, // it shouldn't have rewrite rules
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'menu_icon'           => CCPWF_URL . '/assets/ccpw-icon.png',
			'capability_type'     => 'post',
		);
		register_post_type( 'ccpw', $args );

	}

	/*
	|--------------------------------------------------------------------------
	| Register  meta boxes for shortcode
	|--------------------------------------------------------------------------
	*/
	function register_ccpw_meta_box() {
		 add_meta_box( 'ccpw-shortcode', 'Crypto Widget Shortcode', array( $this, 'ccpw_p_shortcode_meta' ), 'ccpw', 'side', 'high' );
	}


	/*
	Plugin Shortcode meta section
	*/
	function ccpw_p_shortcode_meta() {
		$id           = get_the_ID();
		$dynamic_attr = '';
		esc_html_e( 'Paste this shortcode anywhere in Page/Post.', 'ccpwx' );

		$element_type  = get_post_meta( $id, 'pp_type', true );
		$dynamic_attr .= "[ccpw id=\"{$id}\"";
		$dynamic_attr .= ']';
		?>
			<input style="width:100%" onClick="this.select();" type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo esc_attr( htmlentities( $dynamic_attr ) ); ?>" readonly/>
			<hr>
			<div style="display:flex;justify-content:space-between;">
				<a class="button button-secondary" target="_blank" href="https://cryptocurrencyplugins.com/demo/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=demo&utm_content=copy-shortcode">View Pro Demos</a>
				<a class="button button-primary" target="_blank" href="https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=copy-shortcode">Buy Pro</a>
			</div>
			<?php
	}

	/*
	|--------------------------------------------------------------------------
	| Register  meta boxes for Feedback
	|--------------------------------------------------------------------------
	*/

	function ccpw_add_meta_boxes( $post ) {
		add_meta_box(
			'ccpw-feedback-section',
			__( 'Information', 'ccpwx' ),
			array( $this, 'ccpw_right_section' ),
			'ccpw',
			'side',
			'low'
		);
	}

	/*
	Admin notice for plugin feedback
	*/
	function ccpw_right_section( $post, $callback ) {
		global $post;
		$pro_add  = '';
		$pro_add .=

		'<ul>
			<li><b>Q1. A website like coinmarketcap.com?</b><br/>
			Explore our <a href="https://cryptocurrencyplugins.com/coinmarketcap-clone-website/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=cmc-clone&utm_content=widget-sidebar" target="_blank">coinmarketcap clone website creation service</a> and create a website similar to that.
			<hr></li>
			
			<li><b>Q2. Accept cryptocurrency payments?</b><br/>
			Install our <a href="https://paywithcryptocurrency.net/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=metamask&utm_content=widget-sidebar" target="_blank">WooCommerce plugin for MetaMask</a> & start accepting crypto payments in your store.
			<hr></li>
			
			<li><b>Q3. More cryptocurrency widgets?</b><br/>
			Check our <a href="https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=widget-sidebar" target="_blank">Cryptocurrency Widgets Pro</a> plugin and add premium crypto widgets inside your website.
			<hr></li>
			
			<li><b>Q4. Enjoying our free plugin?</b><br/>
			If you really like our plugin, please leave a review on WP.org. Your review helps us keep improving the free plugin.
			<hr>
			<a style="width:100%;text-align:center;" href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post" class="button button-primary" target="_blank">' . __( 'Submit Review', 'ccpwx' ) . ' ★★★★★</a>
			</li>
		</ul>
        ';
		 

		echo wp_kses_post( $pro_add );

	}

	/*
	|--------------------------------------------------------------------------
	| Set Custom Column for Post Type
	|--------------------------------------------------------------------------
	*/

	function set_custom_edit_ccpw_columns( $columns ) {
		 $columns['type']      = __( 'Widget Type', 'ccpwx' );
		 $columns['shortcode'] = __( 'Shortcode', 'ccpwx' );
		 return $columns;
	}

	function custom_ccpw_column( $column, $post_id ) {
		switch ( $column ) {
			case 'type':
				  $type = get_post_meta( $post_id, 'type', true );
				switch ( $type ) {
					case 'ticker':
						esc_html_e( 'Ticker', 'ccpwx' );
						break;
					case 'price-label':
						esc_html_e( 'Price Label', 'ccpwx' );
						break;
					case 'multi-currency-tab':
						esc_html_e( 'Multi Currency Tabs', 'ccpwx' );
						break;
					case 'table-widget':
						esc_html_e( 'Table Widget', 'ccpwx' );
						break;
					default:
						esc_html_e( 'List Widget', 'ccpwx' );
				}
				break;
			case 'shortcode':
				echo '<code>[ccpw id="' . esc_html( $post_id ) . '"]</code>';
				break;
			default:
				esc_html_e( 'Not Matched', 'ccpwx' );
		}
	}


	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int  $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	function save_ccpw_shortcode( $post_id, $post, $update ) {
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// AJAX? Not used here
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) ) {
			return;
		}
		/*
		* In production code, $slug should be set only once in the plugin,
		* preferably as a class property, rather than in each function that needs it.
		*/
		$post_type = get_post_type( $post_id );

		// If this isn't a 'ccpw' post, don't update it.
		if ( 'ccpw' != $post_type ) {
			return;
		}
		// - Update the post's metadata.
		if ( isset( $_POST['ticker_position'] ) && in_array( $_POST['ticker_position'], array( 'header', 'footer' ) ) ) {
			update_option( 'ccpw-p-id', $post_id );
			update_option( 'ccpw-shortcode', '[ccpw id=' . $post_id . ']' );
		}

		delete_transient( 'ccpw-coins' ); // Site Transient
	}



}


