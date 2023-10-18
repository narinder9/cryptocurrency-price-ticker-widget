<?php

if ( ! class_exists( 'CCPW_Review_Notice' ) ) {
	class CCPW_Review_Notice {

		const PLUGIN        = 'Cryptocurrency Widgets';
		const SLUG          = 'ccpw';
		const LOGO          = CCPWF_URL . 'assets/crypto-widget.png';
		const SPARE_ME      = 'ccpw_spare_me';
		const ACTIVATE_TIME = 'ccpw_activation_time';
		const REVIEW_LINK   = 'https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post';
		const AJAX_REQUEST  = 'ccpw_dismiss_notice';

		public function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'display_review_notice' ) );
				add_action( 'wp_ajax_' . self::AJAX_REQUEST, array( $this, 'dismiss_review_notice' ) );
			}
		}

		public function dismiss_review_notice() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ccpw-nonce' ) ) {
				wp_die( 'Permission denied.' );
			}
			update_option( self::SPARE_ME, 'yes' );
			wp_send_json_success();
		}

		public function display_review_notice() {
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}
		
			$spare_me_option = get_option( self::SPARE_ME );
		
			if ( $spare_me_option === 'yes' ) {
				return;
			}
		
			$installation_date = get_option( self::ACTIVATE_TIME );
		
			if ( ! $installation_date ) {
				return;
			}
		
			$install_date = new DateTime( $installation_date );
			$current_date = new DateTime();
			$diff_days    = $install_date->diff( $current_date )->days;
		
			if ( $diff_days >= 3 ) {
				echo $this->create_notice_content();
			}
		}		

		public function create_notice_content() {
			ob_start();
			?>
			<div data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" 
				data-ajax-callback="<?php echo esc_attr( self::AJAX_REQUEST ); ?>" 
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'ccpw-nonce' ) ); ?>" 
				class="<?php echo self::SLUG; ?>-feedback-notice-wrapper notice notice-info">
				<div class="logo_container">
					<a href="<?php echo esc_url( self::REVIEW_LINK ); ?>" target="_blank">
						<img src="<?php echo esc_url( self::LOGO ); ?>" alt="<?php echo esc_attr( self::PLUGIN ); ?>" style="max-width:80px;">
					</a>
				</div>
				<div class="message_container">
					<?php
					echo "Thanks for using the <b>" . self::PLUGIN . "</b> WordPress plugin! We hope you liked it. ";
					echo "Please take a moment to rate it - your feedback encourages us to create more <a href='https://coolplugins.net/?utm_source=ccpw_plugin&utm_medium=inside&utm_campaign=review&utm_content=review-notice' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";
					?>
					<div class="callto_action">
						<a href="<?php echo esc_url( self::REVIEW_LINK ); ?>" class="button button-primary" target="_blank" title="Rate Now! ★★★★★">Rate Now! ★★★★★</a>
						<a href="javascript:void(0);" class="<?php echo self::SLUG; ?>_dismiss_notice" title="Dismiss this notice.">I already rated it</a>
						<a href="javascript:void(0);" class="<?php echo self::SLUG; ?>_dismiss_notice" title="Dismiss this notice.">Not interested</a>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_clean();

			$style = '<style>
				.' . self::SLUG . '-feedback-notice-wrapper.notice.notice-info {
					padding: 5px;
					display: table;
					width: fit-content;
					max-width: 855px;
					clear: both;
					border-radius: 5px;
					border: 1px solid #b7bfc7;
				}
				.' . self::SLUG . '-feedback-notice-wrapper .logo_container {
					width: 85px;
					display: table-cell;
					padding: 5px;
					vertical-align: middle;
				}
				.' . self::SLUG . '-feedback-notice-wrapper .logo_container a,
				.' . self::SLUG . '-feedback-notice-wrapper .logo_container img {
					width: fit-content;
					height: auto;
					display: block;
				}
				.' . self::SLUG . '-feedback-notice-wrapper .message_container {
					display: table-cell;
					padding: 5px;
					vertical-align: middle;
				}
				.' . self::SLUG . '-feedback-notice-wrapper .callto_action {
					display: flex;
					flex-flow: row wrap;
					align-items: center;
					margin: 5px 0;
					gap: 20px;
				}
				.' . self::SLUG . '-feedback-notice-wrapper a.ccpw_dismiss_notice:after {
					color: #e86011;
					content: "\f153";
					display: inline-block;
					vertical-align: middle;
					margin-left: 5px;
					font-size: 15px;
					font-family: dashicons;
				}
				/* Additional styles if needed */
			</style>';

			$script = '<script>
				jQuery(document).ready(function ($) {
					$(".' . self::SLUG . '_dismiss_notice").on("click", function (event) {
						var $this = $(this);
						var wrapper = $this.closest(".' . self::SLUG . '-feedback-notice-wrapper");
						var ajaxURL = wrapper.data("ajax-url");
						var ajaxCallback = wrapper.data("ajax-callback");
						var ajaxNonce = wrapper.data("nonce");
						$.post(ajaxURL, { 
							"action": ajaxCallback,
							"nonce": ajaxNonce
						}, function( response ) {
							if (response !== undefined) {
								wrapper.slideUp("fast");
							}
						}, "json");
					});
				});
			</script>';

			return $style . $html . $script;
		}
	}

	new CCPW_Review_Notice();
}