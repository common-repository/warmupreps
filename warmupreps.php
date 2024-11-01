<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://uniquehosting.net/
 * @since             1.0.0.0
 * @package           WarmupReps
 *
 * @wordpress-plugin
 * Plugin Name:       WarmupReps
 * Plugin URI:        https://uniquehosting.net/
 * Description:       Easily calculate and log your workout routines from popular strength training programs or create your own.
 * Version:           1.0.0.0
 * Author:            Chris Newell @ Unique Technologies
 * Author URI:        https://uniquetechnologies.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       warmupreps
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WARMUP_VERSION', '1.0.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-warmup-activator.php
 */
function uniwmp_activate_warmup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-warmup-activator.php';
	Warmup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-warmup-deactivator.php
 */
function uniwmp_deactivate_warmup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-warmup-deactivator.php';
	Warmup_Deactivator::deactivate();
}
function uniwmp_delete_all_warmup_data() {
	global $wpdb;
	$tables = array();
	$tables[] = $wpdb->prefix . 'wmp_workout';
	$tables[] = $wpdb->prefix . 'wmp_excercise';
	foreach($tables as $table){
    	$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	delete_option('wmp_default_data_imported');
}
register_activation_hook( __FILE__, 'uniwmp_activate_warmup' );
register_deactivation_hook( __FILE__, 'uniwmp_deactivate_warmup' );
register_uninstall_hook(__FILE__, 'uniwmp_delete_all_warmup_data' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-warmup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0.0
 */
function uniwmp_run_warmup() {

	$plugin = new Warmup();
	$plugin->run();

}
uniwmp_run_warmup();
