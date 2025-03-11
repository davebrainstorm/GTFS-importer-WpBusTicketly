<?php
/**
 * Provides the admin dashboard view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get plugin settings
$settings = get_option('gtfs_importer_settings', array());

// Get GTFS feeds
global $wpdb;
$feeds_table = $wpdb->prefix . 'gtfs_feeds';
$feeds = array();

if ($wpdb->get_var("SHOW TABLES LIKE '$feeds_table'") === $feeds_table) {
    $feeds = $wpdb->get_results("SELECT * FROM $feeds_table ORDER BY imported_on DESC", ARRAY_A);
}

// Count data from various tables
$stats = array(
    'agencies' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_agencies"),
    'routes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_routes"),
    'stops' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_stops"),
    'trips' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_trips"),
    'stop_times' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_stop_times"),
    'calendar' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_calendar"),
    'fare_rules' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_fare_rules")
);

// Get WpBusTicketly integration stats
$integration_stats = array(
    'routes_mapped' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_wpbusticketly_mapping WHERE gtfs_entity_type = 'route'"),
    'stops_mapped' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_wpbusticketly_mapping WHERE gtfs_entity_type = 'stop'"),
    'schedules_mapped' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_wpbusticketly_mapping WHERE gtfs_entity_type = 'trip' OR gtfs_entity_type = 'schedule'"),
    'fares_mapped' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gtfs_wpbusticketly_mapping WHERE gtfs_entity_type = 'fare'")
);
?>

<div class="wrap gtfs-importer-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gtfs-admin-notices">
        <?php settings_errors('gtfs_importer_notices'); ?>
    </div>

    <div class="gtfs-dashboard-header">
        <div class="gtfs-dashboard-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=gtfs-importer-import')); ?>" class="button button-primary">
                <?php _e('Import New GTFS Feed', 'gtfs-importer'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gtfs-importer-settings')); ?>" class="button">
                <?php _e('Plugin Settings', 'gtfs-importer'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gtfs-importer-integration')); ?>" class="button">
                <?php _e('WpBusTicketly Integration', 'gtfs-importer'); ?>
            </a>
        </div>
    </div>

    <div class="gtfs-dashboard-stats">
        <h2><?php _e('GTFS Data Overview', 'gtfs-importer'); ?></h2>
        
        <div class="gtfs-stats-grid">
            <div class="gtfs-stats-card">
                <h3><?php _e('Agencies', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['agencies']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Routes', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['routes']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Stops', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['stops']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Trips', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['trips']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Stop Times', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['stop_times']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Calendar Entries', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['calendar']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Fare Rules', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($stats['fare_rules']); ?></div>
            </div>
        </div>
    </div>

    <div class="gtfs-integration-stats">
        <h2><?php _e('WpBusTicketly Integration', 'gtfs-importer'); ?></h2>
        
        <div class="gtfs-stats-grid">
            <div class="gtfs-stats-card">
                <h3><?php _e('Routes Mapped', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($integration_stats['routes_mapped']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Stops Mapped', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($integration_stats['stops_mapped']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Schedules Mapped', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($integration_stats['schedules_mapped']); ?></div>
            </div>
            
            <div class="gtfs-stats-card">
                <h3><?php _e('Fares Mapped', 'gtfs-importer'); ?></h3>
                <div class="stats-number"><?php echo intval($integration_stats['fares_mapped']); ?></div>
            </div>
        </div>
    </div>

    <div class="gtfs-feeds-section">
        <h2><?php _e('Imported GTFS Feeds', 'gtfs-importer'); ?></h2>
        
        <?php if (empty($feeds)) : ?>
            <div class="gtfs-no-feeds">
                <p><?php _e('No GTFS feeds have been imported yet.', 'gtfs-importer'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=gtfs-importer-import')); ?>" class="button button-primary">
                    <?php _e('Import a GTFS Feed', 'gtfs-importer'); ?>
                </a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped gtfs-feeds-table">
                <thead>
                    <tr>
                        <th><?php _e('Feed Name', 'gtfs-importer'); ?></th>
                        <th><?php _e('Source', 'gtfs-importer'); ?></th>
                        <th><?php _e('Imported On', 'gtfs-importer'); ?></th>
                        <th><?php _e('Valid From', 'gtfs-importer'); ?></th>
                        <th><?php _e('Valid Until', 'gtfs-importer'); ?></th>
                        <th><?php _e('Status', 'gtfs-importer'); ?></th>
                        <th><?php _e('Actions', 'gtfs-importer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feeds as $feed) : ?>
                        <tr>
                            <td><?php echo esc_html($feed['name']); ?></td>
                            <td><?php echo empty($feed['url']) ? __('Uploaded File', 'gtfs-importer') : esc_url($feed['url']); ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($feed['imported_on'])); ?></td>
                            <td><?php echo !empty($feed['valid_from']) ? date_i18n(get_option('date_format'), strtotime($feed['valid_from'])) : '—'; ?></td>
                            <td><?php echo !empty($feed['valid_until']) ? date_i18n(get_option('date_format'), strtotime($feed['valid_until'])) : '—'; ?></td>
                            <td>
                                <span class="gtfs-status gtfs-status-<?php echo sanitize_html_class($feed['status']); ?>">
                                    <?php echo esc_html(ucfirst($feed['status'])); ?>
                                </span>
                            </td>
                            <td class="gtfs-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gtfs-importer-integration&feed_id=' . $feed['id'])); ?>" class="button button-small">
                                    <?php _e('Map Data', 'gtfs-importer'); ?>
                                </a>
                                
                                <button class="button button-small gtfs-validate-feed" data-feed-id="<?php echo intval($feed['id']); ?>">
                                    <?php _e('Validate', 'gtfs-importer'); ?>
                                </button>
                                
                                <button class="button button-small gtfs-delete-feed" data-feed-id="<?php echo intval($feed['id']); ?>">
                                    <?php _e('Delete', 'gtfs-importer'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
