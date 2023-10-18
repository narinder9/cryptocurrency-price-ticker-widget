<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('CCPW_api_data')) {
    class CCPW_api_data
    {

        /**
         * CCPW_API_ENDPOINT
         *
         * Holds the URL of the coins data API.
         *
         * @access public
         *
         */

        const COINGECKO_API_ENDPOINT = 'https://api.coingecko.com/api/v3/';
        /**
         * OPENEXCHANGERATE_API_ENDPOINT
         *
         * Holds the URL of the openexchangerates API.
         *
         * @access public
         *
         */

        const OPENEXCHANGERATE_API_ENDPOINT = 'https://openexchangerates.org/api/latest.json?app_id=';

        public function __construct()
        {
            // self::CMC_API_ENDPOINT = 'https://apiv3.coinexchangeprice.com/v3/';
        }

        /*
        |-----------------------------------------------------------
        | Fetching data through CoinGecko API and save in database
        |-----------------------------------------------------------
        | MUST NOT CALL THIS FUNCTION DIRECTLY.
        |-----------------------------------------------------------
         */
        public function ccpw_get_coin_gecko_data()
        {
            $update_api_name = 'ccpw-active-api';
            $data_cache_name = 'ccpw-saved-coindata';
            $activate_api = get_transient($update_api_name);
            $cache = get_transient($data_cache_name);
            $settings = get_option('ccpw_options');

            $cache_time = (!isset($settings['select_cache_time']) && empty($settings['select_cache_time'])) ? 5 : $settings['select_cache_time'];
            $cache_time = (int) $cache_time;

            // Avoid updating database if cache exist and same API is requested
            if ($activate_api == 'CoinGecko' && false != $cache) {
                return;
            }

            $coins = array();
            $api_url = self::COINGECKO_API_ENDPOINT . 'coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250&page=1&sparkline=false';

            $request = wp_remote_get($api_url, array('timeout' => 120, 'sslverify' => false));
            if (is_wp_error($request)) {
                return false; // Bail early
            }
            $body = wp_remote_retrieve_body($request);
            $coins = json_decode($body);
            $response = array();
            $coins_data = array();

            if (isset($coins) && $coins != "" && is_array($coins)) {
                foreach ($coins as $coin) {
                    $response['coin_id'] = $coin->id;
                    $response['rank'] = $coin->market_cap_rank;
                    $response['name'] = $coin->name;
                    $response['symbol'] = strtoupper($coin->symbol);
                    $response['price'] = ccpw_set_default_if_empty($coin->current_price, 0.00);
                    $response['percent_change_24h'] = ccpw_set_default_if_empty($coin->price_change_percentage_24h, 0);
                    $response['market_cap'] = ccpw_set_default_if_empty($coin->market_cap, 0);
                    $response['total_volume'] = ccpw_set_default_if_empty($coin->total_volume);
                    $response['circulating_supply'] = ccpw_set_default_if_empty($coin->circulating_supply);
                    $response['logo'] = $coin->image;
                    $coins_data[] = $response;
                }
                $DB = new ccpw_database();
                $DB->create_table();
                $DB->ccpw_insert($coins_data);
                

                set_transient($data_cache_name, date('H:s:i'), $cache_time * MINUTE_IN_SECONDS);
               // set_transient('ccpw_data', 'CCPW_EXPIRY_TIME', $cache_time * MINUTE_IN_SECONDS);
                set_transient($update_api_name, 'CoinGecko', 0);

            }

        }


        public function ccpw_get_coin_paprika_data(){
            $update_api_name = 'ccpw-active-api';
            $data_cache_name = 'ccpw-saved-coindata';
            $activate_api = get_transient($update_api_name);
            $cache = get_transient($data_cache_name);
            $settings = get_option('ccpw_options');
            $cache_time = (!isset($settings['select_cache_time']) && empty($settings['select_cache_time'])) ? 5 : $settings['select_cache_time'];
            $cache_time = (int) $cache_time;

            // Avoid updating database if cache exist and same API is requested
            if ($activate_api == 'CoinPaprika' && false != $cache) {
                return;
            }

            
            $api_url = 'https://api.coinpaprika.com/v1/tickers';
            $request = wp_remote_get(
                $api_url,
                array(
                    'timeout' => 120,
                    'sslverify' => false,
                )
            );
            if (is_wp_error($request)) {
                return false; // Bail early
            }
            $body = wp_remote_retrieve_body($request);
            $coin_info = json_decode($body, true);
            $response = array();
            $coins_data = array();
            $coin_info = array_slice($coin_info, 0, 250);

            if (is_array($coin_info) && !empty($coin_info)) {
                foreach ($coin_info as $coin) {
                    $response['coin_id'] = $coin['id'];
                    $response['rank'] = $coin['rank'];
                    $response['name'] = $coin['name'];
                    $response['symbol'] = strtoupper($coin['symbol']);
                    $response['price'] = ccpw_set_default_if_empty($coin['quotes']['USD']['price'], 0.00);
                    //$response['percent_change_1h'] = ccpw_set_default_if_empty($coin['quotes']['USD']['percent_change_1h']);
                    $response['percent_change_24h'] = ccpw_set_default_if_empty($coin['quotes']['USD']['percent_change_24h']);
                    // $response['percent_change_7d'] = ccpw_set_default_if_empty($coin['quotes']['USD']['percent_change_7d']);
                    // $response['percent_change_30d'] = ccpw_set_default_if_empty($coin['quotes']['USD']['percent_change_30d']);
                    //   $response['high_24h'] = 'N/A';
                    //  $response['low_24h'] = 'N/A';
                    $response['market_cap'] = ccpw_set_default_if_empty($coin['quotes']['USD']['market_cap'], 0);
                    $response['total_volume'] = 'N/A';
                    //$response['total_supply'] = ccpw_set_default_if_empty($coin['total_supply']);
                    $response['circulating_supply'] = ccpw_set_default_if_empty($coin['circulating_supply']);
                    //$response['7d_chart'] = 'N/A';
                    $response['logo'] = 'N/A';
                    $response['last_updated'] = gmdate('Y-m-d h:i:s');
                    $coins_data[] = $response;
                }

            
            

               
                    $DB = new ccpw_database();
                    $DB->ccpw_insert($coins_data);
                    set_transient($data_cache_name, date('H:s:i'), $cache_time * MINUTE_IN_SECONDS);
                    set_transient($update_api_name, 'CoinPaprika', 0);
                    

                    
                    
                
                
            }

            

               
        }

        
        







        /*
        |-------------------------------------------------------------------------
        |    USD conversions
        |-------------------------------------------------------------------------
         */

        public function ccpw_usd_conversions($currency)
        {
            // use common transient between cmc and ccpw
            $conversions = get_transient('cmc_usd_conversions');
            $conversions_option = get_option('cmc_usd_conversions');

            if (empty($conversions) || $conversions === "" || empty($conversions_option)) {
                $api_option = get_option("openexchange-api-settings");
                $api = (!empty($api_option['openexchangerate_api'])) ? $api_option['openexchangerate_api'] : "";
                $request = "";
                if (empty($api)) {
                    if (!empty($conversions_option)) {
                        if ($currency == "all") {

                            return $conversions_option;

                        } else {
                            if (isset($conversions_option[$currency])) {
                                return $conversions_option[$currency];
                            }
                        }
                    }
                    return false;

                } else {
                    $request = wp_remote_get(self::OPENEXCHANGERATE_API_ENDPOINT . $api . '', array('timeout' => 120, 'sslverify' => true));

                }

                if (is_wp_error($request)) {
                    return false;
                }

                $currency_ids = array("USD", "AUD", "BRL", "CAD", "CZK", "DKK", "EUR", "HKD", "HUF", "ILS", "INR", "JPY", "MYR", "MXN", "NOK", "NZD", "PHP", "PLN", "GBP", "SEK", "CHF", "TWD", "THB", "TRY", "CNY", "KRW", "RUB", "SGD", "CLP", "IDR", "PKR", "ZAR");
                $body = wp_remote_retrieve_body($request);
                $conversion_data = json_decode($body);

                if (isset($conversion_data->rates)) {
                    $conversion_data = (array) $conversion_data->rates;
                } else {
                    $conversion_data = array();
                    if (!empty($conversions_option)) {
                        if ($currency == "all") {

                            return $conversions_option;

                        } else {
                            if (isset($conversions_option[$currency])) {
                                return $conversions_option[$currency];
                            }
                        }
                    }
                }

                if (is_array($conversion_data) && count($conversion_data) > 0) {
                    foreach ($conversion_data as $key => $currency_price) {
                        if (in_array($key, $currency_ids)) {
                            $conversions_option[$key] = $currency_price;
                        }

                    }

                    uksort($conversions_option, function ($key1, $key2) use ($currency_ids) {
                        return (array_search($key1, $currency_ids) > array_search($key2, $currency_ids)) ? 1 : -1;
                    });

                    update_option('cmc_usd_conversions', $conversions_option);
                    set_transient('cmc_usd_conversions', $conversions_option, 12 * HOUR_IN_SECONDS);
                }
            }

            if ($currency == "all") {

                return $conversions_option;

            } else {
                if (isset($conversions_option[$currency])) {
                    return $conversions_option[$currency];
                }
            }
        }

    }
}
