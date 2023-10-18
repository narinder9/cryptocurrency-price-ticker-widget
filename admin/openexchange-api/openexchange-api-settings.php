<?php
// Do not use namespace to keep this on global space to keep the singleton initialization working
if ( ! class_exists( 'Openexchange_api_settings' ) ) {

	/**
	 *
	 * This is the main class for creating dashbord addon page and all submenu items
	 *
	 * Do not call or initialize this class directly, instead use the function mentioned at the bottom of this file
	 */
	class Openexchange_api_settings {

		/**
		 * None of these variables should be accessable from the outside of the class
		 */
		private static $instance;

		/**
		 * initialize the class and create dashboard page only one time
		 */
		public static function init() {
			if ( empty( self::$instance ) ) {
				return self::$instance = new self();
			}
			return self::$instance;

		}

		/**
		 * Initialize the dashboard with specific plugins as per plugin tag
		 */
		public function cool_init_hooks() {
			 add_action( 'admin_notices', array( $this, 'Openexchange_api_key_notice' ) );
			add_action( 'admin_menu', array( $this, 'Openexchange_add_submenu' ), 100 );
			add_action( 'cmb2_admin_init', array( $this, 'Openexchange_settings_callback' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'Openexchange_custom_javascript_for_cmb2' ) );

		}

		function Openexchange_custom_javascript_for_cmb2() {

			wp_enqueue_script( 'ccpw-openexchange', CCPWF_URL . 'admin/openexchange-api/js/ccpw-openexchange-notice.js', array( 'jquery' ), CCPWF_VERSION, true );

		}


		/**
		 * This function will initialize the main dashboard menu for all plugins
		 */
		public function Openexchange_add_submenu() {
			add_submenu_page( 'cool-crypto-plugins', 'Open Exchange API', 'Open Exchange API', 'manage_options', 'admin.php?page=openexchange-api-settings', false, 100 );

		}

		/**
		 * This function will render and create the HTML display of dashboard page.
		 * All the HTML can be located in other template files.
		 * Avoid using any HTML here or use nominal HTML tags inside this function.
		 */
		public function Openexchange_settings_callback() {
			/**
			 * Registers options page menu item and form.
			 */
			$cool_options = new_cmb2_box(
				array(
					'id'           => 'ccpw_settings_page',
					'title'        => esc_html__( 'Open Exchange Rates API', 'celp1' ),
					'object_types' => array( 'options-page' ),

					/*
					 * The following parameters are specific to the options-page box
					 * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
					 */
					'option_key'   => 'openexchange-api-settings', // The option key and admin menu page slug.
					'menu_title'   => false, // Falls back to 'title' (above).
					'parent_slug'  => 'cool-crypto-plugins', // Make options page a submenu item of the themes menu.
					'capability'   => 'manage_options', // Cap required to view options-page.
					'position'     => 44, // Menu position. Only
				)
			);

			/*
			 * Options fields ids only need
			 * to be unique within this box.
			 * Prefix is not needed.
			 */
			$cool_options->add_field(
				array(
					'name' => __( 'Enter OpenExchangeRates.org API Key', 'ccpw1' ),
					'id'   => 'ccpw_openexchangerate_api_title',
					'type' => 'title',

				)
			);

			$cool_options->add_field(
				array(
					'name' => __( 'Enter API Key', 'ccpw1' ),
					'desc' => __( 'Display cryptocurrency prices in over <b>30 fiat currencies</b>.<br/>
					>>  <a href="https://openexchangerates.org/signup/free" target="blank">Get OpenExchangeRates.org Free API Key</a>', 'ccpw1' ),
					'id'   => 'openexchangerate_api',
					'type' => 'text',

				)
			);

		}
		public function ccpwp_get_post_type_page()
		{
			global $post, $typenow, $current_screen;
	
			if ($post && $post->post_type) {
				return $post->post_type;
			} elseif ($typenow) {
				return $typenow;
			} elseif ($current_screen && $current_screen->post_type) {
				return $current_screen->post_type;
			} elseif (isset($_REQUEST['page'])) {
				return sanitize_key($_REQUEST['page']);
			} elseif (isset($_REQUEST['post_type'])) {
				return sanitize_key($_REQUEST['post_type']);
			} elseif (isset($_REQUEST['post'])) {
				return get_post_type($_REQUEST['post']);
			}
			return null;
		}
		/*
		|----------------------------------------------------------------
		|   Admin notice for OpenExchangeRates.org API key.
		|----------------------------------------------------------------
		 */
		public function openexchange_api_key_notice() {
			$api_option = get_option('openexchange-api-settings');
			$api = !empty($api_option['openexchangerate_api']) ? $api_option['openexchangerate_api'] : '';
		
			if (!current_user_can('delete_posts') || !empty($api)) {
				return;
			}
		
			$post_array = array('ccpw', 'openexchange-api-settings', 'ccpw_options', 'ccpw_get_started');
			$current_post_type = $this->ccpwp_get_post_type_page();
		
			if (in_array($current_post_type, $post_array)) {
				$current_user = wp_get_current_user();
				$user_name = esc_html(ucwords($current_user->display_name));
				$admin_url = get_admin_url(null, 'admin.php?page=openexchange-api-settings');
				?>
				<div class="license-warning notice notice-error is-dismissible" id="ccpw_dismiss_notice">
					<p> Hi, <strong><?php echo $user_name; ?></strong>! 
						Please <strong><a href="<?php echo esc_url($admin_url); ?>">enter OpenexchangeRates.org free API key</a></strong> to show cryptocurrency prices in different fiat currencies (<b>EUR, GBP, INR, AUD & more</b>)
					</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
				</div>
				<?php
			}
		}		
	}

	/**
	 *
	 * initialize the main dashboard class with all required parameters
	 */

	$Openexchange = Openexchange_api_settings::init();
	$Openexchange->cool_init_hooks();


}
