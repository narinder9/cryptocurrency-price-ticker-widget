<?php
/**
 * Plugin Name:Cryptocurrency Price Ticker Widget
 * Description:Cryptocurrency price ticker widget WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc. using CoinMarketCap API.
 * Author:Cool Timeline Team
 * Author URI:https://www.cooltimeline.com/
 * Version: 1.3.1
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

define( 'Crypto_Currency_Price_Widget_VERSION', '1.3.1' );
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
		add_shortcode( 'ccpw', array( $this, 'ccpw_shortcode' ));
		add_filter( 'rwmb_meta_boxes', array( $this, 'ccpw') );
		add_action( 'save_post', array( $this,'save_ccpw_shortcode'),10, 3 );

		add_action( 'init',  array( $this,'ccpw_post_type') );	
		add_action( 'cmb2_admin_init', array( $this,'cmb2_ccpw_metaboxes' ));
		add_action( 'add_meta_boxes', array( $this,'register_ccpw_meta_box') );
		
		add_action( 'wp_footer', array($this,'ticker_in_footer') );
		add_action( 'wp_footer', array($this,'ccpw_enable_ticker') );

		if(is_admin()){
		add_action( 'admin_init',array($this,'check_installation_time'));
		add_action( 'admin_init',array($this,'ccpw_spare_me'), 5 );
		add_action('admin_enqueue_scripts', array($this,'ccpw_load_scripts'));

		add_action( 'add_meta_boxes_ccpw',array($this,'ccpw_add_meta_boxes'));

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
		 * (Update path to use cmb2 or CMB2, depending on the name of the folder.
		 * Case-sensitive is important on some systems.)
		 */
		require_once __DIR__ . '/cmb2/init.php';
	}
	// shortcode
	function ccpw_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id'  => '',
		'class' => ''
	), $atts, 'ccpw' );

	$post_id=$atts['id'];

	// Grab the metadata from the database
	$type = get_post_meta($post_id,'type', true );
	$ticker_position = get_post_meta($post_id,'ticker_position', true );
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
	$dynamic_styles="";
	$bg_color=!empty($back_color)? "background-color:".$back_color.";":"background-color:#fff;";
	$fnt_color=!empty($font_color)? "color:".$font_color.";":"color:#000;";
	$dynamic_styles.=".tickercontainer{".$bg_color."} .ccpw-widget{".$bg_color."} .ccpw-widget .ccpw_table tr{".$bg_color."}  
                      span.name{".$fnt_color."}	span.price{".$fnt_color."} .price-value{".$fnt_color."} ";
	$dynamic_styles.=$custom_css;
	$styles="<style type='text/css'>".$dynamic_styles."</style>";

	wp_enqueue_style('ccpw-font-awesome','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

	if($type=="ticker"){
	wp_enqueue_script('ccpw_marque_js',Crypto_Currency_Price_Widget_URL. 'assets/marquee/jquery.webticker.min.js', null, null, 'all');
	 wp_add_inline_script('ccpw_marque_js', 'jQuery(document).ready(function($){
	 	$(".ccpw-ticker-cont ul").each(function(index){
	 		var id="#"+$(this).attr("id");
	 			$(id).webTicker({
	 				speed:'.$t_speed.',
	 				moving:true,
	 				 duplicate:true,
	 				 hoverpause:true,
	 				  startEmpty:false,
	 				
	 			});
	 		});
	 	});' );
	}

	wp_enqueue_style('ppcw-styles', Crypto_Currency_Price_Widget_URL. 'assets/css/ppcw-styles.css', array(), null, null, 'all');
	$all_coin_data=$this->get_api_data(15);

		if( ! empty( $all_coin_data ) && is_array($all_coin_data)) {
			
			if(is_array($display_currencies)&& !empty($display_currencies)){

				foreach( $all_coin_data as $coin ) {
				if(in_array($coin->id,$display_currencies)){
					$crypto_html .=$this->generate_html($coin,$type,$display_logo,$display_changes,$ticker_position,$ticker_speed,$back_color,$font_color,$custom_css);
				}
			}	
			}else{
				  return _e('You have not selected any currencies to display','ccpw');
			}	

			 if ($type=="ticker"){

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
			
         	$id="ccpw-ticker-".rand(1,100);
			$output .= '<div style="display:none" class="ccpw-container ccpw-ticker-cont '.$container_cls.'"><ul  id="'.$id.'">';
			$output .= $crypto_html;
			$output .= '</ul></div>';

			}else if($type=="list-widget"){
			$cls='ccpw-widget';
				
			$output .= '<div class="'.$cls.'"><table class="ccpw_table" style="border:none!important;"><tbody>';
			$output .= $crypto_html;
			$output .= '</tbody></table></div>';
				
			}
            
			return $output.$styles;	
		 
		}else{
			 return _e('There is something wrong with the server','ccpw');
		}
	}
	
		function generate_html($coin,$type,$display_logo,$display_changes,$ticker_position,$ticker_speed,$back_color,$font_color,$custom_css){
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
             	
             	$coin_icon='https://files.coinmarketcap.com/static/img/coins/32x32/'. $coin_id . '.png';

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
				
						//if($display_logo){
						//$coin_html.='<td class image-coin>';	
						if($display_logo){
							$coin_html.='<td class="image-coin">';
							$coin_html.='<div class="ccpw_icon"><img src="'.$coin_icon.'" /></div>';
							$coin_html.='</td>';
							}
						
						$coin_html.='<td><span class="name">' . $coin_name.'('.$coin_symbol.')</span></td>
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

	

	// Register Custom Post Type
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
    'desc'    => 'Select the option where you want to display ticker',
   'id'      => 'ticker_position',
   'type'    => 'radio_inline',
  'attributes' => array(
          'required' => true
     ),
   
   'options' => array(
       'header'   => __( 'Header', 'cmb2' ),
       'footer'   => __( 'Footer', 'cmb2' ),
        'shortcode' => __( 'Anywhere', 'cmb2' ),
   ),
   'default' => 'no',
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

    // Add other metaboxes as needed

}


public	function register_ccpw_meta_box()
	{
	    add_meta_box( 'ccpw-shortcode', 'Crypto Currency Price shortcode',array($this,'p_shortcode_meta'), 'ccpw', 'side', 'high' );
	}

	public	function p_shortcode_meta()
		{ 
	    $id = get_the_ID();
	    $dynamic_attr='';
	    echo '<p>Paste this shortcode in anywhere (page/post)</p>'; 

	   $element_type = get_post_meta( $id, 'pp_type', true );
	  // $element_layout = get_post_meta( $id, 'pp_layout', true );

	   $dynamic_attr.="[ccpw id=\"{$id}\"";
	   
	 //  $dynamic_attr.=$element_type?" type=\"$element_type\"":" type=\"default\"";
	 // $dynamic_attr.=$element_layout?" layout=\"$element_layout\"":" type=\"default\"";
	   $dynamic_attr.=']';
	    ?>
	    <input type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr) ;?>" readonly/>
	   	
	    <?php 
	}

	/**
	 * Save shortcode when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
function save_ccpw_shortcode( $post_id, $post, $update ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $post_type = get_post_type($post_id);

    // If this isn't a 'book' post, don't update it.
    if ( "ccpw" != $post_type ) return;
	    // - Update the post's metadata.
    	$ticker_position = get_post_meta($post_id,'ticker_position', true );
    	if($ticker_position){
	    update_option('ccpw-p-id',$post_id);
	    update_option('ccpw-shortcode',"[ccpw id=".$post_id."]");
		}
		 delete_transient('ccpw-coins');
	}

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
	        $past_date = strtotime( '-7 days' );
	     
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

	function ccpw_add_meta_boxes( $post){
		 add_meta_box(
                'ccpw-feedback-section',
                __( 'Hopefully you are Happy with our plugin','ccpw'),
                array($this,'ccpw_right_section'),
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
		<li>  Different currency support -<strong> USD, INR, Pound, Euro, Yen, WON</strong></li> 
		<li>  Display market cap and volume of virtual crypto coins.</li> 
		<li>  Many advanced design options.</li> 
		<li><a target="_blank" href="http://cryptowidgetpro.coolplugins.net/">'.__('View Demos','ccpw').'</a></li>

		<li>
		<h3><a target="_blank" href="https://codecanyon.net/item/cryptocurrency-price-ticker-widget-pro-wordpress-plugin/21269050?ref=CoolHappy">'.__('Buy Now','ccpw').'</a></h3></li>
		</ol>
		<br>
		
		 <iframe src="https://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fcryptowidget.coolplugins.net&width=122&layout=button&action=like&size=large&show_faces=false&share=true&height=65&appId=1798381030436021" width="122" height="65" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe></div>';
        echo $pro_add ;

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
