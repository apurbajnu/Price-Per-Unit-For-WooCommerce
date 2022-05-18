<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              apurba.me
 * @since             1.0.0
 * @package           Price_Per_Unit_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Price Per Unit
 * Plugin URI:        bestdecoders.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.3.3
 * Author:            Apurba
 * Author URI:        apurba.me gs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       price-per-unit-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PRICE_PER_UNIT_FOR_WOOCOMMERCE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-price-per-unit-for-woocommerce-activator.php
 */
function activate_price_per_unit_for_woocommerce()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-price-per-unit-for-woocommerce-activator.php';
    Price_Per_Unit_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-price-per-unit-for-woocommerce-deactivator.php
 */
function deactivate_price_per_unit_for_woocommerce()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-price-per-unit-for-woocommerce-deactivator.php';
    Price_Per_Unit_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_price_per_unit_for_woocommerce');
register_deactivation_hook(__FILE__, 'deactivate_price_per_unit_for_woocommerce');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-price-per-unit-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_price_per_unit_for_woocommerce()
{
    $plugin = new Price_Per_Unit_For_Woocommerce();
    $plugin->run();
}
run_price_per_unit_for_woocommerce();
