<?php
/**
 * Plugin Name:Cryptocurrency Widgets
 * Description:Cryptocurrency Widgets WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc. using CoinMarketCap API. Add <strong><a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050">premium cryptocurrency widgets</a></strong> inside your crypto blog or website. <strong><a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844">Click to create a website similar like coinmarketcap.com.</a></strong>
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/
 * Plugin URI:https://cryptowidget.coolplugins.net/
 * Version: 1.8.3
 * License: GPL2
 * Text Domain:ccpw
 * Domain Path: languages
 *
 *@package Cryptocurrency Price Ticker Widget*/

/*Copyright (C) 2016-18  Narinder Singh contact@coolplugins.net

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'Crypto_Currency_Price_Widget_VERSION' ) ) {
	return;
}

/*
	Defined constent for later use
*/
define( 'Crypto_Currency_Price_Widget_VERSION', '1.8.3' );
define( 'Crypto_Currency_Price_Widget_FILE', __FILE__ );
define( 'Crypto_Currency_Price_Widget_PATH', plugin_dir_path( Crypto_Currency_Price_Widget_FILE ) );
define( 'Crypto_Currency_Price_Widget_URL', plugin_dir_url( Crypto_Currency_Price_Widget_FILE ) );

register_activation_hook( Crypto_Currency_Price_Widget_FILE, array( 'Crypto_Currency_Price_Widget', 'activate' ) );
register_deactivation_hook( Crypto_Currency_Price_Widget_FILE, array( 'Crypto_Currency_Price_Widget', 'deactivate' ) );


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
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		$this->includes();
		$this->installation_date();
	
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		//main plugin shortcode for list widget
		add_shortcode( 'ccpw', array( $this, 'ccpw_shortcode' ));
		
		add_action( 'save_post', array( $this,'save_ccpw_shortcode'),10, 3 );

		//creating posttype for plugin settings panel
		add_action( 'init','ccpw_post_type');	
		// integrating cmb2 metaboxes in post type
		add_action( 'cmb2_admin_init','cmb2_ccpw_metaboxes');
		add_action( 'add_meta_boxes','register_ccpw_meta_box');
		
		// check coin market cap plugin is activated.
		add_action('admin_init', array($this, 'ccpwt_check_cmc_activated'));

		add_action( 'wp_footer', array($this,'ticker_in_footer') );
		add_action( 'wp_footer', array($this,'ccpw_enable_ticker') );

		if(is_admin()){
		add_action( 'admin_init',array($this,'check_installation_time'));
		add_action( 'admin_init',array($this,'ccpw_spare_me'), 5 );
	//	add_action ('upgrader_process_complete', array($this,'ccpwt_upgrade_completed', 10, 2));
		add_action('admin_enqueue_scripts', array($this,'ccpw_load_scripts'));
		add_action('admin_head-edit.php', array($this, 'ccpwt_custom_btn'));	
		add_action( 'add_meta_boxes_ccpw','ccpw_add_meta_boxes');

		add_filter( 'manage_ccpw_posts_columns',array($this,'set_custom_edit_ccpw_columns'));
		add_action( 'manage_ccpw_posts_custom_column' ,array($this,'custom_ccpw_column'), 10, 2 );
		}
	}

	/**
	 * Load plugin function files here.
	 */
	public function includes() {

		/**
		 * Get the bootstrap!
		 */
		if ($this->ccpw_get_post_type_page() == "ccpw") {
			require_once __DIR__ . '/cmb2/init.php';
			require_once __DIR__ . '/cmb2/cmb2-conditionals.php';
			require_once __DIR__ . '/cmb2/cmb-field-select2/cmb-field-select2.php';
		}

		//loading required functions
		require_once __DIR__ . '/includes/ccpwt-functions.php';
		require_once __DIR__ . '/includes/ccpwt-widget.php';
	}
	
	/**
	 * Crypto Widget Main Shortcode
	 */

	function ccpw_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id'  => '',
		'class' => ''
	), $atts, 'ccpw' );

	$post_id=$atts['id'];

	/*
	 *	Return if post status is anything other than 'publish'
	 */
	if( get_post_status( $post_id ) != "publish" ){
		return;
	}

	// Grab the metadata from the database
	$type = get_post_meta($post_id,'type', true );
	$currency = get_post_meta($post_id, 'currency', true);
	$fiat_currency= $currency ? $currency :"USD";
	$ticker_position = get_post_meta($post_id,'ticker_position', true );
    $header_ticker_position = get_post_meta($post_id,'header_ticker_position', true );
	$ticker_speed = get_post_meta($post_id,'ticker_speed', true ) ;
	$t_speed=$ticker_speed ?$ticker_speed:15;
	$display_currencies = get_post_meta($post_id,'display_currencies', true );
	if($display_currencies==false){
		$display_currencies=array();
	}
	$output='';$cls='';$crypto_html='';
	$display_logo = get_post_meta($post_id,'display_logo', true );
	$display_changes = get_post_meta($post_id,'display_changes', true );
    $back_color = get_post_meta($post_id,'back_color', true );
	$font_color = get_post_meta($post_id,'font_color', true );
	$custom_css = get_post_meta($post_id,'custom_css', true );
	$id = "ccpw-ticker" . $post_id . rand(1, 20);
	// Initialize Titan for cmc links
		if (class_exists('TitanFramework')) {
			$cmc_titan = TitanFramework::getInstance('cmc_single_settings');

			$cmc_slug = $cmc_titan->getOption('single-page-slug');

			if (empty($cmc_slug)) {
				$cmc_slug = 'currencies';
			}
		} else {
			$cmc_slug = 'currencies';
		}



	wp_enqueue_style('ccpw-font-awesome','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

	// loading Scripts for ticker widget


	if($type=="ticker"){
	$id = "ccpw-ticker-widget-" . $post_id;	
//	wp_enqueue_script('ccpw_marque_js',Crypto_Currency_Price_Widget_URL. 'assets/marquee/jquery.webticker.min.js',array( 'jquery' ));
	wp_enqueue_script('ccpw_marque_js', '//cdn.jsdelivr.net/npm/jquery.marquee@1.5.0/jquery.marquee.min.js', array('jquery'), null, true);
	wp_add_inline_script('ccpw_marque_js', 'jQuery(document).ready(function($){
		$(".ccpw-ticker-cont #'.$id.'").each(function(index){
			$(this).marquee({
				allowCss3Support: true,
				speed: '.$t_speed.'-'.$t_speed.'* 60/100,
				pauseOnHover: true,
				gap: 0,
				delayBeforeStart: 0,
				direction: "left",
				duplicated: true,
			startVisible: true,
			});
		});

	});' );

	} else if($type=="multi-currency-tab"){

	wp_enqueue_script('ccpw_script',Crypto_Currency_Price_Widget_URL. 'assets/js/ccpwt-script.js',array('jquery'));

	}
	
	// CCPW main styles file
	wp_enqueue_style('ppcw-styles', Crypto_Currency_Price_Widget_URL. 'assets/css/ppcw-styles.css', array(), null, null, 'all');
	

	/* dynamic styles */
	$dynamic_styles="";
	$styles='';
	$dynamic_styles_list="";
	$dynamic_styles_multicurrency="";
	$bg_color=!empty($back_color)? "background-color:".$back_color.";":"background-color:#fff;";
	$bg_coloronly=!empty($back_color)? ":".$back_color."d9;":":#ddd;";
	$fnt_color=!empty($font_color)? "color:".$font_color.";":"color:#000;";
	$fnt_coloronly=!empty($font_color)? ":".$font_color."57;":":#666;";
	$fnt_colorlight=!empty($font_color)? ":".$font_color."1F;":":#eee;";
	$ticker_top=!empty($header_ticker_position)? "top:".$header_ticker_position."px !important;":"top:0px !important;";

	if ($type == "ticker") {
		$id = "ccpw-ticker-widget-" . $post_id;	
		$dynamic_styles.=".tickercontainer #".$id."{".$bg_color."}
		.tickercontainer #".$id." span.name {".$fnt_color."}	
		.tickercontainer #".$id." span.coin_symbol {".$fnt_color."}
			
		.tickercontainer #".$id." span.price {".$fnt_color."} .tickercontainer .price-value{".$fnt_color."}
		.ccpw-header-ticker-fixedbar{".$ticker_top."}";
	
	}
	else if ($type == "price-label") {
		$id = "ccpw-label-widget-" . $post_id;	
		$dynamic_styles .= "#".$id.".ccpw-price-label li a , #".$id.".ccpw-price-label li{" . $fnt_color . "}
		";

	}
	else if($type == "list-widget"){
			$id = "ccpw-list-widget-" . $post_id;	
			$dynamic_styles .="#".$id.".ccpw-widget{".$bg_color."}
			#".$id.".ccpw-widget .ccpw_table tr{".$bg_color.$fnt_color."}
			#".$id.".ccpw-widget .ccpw_table tr th, #".$id.".ccpw-widget .ccpw_table tr td,
			#".$id.".ccpw-widget .ccpw_table tr td a{".$fnt_color."}
			";
		
	}
	else if ($type == "multi-currency-tab") {
			$id = "ccpw-multicurrency-widget-" . $post_id;	
			$dynamic_styles .=".currency_tabs#".$id.",.currency_tabs#".$id." ul.multi-currency-tab li.active-tab{".$bg_color."}
			.currency_tabs#".$id." .mtab-content, .currency_tabs#".$id." ul.multi-currency-tab li, .currency_tabs#".$id." .mtab-content a{".$fnt_color."}";
	}

	 if($type=="multi-currency-tab"){
		  $usd_conversions=(array)ccpwt_usd_conversions('all');
		}else{
		  $usd_conversions=array();
		}
	
		$coins_row_data = ccpwt_coin_arr($limit = 200, $fiat_currency, $return_type = "all");
		$all_coin_data = ccpwt_objectToArray($coins_row_data);

		if (is_array($display_currencies) && !empty($display_currencies)) {
			foreach ($display_currencies as $currency) {
				if(isset($all_coin_data[$currency])){
				$coin = $all_coin_data[$currency];
				require(__DIR__ . '/includes/generate-html.php');
				$crypto_html .= $coin_html;
				}
			}

		} else {
			return _e('You have not selected any currencies to display', 'ccpw');
		}	

		if ($type=="ticker"){
				$id = "ccpw-ticker-widget-" . $post_id;	
			 	if($ticker_position=="footer"||$ticker_position=="header"){
			 		$cls='ccpw-sticky-ticker';
			 		if($ticker_position=="footer"){
			 			$container_cls='ccpw-footer-ticker-fixedbar';
			 		}else{
			 			$container_cls='ccpw-header-ticker-fixedbar';
			 		}
			 		
			 	}else{
			 		$cls='ccpw-ticker-cont';
			 		$container_cls='';
			 	}

		
			$output .= '<div style="display:none" class="ccpw-container ccpw-ticker-cont '.$container_cls.'">';
			$output .= '<div class="tickercontainer" style="height: auto; overflow: hidden;"><div class="mask">';
			$output .= '<ul  id="'.$id.'">';
			$output .= $crypto_html;
			$output .= '</ul></div>';

	}else if($type == "price-label"){
			$id = "ccpw-label-widget-".$post_id;	
			$output .='<div id="'.$id.'" class="ccpw-container ccpw-price-label"><ul class="lbl-wrapper">';
			$output .= $crypto_html;
			$output .= '</ul></div>';
	 
		}else if($type=="list-widget"){
			$cls='ccpw-widget';
			$id="ccpw-list-widget-".$post_id;	
			$output .= '<div id="'.$id.'" class="'.$cls.'"><table class="ccpw_table" style="border:none!important;"><thead>
			<th>'.__('Name','ccpw').'</th>
			<th>'.__('Price','ccpw').'</th>';
			if($display_changes){
			$output .='<th>'.__('24H (%)','ccpw').'</th>';
				}
			$output .='</thead><tbody>';
			$output .= $crypto_html;
			$output .= '</tbody></table></div>';
		
      }else if($type=="multi-currency-tab"){
				$id = "ccpw-multicurrency-widget-" . $post_id;	
				$output .= '<div class="currency_tabs" id="'.$id.'">';
      			$output .= '<ul class="multi-currency-tab">
      			<li data-currency="usd" class="active-tab">'.__("USD","ccpw").'</li>
      			<li data-currency="eur">'.__("EUR","ccpw").'</li>
      			<li data-currency="gbp">'.__("GPB","ccpw").'</li>
      			<li data-currency="aud">'.__("AUD","ccpw").'</li>
      			<li data-currency="jpy">'.__("JPY","ccpw").'</li>
      			</ul>';
				$output .= '<div><ul class="multi-currency-tab-content">';
      			$output .= $crypto_html;
      			$output .= '</ul></div></div>';

				}
				$cmc_css= $dynamic_styles . $custom_css;
			
		wp_add_inline_style('ppcw-styles', $cmc_css);

		$ccpw_v='<!-- Cryptocurrency Widgets - Version:- '.Crypto_Currency_Price_Widget_VERSION.' By Cool Plugins (CoolPlugins.net) -->';	
			return  $ccpw_v.$output;	
		 
	/*	}else{
			 return _e('There is something wrong with the server','ccpw');
		} */
	}
	
		/**
		 * Code you want to run when all other plugins loaded.
		 */
		public function init() {
			load_plugin_textdomain( 'ccpw', false, Crypto_Currency_Price_Widget_PATH . 'languages' );
		}

	/**
	 * Run when activate plugin.
	 */
	public static function activate() {
	}

	/**
	 * Run when deactivate plugin.
	 */
	public static function deactivate() {
	}


	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
function save_ccpw_shortcode( $post_id, $post, $update ) {
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		        return;
		// AJAX? Not used here
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
		        return;
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
		        return;
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
		        return;
    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $post_type = get_post_type($post_id);

    // If this isn't a 'book' post, don't update it.
    if ( "ccpw" != $post_type ) return;
	    // - Update the post's metadata.
    	if(isset($_POST['ticker_position'])&& in_array($_POST['ticker_position'],array('header','footer'))){
		    update_option('ccpw-p-id',$post_id);
		    update_option('ccpw-shortcode',"[ccpw id=".$post_id."]");
			}

		delete_transient( 'ccpw-coins' ); // Site Transient
	}

	/*
		Added ticker shortcode in footer hook for footer ticker
	*/

	function ticker_in_footer(){
		 $id=get_option('ccpw-p-id');
		if($id){
				$ticker_position = get_post_meta($id,'ticker_position', true );
    			$type = get_post_meta($id,'type', true );
  		
    			if($type=="ticker"){
    			if($ticker_position=="header"||$ticker_position=="footer"){
					 $shortcode=get_option('ccpw-shortcode');
					echo do_shortcode($shortcode);
				 }
				}
			}	
	}

	// Re-enable ticker after dom load
	function ccpw_enable_ticker(){
		echo'<script type="text/javascript">
		jQuery(document).ready(function($){
			$(".ccpw-ticker-cont").fadeIn();     
		});
		</script>';
	}

	/*
	For ask for reviews code
	*/

	function installation_date(){
		 $get_installation_time = strtotime("now");
   	 	  add_option('ccpw_activation_time', $get_installation_time ); 
	}	

	//check if review notice should be shown or not

	function check_installation_time() {
		$spare_me = get_option('ccpw_spare_me');
  		if(get_option('ccpw_spare_me')==false){
		  $install_date = get_option( 'ccpw_activation_time' );
	        $past_date = strtotime( '-1 days' );
	      if ( $past_date >= $install_date ) {
	     	 add_action( 'admin_notices', array($this,'ccpw_display_admin_notice'));
	     		}
	    }
	}

	/**
	* Display Admin Notice, asking for a review
	**/
	function ccpw_display_admin_notice() {
	    // wordpress global variable 
	    global $pagenow;
	//    if( $pagenow == 'index.php' ){
	        $dont_disturb = esc_url( get_admin_url() . '?spare_me=1' );
	        $plugin_info = get_plugin_data( __FILE__ , true, true );       
	        $reviewurl = esc_url( 'https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post' );
			echo $html='<div class="ccpw-review wrap"><img src="'.plugin_dir_url(__FILE__).'assets/crypto-widget.png" />
			<p>You have been using <b> '.$plugin_info['Name']. '</b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !<br/>
			<br/><a href="'.$reviewurl.'" class="button button-primary" target=
				"_blank">Rate Now! ★★★★★</a>
				<a href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050" class="button button-secondary" style="margin-left: 10px !important;" target="_blank"> Try Crypto Widgets Pro !</a>
				<a href="'.$dont_disturb.'" class="ccpw-review-done"> Already Done ☓</a></p></div>';
	       
	   // }
	}

	
	function ccpwt_upgrade_completed($upgrader_object, $options)
	{
 		// The path to our plugin's main file
		$ccpwt_plugin = plugin_basename(__FILE__);
		if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
 		 // Iterate through the plugins being updated and check if ours is there
			foreach ($options['plugins'] as $plugin) {
				if ($plugin == $ccpwt_plugin) {
					update_option('ccpw_spare_me',false);
				}
			}
		}
	}

 	 function set_custom_edit_ccpw_columns($columns) {
	   $columns['type'] = __( 'Widget Type', 'ccpw' );
	    $columns['shortcode'] = __( 'Shortcode', 'ccpw' );
	   return $columns;
	}

	function custom_ccpw_column( $column, $post_id ) {
	    switch ( $column ) {
			case 'type' :
	          $type=get_post_meta( $post_id , 'type' , true ); 
	         if($type=="ticker"){
	         	_e('Ticker','ccpw');
	         }else if($type == "price-label"){
					_e('Price Label', 'ccpw');
			 } else if ($type == "multi-currency-tab") {
					_e('Multi Currency Tabs', 'ccpw');
				} else{
	         	_e('List Widget','ccpw');
	         }
	      	 break;
		   case 'shortcode' :
	            echo '<code>[ccpw id="'.$post_id.'"]</code>'; 
	            break;

	    }
	}

	/*
	 check admin side post type page
	*/
	function ccpw_get_post_type_page() {
    global $post, $typenow, $current_screen;
 
	 if ( $post && $post->post_type ){
	        return $post->post_type;
	 }elseif( $typenow ){
	        return $typenow;
	  }elseif( $current_screen && $current_screen->post_type ){
	        return $current_screen->post_type;
	 }
	 elseif( isset( $_REQUEST['post_type'] ) ){
	        return sanitize_key( $_REQUEST['post_type'] );
	 }
	 elseif ( isset( $_REQUEST['post'] ) ) {
	   return get_post_type( $_REQUEST['post'] );
	 }
	  return null;
	}

	function ccpw_admin_css(){

	}
	// remove the notice for the user if review already done or if the user does not want to
	function ccpw_spare_me(){    
	    if( isset( $_GET['spare_me'] ) && !empty( $_GET['spare_me'] ) ){
			$spare_me = $_GET['spare_me'];
		
	        if( $spare_me == 1 ){
				update_option('ccpw_spare_me',true);
	        }
	    }
	}

	//check coin market cap plugin is activated. then enable links
	function ccpwt_check_cmc_activated()
	{
		if (is_plugin_active('coin-market-cap/coin-market-cap.php') || class_exists('CoinMarketCap')) {
			update_option('cmc-dynamic-links', true);
		} else {
			update_option('cmc-dynamic-links', false);
		}
	}
	
	public function ccpwt_custom_btn()
	{
		global $current_screen;

    // Not our post type, exit earlier
 		if ('ccpw' != $current_screen->post_type) {
			return;
		}

		?>
        <script type="text/javascript">
            jQuery(document).ready( function($)
            {
				$(".wrap").find('a.page-title-action').after("<a  id='ccpwt_add_premium' href='https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050' target='_blank' class='add-new-h2'>Add Premium Widgets</a>");
                
            });
        </script>
    <?php

	}

	function ccpw_load_scripts($hook) {
 
	//if( $hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php' ) 
	//	return;
 		wp_enqueue_style( 'ccpw-custom-styles', Crypto_Currency_Price_Widget_URL.'assets/css/ppcw-admin-styles.css');
		}
	}

function Crypto_Currency_Price_Widget() {
	return Crypto_Currency_Price_Widget::get_instance();
}

$GLOBALS['Crypto_Currency_Price_Widget'] = Crypto_Currency_Price_Widget();
