<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://gabrielcastillo.net
 * @since             1.0.0
 * @package           Afm_Woo_Customer_Export
 *
 * @wordpress-plugin
 * Plugin Name:       NCGASA - Export Custom Subscriptions List
 * Plugin URI:        https://gabrielcastillo.net
 * Description:       Custom Plugin for NCGASA, export subscribers.
 * Version:           1.0.0
 * Author:            Gabriel Castillo
 * Author URI:        https://gabrielcastillo.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       afm-woo-customer-export
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


//set_time_limit(90);


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AFM_WOO_CUSTOMER_EXPORT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-afm-woo-customer-export-activator.php
 */
function activate_afm_woo_customer_export() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-afm-woo-customer-export-activator.php';
	Afm_Woo_Customer_Export_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-afm-woo-customer-export-deactivator.php
 */
function deactivate_afm_woo_customer_export() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-afm-woo-customer-export-deactivator.php';
	Afm_Woo_Customer_Export_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_afm_woo_customer_export' );
register_deactivation_hook( __FILE__, 'deactivate_afm_woo_customer_export' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-afm-woo-customer-export.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_afm_woo_customer_export() {

	$plugin = new Afm_Woo_Customer_Export();
	$plugin->run();

}
run_afm_woo_customer_export();
