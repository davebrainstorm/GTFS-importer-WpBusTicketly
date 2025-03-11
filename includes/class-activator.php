<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 * It creates the necessary database tables for storing GTFS data.
 *
 * @since      1.0.0
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

class GTFS_Importer_Activator {

    /**
     * Activate the plugin.
     *
     * Creates all necessary database tables for storing GTFS data
     * and sets up initial plugin options.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::create_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule cron events
        if (!wp_next_scheduled('gtfs_importer_daily_update')) {
            wp_schedule_event(time(), 'daily', 'gtfs_importer_daily_update');
        }
        
        if (!wp_next_scheduled('gtfs_importer_realtime_update')) {
            wp_schedule_event(time(), 'hourly', 'gtfs_importer_realtime_update');
        }
    }
    
    /**
     * Create database tables for GTFS data.
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create tables based on GTFS specification
        $sql = array();
        
        // Agencies table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_agencies (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            agency_id VARCHAR(255) NULL,
            agency_name VARCHAR(255) NOT NULL,
            agency_url VARCHAR(255) NOT NULL,
            agency_timezone VARCHAR(100) NOT NULL,
            agency_lang VARCHAR(20) NULL,
            agency_phone VARCHAR(50) NULL,
            agency_fare_url VARCHAR(255) NULL,
            agency_email VARCHAR(255) NULL,
            PRIMARY KEY (id),
            KEY agency_id (agency_id),
            KEY feed_id (feed_id)
        ) $charset_collate;";
        
        // Stops table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_stops (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            stop_id VARCHAR(255) NOT NULL,
            stop_code VARCHAR(100) NULL,
            stop_name VARCHAR(255) NOT NULL,
            stop_desc TEXT NULL,
            stop_lat DECIMAL(10,6) NOT NULL,
            stop_lon DECIMAL(10,6) NOT NULL,
            zone_id VARCHAR(100) NULL,
            stop_url VARCHAR(255) NULL,
            location_type TINYINT UNSIGNED NULL,
            parent_station VARCHAR(255) NULL,
            stop_timezone VARCHAR(100) NULL,
            wheelchair_boarding TINYINT UNSIGNED NULL,
            level_id VARCHAR(255) NULL,
            platform_code VARCHAR(255) NULL,
            PRIMARY KEY (id),
            KEY stop_id (stop_id),
            KEY feed_id (feed_id),
            KEY location_type (location_type),
            KEY parent_station (parent_station)
        ) $charset_collate;";
        
        // Routes table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_routes (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            route_id VARCHAR(255) NOT NULL,
            agency_id VARCHAR(255) NULL,
            route_short_name VARCHAR(255) NULL,
            route_long_name VARCHAR(255) NULL,
            route_desc TEXT NULL,
            route_type TINYINT UNSIGNED NOT NULL,
            route_url VARCHAR(255) NULL,
            route_color VARCHAR(6) NULL,
            route_text_color VARCHAR(6) NULL,
            route_sort_order INT NULL,
            continuous_pickup TINYINT UNSIGNED NULL,
            continuous_drop_off TINYINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY route_id (route_id),
            KEY feed_id (feed_id),
            KEY agency_id (agency_id)
        ) $charset_collate;";
        
        // Trips table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_trips (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            route_id VARCHAR(255) NOT NULL,
            service_id VARCHAR(255) NOT NULL,
            trip_id VARCHAR(255) NOT NULL,
            trip_headsign VARCHAR(255) NULL,
            trip_short_name VARCHAR(255) NULL,
            direction_id TINYINT UNSIGNED NULL,
            block_id VARCHAR(255) NULL,
            shape_id VARCHAR(255) NULL,
            wheelchair_accessible TINYINT UNSIGNED NULL,
            bikes_allowed TINYINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY trip_id (trip_id),
            KEY feed_id (feed_id),
            KEY route_id (route_id),
            KEY service_id (service_id),
            KEY shape_id (shape_id)
        ) $charset_collate;";
        
        // Stop times table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_stop_times (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            trip_id VARCHAR(255) NOT NULL,
            arrival_time VARCHAR(8) NULL,
            departure_time VARCHAR(8) NULL,
            stop_id VARCHAR(255) NOT NULL,
            stop_sequence INT UNSIGNED NOT NULL,
            stop_headsign VARCHAR(255) NULL,
            pickup_type TINYINT UNSIGNED NULL,
            drop_off_type TINYINT UNSIGNED NULL,
            shape_dist_traveled FLOAT NULL,
            timepoint TINYINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY trip_id (trip_id),
            KEY stop_id (stop_id),
            KEY arrival_time (arrival_time),
            KEY departure_time (departure_time)
        ) $charset_collate;";
        
        // Calendar table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_calendar (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            service_id VARCHAR(255) NOT NULL,
            monday TINYINT UNSIGNED NOT NULL,
            tuesday TINYINT UNSIGNED NOT NULL,
            wednesday TINYINT UNSIGNED NOT NULL,
            thursday TINYINT UNSIGNED NOT NULL,
            friday TINYINT UNSIGNED NOT NULL,
            saturday TINYINT UNSIGNED NOT NULL,
            sunday TINYINT UNSIGNED NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY service_id (service_id)
        ) $charset_collate;";
        
        // Calendar dates table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_calendar_dates (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            service_id VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            exception_type TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY service_id (service_id),
            KEY date (date)
        ) $charset_collate;";
        
        // Fare attributes table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_fare_attributes (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            fare_id VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            currency_type VARCHAR(3) NOT NULL,
            payment_method TINYINT UNSIGNED NOT NULL,
            transfers TINYINT UNSIGNED NULL,
            agency_id VARCHAR(255) NULL,
            transfer_duration INT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY fare_id (fare_id),
            KEY agency_id (agency_id)
        ) $charset_collate;";
        
        // Fare rules table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_fare_rules (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            fare_id VARCHAR(255) NOT NULL,
            route_id VARCHAR(255) NULL,
            origin_id VARCHAR(255) NULL,
            destination_id VARCHAR(255) NULL,
            contains_id VARCHAR(255) NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY fare_id (fare_id),
            KEY route_id (route_id)
        ) $charset_collate;";
        
        // Shapes table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_shapes (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            shape_id VARCHAR(255) NOT NULL,
            shape_pt_lat DECIMAL(10,6) NOT NULL,
            shape_pt_lon DECIMAL(10,6) NOT NULL,
            shape_pt_sequence INT UNSIGNED NOT NULL,
            shape_dist_traveled FLOAT NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY shape_id (shape_id),
            KEY shape_pt_sequence (shape_pt_sequence)
        ) $charset_collate;";
        
        // Frequencies table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_frequencies (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            trip_id VARCHAR(255) NOT NULL,
            start_time VARCHAR(8) NOT NULL,
            end_time VARCHAR(8) NOT NULL,
            headway_secs INT UNSIGNED NOT NULL,
            exact_times TINYINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY trip_id (trip_id)
        ) $charset_collate;";
        
        // Transfers table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_transfers (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            from_stop_id VARCHAR(255) NOT NULL,
            to_stop_id VARCHAR(255) NOT NULL,
            transfer_type TINYINT UNSIGNED NOT NULL,
            min_transfer_time INT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY from_stop_id (from_stop_id),
            KEY to_stop_id (to_stop_id)
        ) $charset_collate;";
        
        // Pathways table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_pathways (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            pathway_id VARCHAR(255) NOT NULL,
            from_stop_id VARCHAR(255) NOT NULL,
            to_stop_id VARCHAR(255) NOT NULL,
            pathway_mode TINYINT UNSIGNED NOT NULL,
            is_bidirectional TINYINT UNSIGNED NOT NULL,
            length FLOAT NULL,
            traversal_time INT UNSIGNED NULL,
            stair_count INT NULL,
            max_slope FLOAT NULL,
            min_width FLOAT NULL,
            signposted_as VARCHAR(255) NULL,
            reversed_signposted_as VARCHAR(255) NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY pathway_id (pathway_id),
            KEY from_stop_id (from_stop_id),
            KEY to_stop_id (to_stop_id)
        ) $charset_collate;";
        
        // Feed info table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_feed_info (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            feed_publisher_name VARCHAR(255) NOT NULL,
            feed_publisher_url VARCHAR(255) NOT NULL,
            feed_lang VARCHAR(20) NOT NULL,
            feed_start_date DATE NULL,
            feed_end_date DATE NULL,
            feed_version VARCHAR(255) NULL,
            feed_contact_email VARCHAR(255) NULL,
            feed_contact_url VARCHAR(255) NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id)
        ) $charset_collate;";
        
        // Main feeds table to track imported feeds
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_feeds (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            url VARCHAR(255) NULL,
            feed_type VARCHAR(50) NOT NULL DEFAULT 'static',
            imported_on DATETIME NOT NULL,
            valid_from DATE NULL,
            valid_until DATE NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            version VARCHAR(50) NULL,
            PRIMARY KEY (id),
            KEY feed_type (feed_type)
        ) $charset_collate;";
        
        // Integration mapping table for WpBusTicketly
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gtfs_wpbusticketly_mapping (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id BIGINT(20) UNSIGNED NOT NULL,
            gtfs_entity_type VARCHAR(50) NOT NULL,
            gtfs_entity_id VARCHAR(255) NOT NULL,
            wpbt_entity_type VARCHAR(50) NOT NULL,
            wpbt_entity_id BIGINT(20) UNSIGNED NOT NULL,
            mapping_data LONGTEXT NULL,
            created_on DATETIME NOT NULL,
            updated_on DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY gtfs_entity_type (gtfs_entity_type),
            KEY gtfs_entity_id (gtfs_entity_id),
            KEY wpbt_entity_type (wpbt_entity_type),
            KEY wpbt_entity_id (wpbt_entity_id)
        ) $charset_collate;";
        
        // Execute all SQL statements
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
    }
    
    /**
     * Create plugin options with default values.
     */
    private static function create_options() {
        // Add plugin version to the options table
        add_option('gtfs_importer_version', GTFS_IMPORTER_VERSION);
        
        // Add default settings
        $default_settings = array(
            'import_on_cron' => false,
            'keep_old_data' => true,
            'auto_map_data' => true,
            'realtime_enabled' => false,
            'wpbusticketly_integration' => true,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'distance_unit' => 'km',
            'fare_currency' => 'USD',
            'map_provider' => 'leaflet'
        );
        
        add_option('gtfs_importer_settings', $default_settings);
    }
}
