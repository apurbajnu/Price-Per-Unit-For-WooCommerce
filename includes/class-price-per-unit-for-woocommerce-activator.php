<?php

/**
 * Fired during plugin activation
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Price_Per_Unit_For_Woocommerce
 * @subpackage Price_Per_Unit_For_Woocommerce/includes
 * @author     Apurba <apurba.jnu@gmail.com>
 */
class Price_Per_Unit_For_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
		if(in_array('price_per_unit_pro/price-per-unit-pro-for-woocommerce.php',$all_plugins)){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'Pro Version Already Active.', 'price-per-unit-for-woocommerce' ) );
		}
		if (!in_array('woocommerce/woocommerce.php',$all_plugins)) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'Please Install and Activate the WooCommerce Plugin,before activating this plugin.', 'price-per-unit-for-woocommerce' ) );
		}
	}

}
