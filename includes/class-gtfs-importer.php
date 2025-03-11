<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

class GTFS_Importer {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      GTFS_Importer_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('GTFS_IMPORTER_VERSION')) {
            $this->version = GTFS_IMPORTER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'gtfs-importer';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
        $this->define_integration_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - GTFS_Importer_Loader. Orchestrates the hooks of the plugin.
     * - GTFS_Importer_i18n. Defines internationalization functionality.
     * - GTFS_Importer_Admin. Defines all hooks for the admin area.
     * - GTFS_Importer_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once GTFS_IMPORTER_PATH . 'includes/class-loader.php';

        // The class responsible for defining internationalization functionality of the plugin.
        require_once GTFS_IMPORTER_PATH . 'includes/class-i18n.php';
        
        // The class responsible for defining all actions that occur in the admin area.
        require_once GTFS_IMPORTER_PATH . 'admin/class-admin.php';

        // The class responsible for defining all actions that occur in the public-facing side of the site.
        require_once GTFS_IMPORTER_PATH . 'public/class-public.php';
        
        // Load API controller
        require_once GTFS_IMPORTER_PATH . 'api/class-api-controller.php';
        
        // Load GTFS data models
        $this->load_data_models();
        
        // Load processors
        $this->load_processors();
        
        // Load integration classes
        $this->load_integration_classes();

        $this->loader = new GTFS_Importer_Loader();
    }
    
    /**
     * Load GTFS data model classes
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_data_models() {
        $models = array(
            'agency',
            'route',
            'stop',
            'trip',
            'stop-time',
            'fare',
            'shape'
        );
        
        foreach ($models as $model) {
            require_once GTFS_IMPORTER_PATH . 'data-models/class-' . $model . '.php';
        }
    }
    
    /**
     * Load processor classes
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_processors() {
        $processors = array(
            'file-processor',
            'import-processor',
            'geocoding',
            'fare-calculator'
        );
        
        foreach ($processors as $processor) {
            require_once GTFS_IMPORTER_PATH . 'processors/class-' . $processor . '.php';
        }
    }
    
    /**
     * Load integration classes
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_integration_classes() {
        // WpBusTicketly integration
        $wpbusticketly_classes = array(
            'route-mapper',
            'stop-mapper',
            'schedule-mapper',
            'fare-mapper'
        );
        
        foreach ($wpbusticketly_classes as $class) {
            require_once GTFS_IMPORTER_PATH . 'integration/wpbusticketly/class-' . $class . '.php';
        }
        
        // Realtime integration
        $realtime_classes = array(
            'api-connector',
            'realtime-updater',
            'alert-manager'
        );
        
        foreach ($realtime_classes as $class) {
            require_once GTFS_IMPORTER_PATH . 'integration/realtime/class-' . $class . '.php';
        }
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the GTFS_Importer_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new GTFS_Importer_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new GTFS_Importer_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add settings link on plugins page
        $this->loader->add_filter('plugin_action_links_' . GTFS_IMPORTER_BASENAME, $plugin_admin, 'add_action_links');
        
        // Register AJAX handlers
        $this->loader->add_action('wp_ajax_gtfs_import_file', $plugin_admin, 'handle_import_file');
        $this->loader->add_action('wp_ajax_gtfs_validate_feed', $plugin_admin, 'handle_validate_feed');
        $this->loader->add_action('wp_ajax_gtfs_map_data', $plugin_admin, 'handle_map_data');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new GTFS_Importer_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Register shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
        
        // Register widgets
        $this->loader->add_action('widgets_init', $plugin_public, 'register_widgets');
    }
    
    /**
     * Register all of the hooks related to the API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $plugin_api = new GTFS_Importer_API_Controller($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
    }
    
    /**
     * Register all of the hooks related to the integration functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_integration_hooks() {
        // Add integration hooks here
        // These will be specific to WpBusTicketly integration
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    GTFS_Importer_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
