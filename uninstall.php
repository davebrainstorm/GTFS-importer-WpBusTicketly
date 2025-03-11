<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up all plugin data from the database when the plugin is uninstalled.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Get global wpdb class
global $wpdb;

// Delete all plugin options
$options = array(
    'gtfs_importer_version',
    'gtfs_importer_settings',
    'gtfs_importer_last_import',
    'gtfs_importer_import_status',
    'gtfs_importer_api_key',
    'gtfs_importer_realtime_feeds'
);

foreach ($options as $option) {
    delete_option($option);
    // For multisite compatibility
    delete_site_option($option);
}

// Delete all custom database tables
$tables = array(
    'gtfs_agencies',
    'gtfs_stops',
    'gtfs_routes',
    'gtfs_trips',
    'gtfs_stop_times',
    'gtfs_calendar',
    'gtfs_calendar_dates',
    'gtfs_fare_attributes',
    'gtfs_fare_rules',
    'gtfs_shapes',
    'gtfs_frequencies',
    'gtfs_transfers',
    'gtfs_feed_info',
    'gtfs_pathways',
    'gtfs_levels',
    'gtfs_translations',
    'gtfs_attributions'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
}

// Delete any transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_transient\_gtfs_importer\_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_transient\_timeout\_gtfs_importer\_%'");

// Delete scheduled events
wp_clear_scheduled_hook('gtfs_importer_daily_update');
wp_clear_scheduled_hook('gtfs_importer_realtime_update');
