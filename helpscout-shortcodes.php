<?php
/*
Plugin Name: Blocks (and Shortcodes) for HelpScout
Plugin URI: https://www.mattcromwell.com/
Description: Shortcodes to output HelpScout data into your WordPress website.
Version: 0.9.0
Author: Matt Cromwell
Author URI: https://www.mattcromwell.com
License: GPLv2 or later
Text Domain: bas4hs
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Constants
defined('BAS4HS_PATH') || define( 'BAS4HS_PATH', plugin_dir_path( __FILE__ ) );
defined('BAS4HS_URL') || define( 'BAS4HS_URL', plugin_dir_url( __FILE__ ) );
defined('BAS4HS_VERSION') || define( 'BAS4HS_VERSION', '0.9.0' );

/**
 * Load the textdomain of the plugin
 *
 * @return void
 */
function bas4hs_load_plugin_textdomain() {
    $locale = apply_filters( 'bas4hs_locale', get_locale(), 'bas4hs' );

    load_textdomain( 'bas4hs', trailingslashit( WP_PLUGIN_DIR ) . 'bas4hs' . '/languages/' . 'bas4hs' . '-' . $locale . '.mo' );
}

add_action( 'plugins_loaded', 'bas4hs_load_plugin_textdomain', 1 );

/**
 *  PHP Minimum Version Compatibility
 */
if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
    function bas4hs_deactivate() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    function bas4hs_show_deactivation_notice() {
        echo wp_kses_post(
            sprintf(
                '<div class="notice notice-error"><p>%s</p></div>',
                __( '"HelpScout Blocks (and Shortcodes) for WordPress" requires PHP 5.6 or newer to function well. Your environment does not meet that criteria and thus the plugin has been automatically deactivated. Please contact your webhost and request to have your PHP version updated to at least version 7.1.', 'bas4hs' )
            )
        );
    }
    add_action( 'admin_init', 'bas4hs_deactivate' );
    add_action( 'admin_notices', 'bas4hs_show_deactivation_notice' );
    // Return early to prevent loading the other includes.
    return;
}

/**
 * Get the settings of the plugin in a filterable way
 *
 * @return array
 */
function bas4hs_get_settings() {
    return apply_filters( 'pn_get_settings', get_option( 'bas4hs-settings' ) );
}

// Initialize all our necessary files upon activation

add_action( 'plugins_loaded', 'bas4hs_includes' );

function bas4hs_includes() {
    require_once( BAS4HS_PATH . 'includes/class-shortcodes.php');
}