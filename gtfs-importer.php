<?php
/**
 * Plugin Name: GTFS Importer for WpBusTicketly
 * Description: Imports GTFS data and integrates with WpBusTicketly to create interactive timetables, journey planners, and fare calculations.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: gtfs-importer
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('GTFS_IMPORTER_VERSION', '1.0.0');
define('GTFS_IMPORTER_PATH', plugin_dir_path(__FILE__));
define('GTFS_IMPORTER_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once GTFS_IMPORTER_PATH . 'includes/class-gtfs-importer.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('GTFS_Importer', 'activate'));
register_deactivation_hook(__FILE__, array('GTFS_Importer', 'deactivate'));

// Start the plugin
function run_gtfs_importer() {
    $plugin = new GTFS_Importer();
    $plugin->run();
}
run_gtfs_importer();
