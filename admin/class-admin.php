<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the admin area and the dashboard.
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin
 * @author     Your Name <email@example.com>
 */
class GTFS_Importer_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            GTFS_IMPORTER_URL . 'admin/assets/css/gtfs-importer-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            GTFS_IMPORTER_URL . 'admin/assets/js/gtfs-importer-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        
        // Add localized script data for the admin JS
        wp_localize_script(
            $this->plugin_name,
            'gtfs_importer_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gtfs_importer_admin_nonce'),
                'import_nonce' => wp_create_nonce('gtfs_import_nonce'),
                'strings' => array(
                    'import_running' => __('Import is running, please do not navigate away from this page...', 'gtfs-importer'),
                    'import_success' => __('Import completed successfully!', 'gtfs-importer'),
                    'import_error' => __('An error occurred during import.', 'gtfs-importer'),
                    'confirm_delete' => __('Are you sure you want to delete this feed? This action cannot be undone.', 'gtfs-importer')
                )
            )
        );
    }

    /**
     * Add menu items to the admin dashboard.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu item
        add_menu_page(
            __('GTFS Importer', 'gtfs-importer'),
            __('GTFS Importer', 'gtfs-importer'),
            'manage_options',
            'gtfs-importer',
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-cart',
            26
        );
        
        // Dashboard submenu
        add_submenu_page(
            'gtfs-importer',
            __('Dashboard', 'gtfs-importer'),
            __('Dashboard', 'gtfs-importer'),
            'manage_options',
            'gtfs-importer',
            array($this, 'display_plugin_admin_dashboard')
        );
        
        // Import submenu
        add_submenu_page(
            'gtfs-importer',
            __('Import GTFS', 'gtfs-importer'),
            __('Import GTFS', 'gtfs-importer'),
            'manage_options',
            'gtfs-importer-import',
            array($this, 'display_plugin_import_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'gtfs-importer',
            __('Settings', 'gtfs-importer'),
            __('Settings', 'gtfs-importer'),
            'manage_options',
            'gtfs-importer-settings',
            array($this, 'display_plugin_settings_page')
        );
        
        // WpBusTicketly Integration submenu
        add_submenu_page(
            'gtfs-importer',
            __('WpBusTicketly Integration', 'gtfs-importer'),
            __('WpBusTicketly Integration', 'gtfs-importer'),
            'manage_options',
            'gtfs-importer-integration',
            array($this, 'display_plugin_integration_page')
        );
    }

    /**
     * Add action links to the plugins page.
     *
     * @since    1.0.0
     * @param    array    $links    Existing action links.
     * @return   array              Modified action links.
     */
    public function add_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=gtfs-importer-settings') . '">' . __('Settings', 'gtfs-importer') . '</a>',
            '<a href="' . admin_url('admin.php?page=gtfs-importer-import') . '">' . __('Import', 'gtfs-importer') . '</a>'
        );
        
        return array_merge($plugin_links, $links);
    }

    /**
     * Render the dashboard page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        require_once GTFS_IMPORTER_PATH . 'admin/partials/gtfs-importer-admin-dashboard.php';
    }

    /**
     * Render the import page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_import_page() {
        require_once GTFS_IMPORTER_PATH . 'admin/partials/gtfs-importer-admin-import.php';
    }

    /**
     * Render the settings page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        require_once GTFS_IMPORTER_PATH . 'admin/partials/gtfs-importer-admin-settings.php';
    }

    /**
     * Render the WpBusTicketly integration page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_integration_page() {
        require_once GTFS_IMPORTER_PATH . 'admin/partials/gtfs-importer-admin-integration.php';
    }

    /**
     * AJAX handler for importing GTFS files.
     *
     * @since    1.0.0
     */
    public function handle_import_file() {
        // Check nonce for security
        check_ajax_referer('gtfs_import_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'gtfs-importer')));
        }
        
        $import_type = isset($_POST['import_type']) ? sanitize_text_field($_POST['import_type']) : 'upload';
        $feed_name = isset($_POST['feed_name']) ? sanitize_text_field($_POST['feed_name']) : date('Y-m-d H:i:s');
        
        // Load the import processor
        $import_processor = new GTFS_Importer_Import_Processor();
        
        try {
            switch ($import_type) {
                case 'upload':
                    // Handle file upload
                    if (!isset($_FILES['gtfs_file'])) {
                        throw new Exception(__('No file was uploaded.', 'gtfs-importer'));
                    }
                    
                    $file = $_FILES['gtfs_file'];
                    
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception(__('Error uploading file. Please try again.', 'gtfs-importer'));
                    }
                    
                    $result = $import_processor->process_uploaded_file($file, $feed_name);
                    break;
                    
                case 'url':
                    // Handle URL import
                    $url = isset($_POST['feed_url']) ? esc_url_raw($_POST['feed_url']) : '';
                    
                    if (empty($url)) {
                        throw new Exception(__('Please enter a valid URL.', 'gtfs-importer'));
                    }
                    
                    $result = $import_processor->process_remote_file($url, $feed_name);
                    break;
                    
                case 'ftp':
                    // Handle FTP import
                    $server = isset($_POST['ftp_server']) ? sanitize_text_field($_POST['ftp_server']) : '';
                    $username = isset($_POST['ftp_username']) ? sanitize_text_field($_POST['ftp_username']) : '';
                    $password = isset($_POST['ftp_password']) ? $_POST['ftp_password'] : '';
                    $path = isset($_POST['ftp_path']) ? sanitize_text_field($_POST['ftp_path']) : '';
                    
                    $result = $import_processor->process_ftp_file($server, $username, $password, $path, $feed_name);
                    break;
                
                default:
                    throw new Exception(__('Invalid import type.', 'gtfs-importer'));
            }
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
        
        wp_die();
    }

    /**
     * AJAX handler for validating GTFS feeds.
     *
     * @since    1.0.0
     */
    public function handle_validate_feed() {
        // Check nonce for security
        check_ajax_referer('gtfs_import_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'gtfs-importer')));
        }
        
        $feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;
        
        if (empty($feed_id)) {
            wp_send_json_error(array('message' => __('Invalid feed ID.', 'gtfs-importer')));
        }
        
        try {
            $import_processor = new GTFS_Importer_Import_Processor();
            $validation_result = $import_processor->validate_feed($feed_id);
            
            wp_send_json_success($validation_result);
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
        
        wp_die();
    }

    /**
     * AJAX handler for mapping GTFS data to WpBusTicketly.
     *
     * @since    1.0.0
     */
    public function handle_map_data() {
        // Check nonce for security
        check_ajax_referer('gtfs_import_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'gtfs-importer')));
        }
        
        $feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;
        $entity_type = isset($_POST['entity_type']) ? sanitize_text_field($_POST['entity_type']) : '';
        
        if (empty($feed_id) || empty($entity_type)) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'gtfs-importer')));
        }
        
        try {
            switch ($entity_type) {
                case 'routes':
                    $mapper = new GTFS_Importer_Route_Mapper();
                    $result = $mapper->map_routes($feed_id);
                    break;
                    
                case 'stops':
                    $mapper = new GTFS_Importer_Stop_Mapper();
                    $result = $mapper->map_stops($feed_id);
                    break;
                    
                case 'schedules':
                    $mapper = new GTFS_Importer_Schedule_Mapper();
                    $result = $mapper->map_schedules($feed_id);
                    break;
                    
                case 'fares':
                    $mapper = new GTFS_Importer_Fare_Mapper();
                    $result = $mapper->map_fares($feed_id);
                    break;
                    
                default:
                    throw new Exception(__('Invalid entity type.', 'gtfs-importer'));
            }
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
        
        wp_die();
    }
}
