<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

class GTFS_Importer_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Performs necessary cleanup when the plugin is deactivated.
     * Note: This is different from uninstallation - data is preserved.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear any scheduled cron jobs
        wp_clear_scheduled_hook('gtfs_importer_daily_update');
        wp_clear_scheduled_hook('gtfs_importer_realtime_update');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        if (WP_DEBUG) {
            error_log('GTFS Importer plugin deactivated');
        }
        
        // Update plugin status in options
        $settings = get_option('gtfs_importer_settings', array());
        $settings['plugin_active'] = false;
        $settings['deactivated_time'] = current_time('mysql');
        update_option('gtfs_importer_settings', $settings);
    }
}
