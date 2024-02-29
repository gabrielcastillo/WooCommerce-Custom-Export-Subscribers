<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://gabrielcastillo.net
 * @since      1.0.0
 *
 * @package    Afm_Woo_Customer_Export
 * @subpackage Afm_Woo_Customer_Export/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Afm_Woo_Customer_Export
 * @subpackage Afm_Woo_Customer_Export/includes
 * @author     Gabriel Castillo <gabriel@gabrielcastillo.net>
 */
class Afm_Woo_Customer_Export_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'afm-woo-customer-export',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
