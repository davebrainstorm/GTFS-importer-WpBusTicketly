<?php
/**
 * GTFS Importer for WpBusTicketly
 *
 * @package           GTFS_Importer
 * @author            Your Name
 * @copyright         2025 Your Name or Company
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       GTFS Importer for WpBusTicketly
 * Plugin URI:        https://example.com/gtfs-importer
 * Description:       Imports GTFS data and integrates with WpBusTicketly to create interactive timetables, journey planners, and fare calculations.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       gtfs-importer
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('GTFS_IMPORTER_VERSION', '1.0.0');
define('GTFS_IMPORTER_PATH', plugin_dir_path(__FILE__));
define('GTFS_IMPORTER_URL', plugin_dir_url(__FILE__));
define('GTFS_IMPORTER_BASENAME', plugin_basename(__FILE__));
define('GTFS_IMPORTER_ADMIN_URL', admin_url('admin.php?page=gtfs-importer'));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function activate_gtfs_importer() {
    require_once GTFS_IMPORTER_PATH . 'includes/class-activator.php';
    GTFS_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_gtfs_importer() {
    require_once GTFS_IMPORTER_PATH . 'includes/class-deactivator.php';
    GTFS_Importer_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_gtfs_importer');
register_deactivation_hook(__FILE__, 'deactivate_gtfs_importer');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once GTFS_IMPORTER_PATH . 'includes/class-gtfs-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_gtfs_importer() {
    $plugin = new GTFS_Importer();
    $plugin->run();
}
run_gtfs_importer();
