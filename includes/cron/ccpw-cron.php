<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('CCPW_cronjob')) {
    class CCPW_cronjob
    {

        public function __construct()
        {
            // update database only if required.
            add_action('init', array($this, 'ccpw_cron_coins_autoupdater'));
            // register cron jobs
            add_filter('cron_schedules', array($this, 'ccpw_cron_schedules'));
            add_action('ccpw_coins_autosave', array($this, 'ccpw_cron_coins_autoupdater'));

        }

        /**
         * Cron status schedule(s).
         */
        public function ccpw_cron_schedules($schedules)
        {
            // 5 minute schedule for grabing all coins
            if (!isset($schedules['5min'])) {
                $schedules['5min'] = array(
                    'interval' => 5 * 60,
                    'display' => __('Once every 5 minutes'),
                );
            }
            return $schedules;
        }

        /*
        |-----------------------------------------------------------
        |   This will update database after specific interval
        |-----------------------------------------------------------
        |   Always use this function to update database
        |-----------------------------------------------------------
         */
        public function ccpw_cron_coins_autoupdater()
        {
            $api = get_option('ccpw_options');
            $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];
            $api_obj = new CCPW_api_data();
            $data = ($api == "coin_gecko") ? $api_obj->ccpw_get_coin_gecko_data() : $api_obj->ccpw_get_coin_paprika_data();


        }

    }

    $cron_init = new CCPW_cronjob();
}
