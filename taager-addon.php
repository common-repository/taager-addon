<?php
/**
 * Plugin Name: Taager Addon
 * Description: Manage WooCommerce Shipping Zones And Provinces for Taager.com.
 * Author: Taager
 * Version: 1.9.0
 * Text Domain: taager-addon
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the main Taager_Checkout_Customizer class if WooCommerce is active.
function taager_check_dependencies() {
    if (class_exists('WooCommerce')) {
        include_once plugin_dir_path(__FILE__) . 'includes/class-taager-checkout-customizer.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-taager-permalink-handler.php';  // Include permalink handler

        new Taager_Checkout_Customizer();
        new Taager_Permalink_Handler();  // Instantiate the permalink handler
    } else {
        add_action('admin_notices', 'taager_display_woocommerce_notice');
    }
}
add_action('plugins_loaded', 'taager_check_dependencies');

// Display an admin notice if WooCommerce is not active.
function taager_display_woocommerce_notice() {
    echo '<div class="notice notice-error is-dismissible"><p>Taager Addon requires WooCommerce to be installed and active.</p></div>';
}

// Clear the cache when the plugin is deactivated or uninstalled.
register_deactivation_hook(__FILE__, 'taager_clear_cache');
register_uninstall_hook(__FILE__, 'taager_clear_cache');

function taager_clear_cache() {
    delete_transient('taager_api_data');
}