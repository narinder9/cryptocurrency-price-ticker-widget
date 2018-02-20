<?php
/**
 * Plugin Name:Cryptocurrency Price Ticker Widget
 * Description:Cryptocurrency price ticker widget WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc. using CoinMarketCap API.
 * Author:Cool Timeline Team
 * Author URI:https://www.cooltimeline.com/
 * Version: 1.4
 * License: GPL2
 * Text Domain:ccpw
 * Domain Path: languages
 *
 * @package Cryptocurrency Price Ticker Widget
 */

/*
Copyright (C) 2016  Narinder Singh narinder99143@gmail.com

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

define( 'Crypto_Currency_Price_Widget_VERSION', '1.4' );
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
		

		add_action( 'wp_footer', array($this,'ticker_in_footer') );
		add_action( 'wp_footer', array($this,'ccpw_enable_ticker') );

		if(is_admin()){
		add_action( 'admin_init',array($this,'check_installation_time'));
		add_action( 'admin_init',array($this,'ccpw_spare_me'), 5 );
		add_action('admin_enqueue_scripts', array($this,'ccpw_load_scripts'));

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
		require_once __DIR__ . '/cmb2/init.php';

		//loading required functions
		require_once __DIR__ . '/includes/ccpwt-functions.php';
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

	// Grab the metadata from the database
	$type = get_post_meta($post_id,'type', true );
	$ticker_position = get_post_meta($post_id,'ticker_position', true );
    $header_ticker_position = get_post_meta($post_id,'header_ticker_position', true );
	$ticker_speed = get_post_meta($post_id,'ticker_speed', true ) ;
	$t_speed=$ticker_speed ?$ticker_speed:30;
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

	wp_enqueue_style('ccpw-font-awesome','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

	// loading Scripts for ticker widget

	if($type=="ticker"){
	wp_enqueue_script('ccpw_marque_js',Crypto_Currency_Price_Widget_URL. 'assets/marquee/jquery.webticker.min.js', null, null, 'all');
	 wp_add_inline_script('ccpw_marque_js', 'jQuery(document).ready(function($){
	 	$(".ccpw-ticker-cont ul").each(function(index){
	 		var id="#"+$(this).attr("id");
	 			$(id).webTicker({
	 				speed:'.$t_speed.',
	 				moving:true,
	 				height:"34px",
	 				 duplicate:true,
	 				 hoverpause:true,
	 				  startEmpty:false,
	 				
	 			});
	 		});
	 	});' );
	}

	// CCPW main styles file
	wp_enqueue_style('ppcw-styles', Crypto_Currency_Price_Widget_URL. 'assets/css/ppcw-styles.css', array(), null, null, 'all');
	

	/* dynamic styles */
	$dynamic_styles="";
	$styles='';
	$dynamic_styles_list="";
	$bg_color=!empty($back_color)? "background-color:".$back_color.";":"background-color:#fff;";
	$fnt_color=!empty($font_color)? "color:".$font_color.";":"color:#000;";
	$ticker_top=!empty($header_ticker_position)? "top:".$header_ticker_position."px;":"top:33px;";
	
	$dynamic_styles.=".tickercontainer{".$bg_color."}
                     .tickercontainer span.name{".$fnt_color."}	
                      .tickercontainer span.coin_symbol{".$fnt_color."}
                        
                      .tickercontainer span.price{".$fnt_color."} .tickercontainer .price-value{".$fnt_color."}
					  .ccpw-header-ticker-fixedbar{".$ticker_top."}";
	$dynamic_styles.=$custom_css;
	
	$dynamic_styles_list.=".ccpw-widget{".$bg_color."} .ccpw-widget .ccpw_table tr{".$bg_color."}  
                     .ccpw_table span.name{".$fnt_color."}	
                        .ccpw_table span.coin_symbol{".$fnt_color."}
                        .ccpw_table	th{
                        	".$fnt_color."
                        }
                      .ccpw_table span.price{".$fnt_color."} .ccpw_table .price-value{".$fnt_color."}";
					  
	$dynamic_styles_list.=$custom_css;
					 

	// fetching data from api 
	$all_coin_data=$this->get_api_data(30);

		if( ! empty( $all_coin_data ) && is_array($all_coin_data)) {
			
			if(is_array($display_currencies)&& !empty($display_currencies)){

				foreach( $all_coin_data as $coin ) {
				if(in_array($coin->id,$display_currencies)){
					$crypto_html .=$this->generate_html($coin,$type,$display_logo,$display_changes,$ticker_position,$header_ticker_position,$ticker_speed,$back_color,$font_color,$custom_css);
				}
			}	
			}else{
				  return _e('You have not selected any currencies to display','ccpw');
			}	

			 if ($type=="ticker"){
				 $styles="<style type='text/css'>".$dynamic_styles."</style>";

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
			
         	$id="ccpw-ticker".$post_id.rand(1,20);
			$output .= '<div style="display:none" class="ccpw-container ccpw-ticker-cont '.$container_cls.'"><ul  id="'.$id.'">';
			$output .= $crypto_html;
			$output .= '</ul></div>';

			}else if($type=="list-widget"){
				
			$styles="<style type='text/css'>".$dynamic_styles_list."</style>";
			$cls='ccpw-widget';
				$id="ccpw-list-widget".$post_id;	

			$output .= '<div id="'.$id.'" class="'.$cls.'"><table class="ccpw_table" style="border:none!important;"><thead>
			<th>'.__('Crypto Currency','ccpw').'</th>
			<th>'.__('Price','ccpw').'</th>';
			if($display_changes){
			$output .='<th>'.__('24H Changes','ccpw').'</th>';
				}
			$output .='</thead><tbody>';
			$output .= $crypto_html;
			$output .= '</tbody></table></div>';
				
			}
            
			return $output.$styles;	
		 
		}else{
			 return _e('There is something wrong with the server','ccpw');
		}
	}
	
		function generate_html($coin,$type,$display_logo,$display_changes,$ticker_position,$header_ticker_position,$ticker_speed,$back_color,$font_color,$custom_css){
			$coin_html='';
			$coin_name = $coin->name;
				$coin_id = $coin->id;
				$coin_symbol =$coin->symbol;
                $coin_slug = strtolower($coin_name);
                $coin_price = '<i class="fa fa-usd" aria-hidden="true"></i>' . $coin->price_usd;
                $percent_change_24h = $coin->percent_change_24h . '%';
                $change_sign ='<i class="fa fa-arrow-up" aria-hidden="true"></i>';
                $change_class = "up";
             	  $change_sign_minus = "-";
                $coin_link = "https://coinmarketcap.com/currencies/".$coin_id;
             	
             	if($type=="ticker"){
             		$coin_icon='https://files.coinmarketcap.com/static/img/coins/32x32/'. $coin_id . '.png';
            	 }else{
            	 	$coin_icon='https://files.coinmarketcap.com/static/img/coins/64x64/'. $coin_id . '.png';
				 }

                if ( strpos( $coin->percent_change_24h, $change_sign_minus ) !==false) {
                    $change_sign = '<i class="fa fa-arrow-down" aria-hidden="true"></i>';
                    $change_class = "down";
                }

				if($type=="ticker"){

				$coin_html.=  '<li id="'.esc_attr( $coin_id).'">';
						$coin_html.=  '<div class="coin-container">';

						if($display_logo){
							$coin_html.='<span class="ccpw_icon"><img src="'.$coin_icon.'" /></span>';
							}

								
						$coin_html.='<span class="name">' . $coin_name.'('.$coin_symbol.')</span>
						<span class="price">' . $coin_price . '</span>';

						if($display_changes){
						$coin_html.='<span class="changes '.$change_class.'">';
						$coin_html.=$change_sign.$percent_change_24h ;

						$coin_html.= '</span>';

						}
						$coin_html.='</div></li>';
					}
					else{
					$coin_html.=  '<tr  id="'.esc_attr( $coin_id ).'">';
		
						
						$coin_html.='<td>';

						if($display_logo){
						 $coin_html.='<div class="ccpw_icon ccpw_coin_logo"><img alt="'. $coin_name.'" src="'.$coin_icon.'" /></div>';
						}	
						$coin_html.='<div class="ccpw_coin_info">
						<span class="name">' . $coin_name.'</span>
						<span class="coin_symbol">('.$coin_symbol.')</span>
						</div></td>
						<td class="price"><div class="price-value">' . $coin_price . '</div></td>';
						if($display_changes){
						$coin_html.='<td>';
						$coin_html.='<span class="changes '.$change_class.'">';
						$coin_html.=$change_sign.$percent_change_24h ;
						$coin_html.= '</span>';
						$coin_html.='</td>';
						}
						$coin_html.='</tr>';
						}	

				return $coin_html;
		
		}	

		/**
		 * Fetching Coins data from coin market cap and creating cache for 1 hours
		 */

		function get_api_data($limit){
		  $coinslist= get_transient( 'ccpw-coins' );
        if( empty( $coinslist ) ) {
          	$request = wp_remote_get( 'https://api.coinmarketcap.com/v1/ticker/?limit='.$limit );
			if( is_wp_error( $request ) ) {
				return false; 
			}
			$body = wp_remote_retrieve_body( $request );
			$coinslist = json_decode( $body );
			if( ! empty( $coinslist ) ) {
			 set_transient( 'ccpw-coins', $coinslist, HOUR_IN_SECONDS);
			}
		 }
		
		return $coinslist;		
		}


		/**
		 * Code you want to run when all other plugins loaded.
		 */
		public function init() {
			load_plugin_textdomain( 'wp-plugin-singleton', false, Crypto_Currency_Price_Widget_PATH . 'languages' );
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
	    if( !$spare_me ){
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
	  	  printf(__('<div class="ccpw-review wrap">You have been using <b> %s </b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !<div class="ccpw-review-btn"><a href="%s" class="button button-primary" target=
	            "_blank">Rate Now!</a><a href="%s" class="ccpw-review-done"> Already Done !</a></div></div>', $plugin_info['TextDomain']), $plugin_info['Name'], $reviewurl, $dont_disturb );
	       
	   // }
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
	         }else{
	         	_e('List Widget','ccpw');
	         }
	      	 break;
		   case 'shortcode' :
	            echo '<code>[ccpw id="'.$post_id.'"]</code>'; 
	            break;

	    }
	}

	function ccpw_admin_css(){

	}
	// remove the notice for the user if review already done or if the user does not want to
	function ccpw_spare_me(){    
	    if( isset( $_GET['spare_me'] ) && !empty( $_GET['spare_me'] ) ){
	        $spare_me = $_GET['spare_me'];
	        if( $spare_me == 1 ){
	            add_option( 'ccpw_spare_me' , TRUE );
	        }
	    }
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
