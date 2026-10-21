<?php
/**
 * Plugin Name: Savika News Radio Directory
 * Plugin URI: https://savikanews.com/
 * Description: Dedicated online radio directory plugin for Savika News. Manages live radio station listings, public directory, broadcaster applications, stream testing, and HTML5 audio playback.
 * Version: 1.0.0
 * Author: Savika News
 * Author URI: https://savikanews.com/
 * Text Domain: savika-news-radio
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'SNRD_VERSION', '1.0.0' );
define( 'SNRD_PLUGIN_FILE', __FILE__ );
define( 'SNRD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SNRD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SNRD_POST_TYPE', 'snrd_station' );
define( 'SNRD_TAXONOMY_GENRE', 'snrd_genre' );

/**
 * Main Plugin Class
 */
final class Savika_News_Radio_Directory {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Main Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required class files
     */
    private function load_dependencies() {
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-post-type.php';
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-admin.php';
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-application-form.php';
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-shortcodes.php';
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-templates.php';
    }

    /**
     * Hook into WordPress
     */
    private function init_hooks() {
        // Activation / Deactivation hooks
        register_activation_hook( SNRD_PLUGIN_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( SNRD_PLUGIN_FILE, array( $this, 'deactivate' ) );

        // Enqueue frontend scripts & styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Initialize sub-components
        add_action( 'plugins_loaded', array( $this, 'init_components' ) );
    }

    /**
     * Initialize sub-classes
     */
    public function init_components() {
        SNRD_Post_Type::init();
        SNRD_Admin::init();
        SNRD_Application_Form::init();
        SNRD_Shortcodes::init();
        SNRD_Templates::init();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_assets() {
        wp_register_style(
            'snrd-public-css',
            SNRD_PLUGIN_URL . 'assets/css/snrd-public.css',
            array(),
            SNRD_VERSION
        );

        wp_register_script(
            'snrd-public-js',
            SNRD_PLUGIN_URL . 'assets/js/snrd-public.js',
            array( 'jquery' ),
            SNRD_VERSION,
            true
        );

        wp_localize_script( 'snrd-public-js', 'snrdConfig', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'strings'   => array(
                'playing'      => __( 'Playing', 'savika-news-radio' ),
                'paused'       => __( 'Paused', 'savika-news-radio' ),
                'connecting'   => __( 'Connecting...', 'savika-news-radio' ),
                'streamError'  => __( 'Stream unavailable. Try official page.', 'savika-news-radio' ),
                'noStations'   => __( 'No radio stations found matching your criteria.', 'savika-news-radio' )
            )
        ) );
    }

    /**
     * Activation logic
     */
    public function activate() {
        // Register post type and taxonomy before flushing rewrite rules
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-post-type.php';
        SNRD_Post_Type::register_post_type();
        SNRD_Post_Type::register_taxonomy();
        SNRD_Post_Type::insert_default_genres();
        flush_rewrite_rules();
    }

    /**
     * Deactivation logic
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Instantiate plugin
Savika_News_Radio_Directory::instance();
