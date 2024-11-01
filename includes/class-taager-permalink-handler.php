<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Taager_Permalink_Handler {
    public function __construct() {
        add_action('init', array($this, 'set_default_permalink_structure'));
    }

    // Function to set the permalink structure to 'Day and Name'
    public function set_default_permalink_structure() {
        global $wp_rewrite;
        $desired_structure = '/%year%/%monthnum%/%day%/%postname%/';

        if (get_option('permalink_structure') !== $desired_structure) {
            update_option('permalink_structure', $desired_structure);
            $wp_rewrite->flush_rules(); // Ensure rewrite rules are regenerated
        }
    }
}