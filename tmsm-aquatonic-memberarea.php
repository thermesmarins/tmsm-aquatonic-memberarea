<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.github.com/thermesmarins/tmsm-aquatonic-memberarea/
 * @since             1.0.0
 * @package           TmsmAquatonicMemberarea
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM Aquatonic Memberarea
 * Plugin URI:        https://www.github.com/thermesmarins/tmsm-aquatonic-memberarea/
 * Description:       TAquatonic Member Area
 * Version:           1.0.0
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * Text Domain:       tmsm-aquatonic-memberarea
 * Domain Path:       /Languages
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Github Plugin URI: https://www.github.com/thermesmarins/tmsm-aquatonic-memberarea/
 * Github Branch:     master
 */

// In strict mode, only a variable of exact type of the type declaration will be accepted.
declare(strict_types=1);

namespace TmsmAquatonicMemberarea;

use TmsmAquatonicMemberarea\Includes\Activator;
use TmsmAquatonicMemberarea\Includes\Deactivator;
use TmsmAquatonicMemberarea\Includes\Updater;
use TmsmAquatonicMemberarea\Includes\Main;

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit;

// Autoloader
require_once plugin_dir_path(__FILE__) . 'Autoloader.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TMSM_AQUATONIC_MEMBERAREA_VERSION', '1.0.0');

/**
 * The string used to uniquely identify this plugin.
 */
define('TMSM_AQUATONIC_MEMBERAREA_SLUG', 'tmsm-aquatonic-memberarea');

/**
 * Configuration data
 *  - db-version:   Start with 0 and increment by 1. It should not be updated with every plugin update,
 *                  only when database update is required.
 */
$configuration = array(
    'version'       => TMSM_AQUATONIC_MEMBERAREA_VERSION,
    'db-version'    => 0
);

/**
 * The ID for the configuration options in the database.
 */
$configurationOptionName = TMSM_AQUATONIC_MEMBERAREA_SLUG . '-configuration';
    
/**
 * The code that runs during plugin activation.
 * This action is documented in Includes/Activator.php
 */
register_activation_hook(__FILE__, function($networkWide) use($configuration, $configurationOptionName) {Activator::activate($networkWide, $configuration, $configurationOptionName);});

/**
 * Run the activation code when a new site is created.
 */
add_action('wpmu_new_blog', function($blogId) {Activator::activateNewSite($blogId);});

/**
 * The code that runs during plugin deactivation.
 * This action is documented in Includes/Deactivator.php
 */
register_deactivation_hook(__FILE__, function($networkWide) {Deactivator::deactivate($networkWide);});

/**
 * Update the plugin.
 * It runs every time, when the plugin is started.
 */
add_action('plugins_loaded', function() use ($configuration, $configurationOptionName) {Updater::update($configuration['db-version'], $configurationOptionName);}, 1);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function runPlugin(): void
{
    $plugin = new Main();
    $plugin->run();
}
runPlugin();
