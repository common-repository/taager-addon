<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Taager_Checkout_Customizer {
    // The API URL to fetch the countries and states data.
    private $api_url = 'https://stores.api.taager.com/stores/WOOCOMMERCE/zones';
    private $countries = array();
    private $states = array();
    private $error_message = '';

    public function __construct() {
        $this->fetch_data_from_api();
        add_filter('woocommerce_countries', array($this, 'taager_limit_countries'));
        add_filter('woocommerce_states', array($this, 'taager_limit_states'));
        add_action('admin_notices', array($this, 'taager_display_error_notice'));
    }

    private function fetch_data_from_api() {
        $cached_data = get_transient('taager_api_data');
        if ($cached_data !== false) {
            $this->process_api_data($cached_data);
            return;
        }

        $response = wp_remote_get(esc_url_raw($this->api_url)); // Sanitize URL for safety.
        if (is_wp_error($response)) {
            $this->error_message = 'Taager WooCommerce Plugin: Error fetching data from API. ' . esc_html($response->get_error_message()) . '. Please contact customer support.';
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error_message = 'Taager WooCommerce Plugin: Error decoding JSON response. ' . esc_html(json_last_error_msg()) . '. Please contact customer support.';
            return;
        }

        set_transient('taager_api_data', $data, HOUR_IN_SECONDS);
        $this->process_api_data($data);
    }

    private function process_api_data($data) {
        if (isset($data['countries'])) {
            foreach ($data['countries'] as $country) {
                $country_code = $this->convert_country_code($country['country']);
                $this->countries[$country_code] = $this->get_country_name($country_code);
                $this->states[$country_code] = array();

                foreach ($country['provinces'] as $province) {
                    $this->states[$country_code][$province['storeProvinceName']] = $province['storeProvinceName'];
                }
            }
        } else {
            $this->error_message = 'Taager WooCommerce Plugin: No countries data found in API response. Please contact customer support.';
        }
    }

    private function convert_country_code($code) {
        $mapping = array(
            'EGY' => 'EG',
            'IRQ' => 'IR',
            'SAU' => 'SA',
            'ARE' => 'AE',
            'JOR' => 'JO',
            'LBN' => 'LB',
            'MAR' => 'MA',
            'OMN' => 'OM',
            'QAT' => 'QA',
            'SYR' => 'SY',
            'TUN' => 'TN',
            'YEM' => 'YE'
        );
        return isset($mapping[$code]) ? $mapping[$code] : $code;
    }

    private function get_country_name($code) {
        $mapping = array(
            'EG' => 'Egypt',
            'IR' => 'Iraq',
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'JO' => 'Jordan',
            'LB' => 'Lebanon',
            'MA' => 'Morocco',
            'OM' => 'Oman',
            'QA' => 'Qatar',
            'SY' => 'Syria',
            'TN' => 'Tunisia',
            'YE' => 'Yemen'
        );
        return isset($mapping[$code]) ? $mapping[$code] : $code;
    }

    public function taager_limit_countries($countries) {
        return $this->countries;
    }

    public function taager_limit_states($states) {
        return $this->states;
    }

    public function taager_display_error_notice() {
        if (!empty($this->error_message)) {
            echo '<div class="notice notice-error"><p>' . esc_html($this->error_message) . '</p></div>';
        }
    }
}
