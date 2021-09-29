<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/nicomollet
 * @since             1.0.0
 * @package           Tmsm_Aquatonic_Memberarea
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM Aquatonic Memberarea
 * Plugin URI:        https://github.com/thermesmarins/tmsm-aquatonic-memberarea
 * Description:       Display Aquatonic Memberarea daily prices (best price) in a calendar view
 * Version:           1.1.6
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * Requires PHP:      5.6
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tmsm-aquatonic-memberarea
 * Domain Path:       /languages
 * Github Plugin URI: https://github.com/thermesmarins/tmsm-aquatonic-memberarea
 * Github Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TMSM_AQUATONIC_MEMBERAREA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tmsm-aquatonic-memberarea-activator.php
 */
function activate_tmsm_aquatonic_memberarea() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-memberarea-activator.php';
	Tmsm_Aquatonic_Memberarea_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tmsm-aquatonic-memberarea-deactivator.php
 */
function deactivate_tmsm_aquatonic_memberarea() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-memberarea-deactivator.php';
	Tmsm_Aquatonic_Memberarea_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tmsm_aquatonic_memberarea' );
register_deactivation_hook( __FILE__, 'deactivate_tmsm_aquatonic_memberarea' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-memberarea.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tmsm_aquatonic_memberarea() {

	$plugin = new Tmsm_Aquatonic_Memberarea();
	$plugin->run();

}
run_tmsm_aquatonic_memberarea();
