<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/includes
 * @author     Apurba <apurba.jnu@gmail.com>
 */
class Price_Per_Unit_For_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'price-per-unit-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
