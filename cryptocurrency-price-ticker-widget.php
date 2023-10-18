<?php
/**
 * Plugin Name: Cryptocurrency Widgets
 * Description: Cryptocurrency price widgets for WordPress website. Display crypto ticker widget, coins live price list, table, labels & coin marketcap via shortcodes.
 * Plugin URI: https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=plugin-uri
 * Author: Cool Plugins
 * Author URI: https://coolplugins.net/?utm_source=ccpw_plugin&utm_medium=author_uri
 * Version: 2.6.4
 * License: GPL3
 * Text Domain: ccpw
 * Domain Path: languages
 *
 * @package Cryptocurrency Price Ticker Widget
 * 
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'CCPWF_VERSION' ) ) {
	return;
}
/*
Defined constent for later use
 */
define( 'CCPWF_VERSION', '2.6.4' );
define( 'CCPWF_FILE', __FILE__ );
define( 'CCPWF_DIR', plugin_dir_path( CCPWF_FILE ) );
define( 'CCPWF_URL', plugin_dir_url( CCPWF_FILE ) );
define( 'CCPWF_PRO_URL', 'https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro' );
define( 'CCPWF_DEMO_URL', 'https://cryptocurrencyplugins.com/demo/cryptocurrency-widgets-pro/' );
define( 'CCPWF_DEMO_UTM', '?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=demo' );


if ( ! class_exists( 'Crypto_Currency_Price_Widget' ) ) {
	/**
	 * Class Crypto_Currency_Price_Widget
	 */
	final class Crypto_Currency_Price_Widget {


		/**
		 * Plugin instance.
		 *
		 * @var Crypto_Currency_Price_Widget
		 * @access private
		 */
		private static $instance = null;

		/**
		 * Get plugin instance.
		 *
		 * @return Crypto_Currency_Price_Widget
		 * @static
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @access private
		 */
		private function __construct() {
			// register activation/ deactivation hooks
			register_activation_hook( CCPWF_FILE, array( $this, 'ccpw_activate' ) );
			register_deactivation_hook( CCPWF_FILE, array( $this, 'ccpw_deactivate' ) );

			// include required files
			$this->ccpw_includes();
			add_action('admin_init', array($this, 'ccpw_do_activation_redirect'));
			// verify plugin version
			add_action( 'init', array( $this, 'ccpw_verify_plugin_version' ) );
			// add_action('admin_init', array($this, 'ccpw_reg_settings'));

			// load text domain for translation
			add_action( 'plugins_loaded', array( $this, 'ccpw_plugins_loaded' ) );

			// ajax call for datatable server processing
			add_action( 'wp_ajax_ccpw_get_coins_list', array( $this, 'ccpw_get_coins_list' ) );
			add_action( 'wp_ajax_nopriv_ccpw_get_coins_list', array( $this, 'ccpw_get_coins_list' ) );

			// check coin market cap plugin is activated
			add_action( 'admin_init', array( $this, 'ccpw_check_cmc_activated' ) );

			add_action( 'wp_footer', array( $this, 'ticker_in_footer' ) );
			add_action( 'wp_footer', array( $this, 'ccpw_enable_ticker' ) );

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'init_crypto_admin_menu' ), 15 );

				add_action( 'admin_enqueue_scripts', array( $this, 'ccpw_load_scripts' ) );
				add_action( 'admin_head-edit.php', array( $this, 'ccpw_custom_btn' ) );

				add_action( 'wp_ajax_ccpw_delete_transient', array( $this, 'ccpw_delete_transient' ) );

				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'ccpw_add_widgets_action_links' ) );

			}

		}
		/**
		 * initialize cron : MUST USE ON PLUGIN ACTIVATION
		 */
		public function ccpw_cron_job_init() {
			if ( ! wp_next_scheduled( 'ccpw_coins_autosave' ) ) {
				wp_schedule_event( time(), '5min', 'ccpw_coins_autosave' );
			}
		}



		public function ccpw_data_insert() {
			$api     = get_option( 'ccpw_options' );
			$api     = ( ! isset( $api['select_api'] ) && empty( $api['select_api'] ) ) ? 'coin_gecko' : $api['select_api'];
			$api_obj = new CCPW_api_data();

			$data = ( $api == 'coin_gecko' ) ? $api_obj->ccpw_get_coin_gecko_data() : $api_obj->ccpw_get_coin_paprika_data();
		
		}

		/*
		|--------------------------------------------------------------------------
		| Load required files
		|--------------------------------------------------------------------------
		 */
		public function ccpw_includes() {
			require_once CCPWF_DIR . 'admin/addon-dashboard-page/addon-dashboard-page.php';
			cool_plugins_crypto_addon_settings_page( 'crypto', 'cool-crypto-plugins', 'Cryptocurrency Plugins Dashboard', 'Crypto Plugins', 'dashicons-chart-area' );
			require_once CCPWF_DIR . 'includes/api/ccpw-api-data.php';
			// require CCPWF_DIR . 'includes/ccpw-db-helper.php';

			// load post type geneartor
			require_once CCPWF_DIR . 'admin/register-post-type/ccpw-post-type.php';
			new CPTW_Posttype();
			require_once CCPWF_DIR . 'includes/ccpw-functions.php';
			
			$post_array = array( 'ccpw','cool-crypto-plugins', 'openexchange-api-settings', 'ccpw_options','ccpw_get_started' );
			if ( isset( $_POST['submit-cmb'] ) || in_array( ccpw_get_post_type_page(), $post_array ) ) {
				require_once CCPWF_DIR . 'admin/cmb2/init.php';
				require_once CCPWF_DIR . 'admin/cmb2/cmb2-conditionals.php';
				if ( ! class_exists( 'PW_CMB2_Field_Select2' ) ) {
					require_once CCPWF_DIR . 'admin/cmb2/cmb-field-select2/cmb-field-select2.php';
				}
			}

			// loading required functions
			require_once CCPWF_DIR . 'admin/review-notices/class.review-notice.php';

			if ( is_admin() ) {
				require_once CCPWF_DIR . 'admin/feedback/admin-feedback-form.php';
				require_once CCPWF_DIR . 'admin/openexchange-api/openexchange-api-settings.php';

			}
			require CCPWF_DIR . 'includes/ccpw-db-helper.php';
			require_once CCPWF_DIR . 'includes/cron/ccpw-cron.php';

			require_once CCPWF_DIR . 'includes/ccpw-shortcode.php';
			new CPTW_Shortcode();
		}

		/**
		 * Move plugin's menu into cryptocurrency plugin menu
		 */
		public function init_crypto_admin_menu() {
			add_submenu_page( 'cool-crypto-plugins', 'Cryptocurrency Widgets', '<strong>Crypto Widgets</strong>', 'manage_options', 'edit.php?post_type=ccpw', false, 15 );
			add_submenu_page( 'cool-crypto-plugins', 'Cryptocurrency Widgets', '↳ All Widgets', 'manage_options', 'edit.php?post_type=ccpw', false, 16 );
			add_submenu_page( 'cool-crypto-plugins', 'Add New Widget', '↳ Add New Widget', 'manage_options', 'post-new.php?post_type=ccpw', false, 17 );
			add_submenu_page( 'cool-crypto-plugins', 'Settings', ' ↳ Settings', 'manage_options', 'admin.php?page=ccpw_options', false, 18 );
		}


		public function ccpw_delete_transient() {

			  // Check for nonce security
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ccpw-nonce' ) ) {
				die( 'You don\'t have permission to delete the cache.' );
			}
			// Delete cache if user has permssion to delete it.
			if ( current_user_can( 'manage_options' ) ) {
				delete_transient( 'ccpw-saved-coindata' );
				delete_option( 'ccpw_data_save' );
				wp_send_json_success();
			}

		}

		/**
		 * Code you want to run when all other plugins loaded.
		 */
		public function ccpw_plugins_loaded() {
			// Require the main plugin file
			if ( ! function_exists( 'is_plugin_active' ) ) {
				// require only if needed
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			load_plugin_textdomain( 'ccpw', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Run when activate plugin.
		 */
		public function ccpw_activate() {
				$active_plugins = get_option('active_plugins', array());
				if (!in_array("cryptocurrency-price-ticker-widget-pro/cryptocurrency-price-ticker-widget-pro.php", $active_plugins)) {
					add_option('ccpw_do_activation_redirect', true);
				}
			$DB = new ccpw_database();
			$DB->create_table();
			$this->ccpw_cron_job_init();
			update_option( 'ccpw-type', 'FREE' );
			update_option( 'ccpw_activation_time', gmdate( 'Y-m-d h:i:s' ) );
			update_option( 'ccpw_data_save', 'false' );
			update_option( 'ccpw-alreadyRated', 'no' );

			$this->ccpw_data_insert();
		
		}
		public function ccpw_do_activation_redirect()
    {
        if (get_option('ccpw_do_activation_redirect', false)) {
			update_option('ccpw_do_activation_redirect',false);
            if (!isset($_GET['activate-multi'])) {			
               wp_redirect(admin_url('admin.php?page=ccpw_get_started'));
			   exit;
            }
        }
    }

		/*
		 * Run when deactivate plugin.
		 */
		public function ccpw_deactivate() {
			if ( wp_next_scheduled( 'ccpw_coins_autosave' ) ) {
				wp_clear_scheduled_hook( 'ccpw_coins_autosave' );
			}
			$db = new ccpw_database();
			$db->drop_table();
			delete_transient( 'ccpw-saved-coindata' );
		}

		/**
		 * server side processing ajax callback
		 */
		public function ccpw_get_coins_list() {
			 require_once CCPWF_DIR . 'includes/ccpw-ad-tbl-handler.php';
			ccpw_get_ajax_data();
			wp_die();
		}

		/*
		|--------------------------------------------------------------------------
		| Added ticker shortcode in footer hook for footer ticker
		|--------------------------------------------------------------------------
		 */
		public function ticker_in_footer() {
			if ( ! wp_script_is( 'jquery', 'done' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			$id = get_option( 'ccpw-p-id' );
			if ( $id ) {
				$ticker_position = get_post_meta( $id, 'ticker_position', true );
				$type            = get_post_meta( $id, 'type', true );

				if ( $type == 'ticker' ) {
					if ( $ticker_position == 'header' || $ticker_position == 'footer' ) {
						$shortcode = get_option( 'ccpw-shortcode' );
						echo do_shortcode( $shortcode );
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Re-enable ticker after dom load
		|--------------------------------------------------------------------------
		 */
		public function ccpw_enable_ticker() {
			wp_add_inline_script(
				'ccpw_bxslider_js',
				'jQuery(document).ready(function($){
				$(".ccpw-ticker-cont").fadeIn();
			});',
				'before'
			);

		}

		/*
		|--------------------------------------------------------------------------
		|  Check if plugin is just updated from older version to new!
		|--------------------------------------------------------------------------
		 */
		public function ccpw_verify_plugin_version() {
			// ccpw_widget_coin_peprika_insert_data();
			// ccpw_widget_insert_data();
			$CCPW_VERSION = get_option( 'CCPW_FREE_VERSION' );
			if ( ! isset( $CCPW_VERSION ) || version_compare( $CCPW_VERSION, CCPWF_VERSION, '<' ) ) {
				$this->ccpw_activate();
				$conversions = get_transient( 'cmc_usd_conversions' );
				if ( ! empty( $conversions ) ) {
					update_option( 'cmc_usd_conversions', $conversions );
				}
				update_option( 'CCPW_FREE_VERSION', CCPWF_VERSION );
			}
		} // end of cmc_plugin_version_verify()

		/*
		|--------------------------------------------------------------------------
		|  check coin market cap plugin is activated. then enable links
		|--------------------------------------------------------------------------
		 */
		public function ccpw_check_cmc_activated() {
			if ( is_plugin_active( 'coin-market-cap/coin-market-cap.php' ) || class_exists( 'CoinMarketCap' ) ) {
				update_option( 'cmc-dynamic-links', true );
			} else {
				update_option( 'cmc-dynamic-links', false );
			}
		}
		/*
		|--------------------------------------------------------------------------
		|  Integrated custom Button
		|--------------------------------------------------------------------------
		 */
		public function ccpw_custom_btn() {
			 global $current_screen;

			// Not our post type, exit earlier
			if ( 'ccpw' != $current_screen->post_type ) {
				return;
			}?>
		<script type="text/javascript">
			jQuery(document).ready( function($)
			{
				$(".wrap").find('a.page-title-action').after("<a  id='ccpw_add_premium' href='https://cryptocurrencyplugins.com/wordpress-plugin/cryptocurrency-widgets-pro/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=add-pro-widgets' target='_blank' class='add-new-h2'>Add Premium Widgets</a>");

			});
		</script>
			<?php

		}

		/*
		|--------------------------------------------------------------------------
		|  custom links for add widgets in all plugins section
		|--------------------------------------------------------------------------
		 */
		public function ccpw_add_widgets_action_links( $links ) {
			$links[] = '<a style="font-weight:bold" href="' . esc_url( get_admin_url( null, 'post-new.php?post_type=ccpw' ) ) . '">Add Widgets</a>';
			$links[] = '<a  style="font-weight:bold" href="https://cryptocurrencyplugins.com/demo/cryptocurrency-widgets-pro/" target="_blank">Click Demos</a>';
			return $links;

		}

		/*
		|--------------------------------------------------------------------------
		|  Load admin side custom Styles
		|--------------------------------------------------------------------------
		 */
		public function ccpw_load_scripts( $hook ) {
			wp_enqueue_style( 'ccpw-custom-styles', CCPWF_URL . 'assets/css/ccpw-admin-styles.css',array(), CCPWF_VERSION );
			wp_enqueue_script( 'ccpw-admin-script', CCPWF_URL . 'assets/js/admin-script.js', array( 'jquery' ), CCPWF_VERSION, true );
			 if ( get_post_type() === "ccpw" || $hook=="crypto-plugins_page_ccpw_get_started" || $hook=="crypto-plugins_page_ccpw_options"){
				wp_enqueue_style('ccpw-custom-setting-styles', CCPWF_URL . 'assets/css/ccpw-custom-setting-styles.css',array(), CCPWF_VERSION );
				}
			if(get_post_type() === "ccpw" || $hook=="crypto-plugins_page_ccpw_get_started"){
				wp_enqueue_script('ccpw-settings-custom-scripts', CCPWF_URL . 'assets/js/setting-custom-scripts.js',array('jquery'), CCPWF_VERSION, true);

			}
		}
	}

	function Crypto_Currency_Price_Widget() {
		return Crypto_Currency_Price_Widget::get_instance();
	}

	Crypto_Currency_Price_Widget();
}
