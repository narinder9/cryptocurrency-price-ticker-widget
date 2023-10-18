<?php
/**
 * Create response for datatable AJAX request
 */


function ccpw_get_ajax_data() {

	if ( isset($_REQUEST['nonce']) && ! wp_verify_nonce( sanitize_text_field($_REQUEST['nonce']), 'ccpwf-tbl-widget' ) ) {
		die( 'Please refresh window and check it again' );
	}

	$rtype                   = isset($_REQUEST['rtype']) ? sanitize_text_field($_REQUEST['rtype']): 0;
		$start_point         = isset($_REQUEST['start']) ? sanitize_text_field($_REQUEST['start']): 0;
		$data_length         = isset($_REQUEST['length']) ? sanitize_text_field($_REQUEST['length']): 10;
		$current_page        = isset($_REQUEST['draw']) && (int) $_REQUEST['draw'] ? sanitize_text_field($_REQUEST['draw']): 1;
		$requiredCurrencies  = isset($_REQUEST['requiredCurrencies']) ? ccpw_set_default_if_empty( sanitize_text_field($_REQUEST['requiredCurrencies']), 10 ) : 10;
		$fiat_currency       = isset($_REQUEST['currency']) ? sanitize_text_field($_REQUEST['currency']): 'USD';
		$fiat_currency_rate  = isset($_REQUEST['currencyRate']) ? sanitize_text_field($_REQUEST['currencyRate']): 1;
		$coin_no             = $start_point + 1;
		$coins_list          = array();
		$order_col_name      = 'market_cap';
		$order_type          = 'DESC';
		$DB                  = new ccpw_database();
		$Total_DBRecords     = '1000';
		$coins_request_count = $data_length + $start_point;
			 $api = get_option('ccpw_options');
            $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];
	if ( $rtype == 'top' ) {
		$coindata = $DB->get_coins(
			array(
				'number'  => $data_length,
				'offset'  => $start_point,
				'orderby' => $order_col_name,
				'order'   => $order_type,
			)
		);
	} else {
		$coinslist = isset($_REQUEST['coinslist']) ? $_REQUEST['coinslist'] : array();
		$coindata  = $DB->get_coins(
			array(
				'coin_id' => $coinslist,
				'offset'  => $start_point,
				'number'  => $data_length,
				'orderby' => $order_col_name,
				'order'   => $order_type,
			)
		);
	}

		  $coin_ids = array();
	if ( $coindata ) {
		foreach ( $coindata as $coin ) {
				 $coin_ids[] = $coin->coin_id;
		}
	}

		$response      = array();
		$coins         = array();
		$bitcoin_price = get_transient( 'ccpw_btc_price' );
		$coins_list    = array();

	if ( $coindata ) {

		foreach ( $coindata as $coin ) {
			$coin          = (array) $coin;
			$coins['rank'] = $coin_no;
			$coins['id']   = $coin['coin_id'];
			 if ($api == "coin_paprika") {
				$coins['id'] = ccpw_coin_array($coin['coin_id']);

			 }
			if ( ccpw_get_coin_logo( $coin['coin_id'], $size = 32 ) == false ) {
				$coins['logo'] = '<img  alt="' . esc_attr( $coin['name'] ) . '" src="' . $coin['logo'] . '">';
			} else {
				$coins['logo'] = ccpw_get_coin_logo( $coin['coin_id'] );
			}
				$coins['symbol'] = strtoupper( $coin['symbol'] );
				$coins['name']   = strtoupper( $coin['name'] );
				$coins['price']  = $coin['price'];
			if ( $fiat_currency == 'USD' ) {
				$coins['price']        = $coin['price'];
				$coins['market_cap']   = $coin['market_cap'];
				
				
                    $coins['total_volume'] = $coin['total_volume'];
                
				
				$c_price               = $coin['price'];
			} else {
				$coins['price']        = $coin['price'] * $fiat_currency_rate;
				$coins['market_cap']   = $coin['market_cap'] * $fiat_currency_rate;
				
				
					$coins['total_volume'] = $coin['total_volume'] * $fiat_currency_rate;
                
				//$coins['total_volume'] = $coin['total_volume'] * $fiat_currency_rate;
			}
				$coins['change_percentage_24h'] = number_format( $coin['percent_change_24h'], 2, '.', '' );
				// $coins['market_cap'] = $coin['market_cap'];
				// $coins['total_volume'] = $coin['total_volume'];
				$coins['supply'] = $coin['circulating_supply'];

				$coin_no++;
				$coins_list[] = $coins;

		}   //end of foreach-block
	}   //end of if-block

		$response = array(
			'draw'            => $current_page,
			'recordsTotal'    => $Total_DBRecords,
			'recordsFiltered' => $requiredCurrencies,
			'data'            => $coins_list,
		);
		echo json_encode( $response );
}
