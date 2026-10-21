<?php
/**
 * Plugin Name: Savika News Radio Directory
 * Plugin URI: https://savikanews.com/
 * Description: Dedicated online radio directory plugin for Savika News. Manages live radio station listings, public directory, broadcaster applications, stream testing, and HTML5 audio playback.
 * Version: 1.0.1
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

define( 'SNRD_VERSION', '1.0.1' );
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

        // Ensure pages exist on admin init if missing
        add_action( 'admin_init', array( $this, 'check_and_create_pages' ) );

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
     * Automatically ensure /radio/ and /radio/apply/ pages exist
     */
    public function check_and_create_pages() {
        if ( ! get_option( 'snrd_pages_auto_created' ) ) {
            self::auto_create_radio_pages();
            update_option( 'snrd_pages_auto_created', '1' );
        }
    }

    /**
     * Auto Create WordPress Pages and Seed Starter Stations
     */
    public static function auto_create_radio_pages() {
        // 1. Create Main Radio Directory Page: /radio/
        $directory_page = get_page_by_path( 'radio' );
        $directory_id   = 0;

        if ( ! $directory_page ) {
            $directory_id = wp_insert_post( array(
                'post_title'     => 'Radio',
                'post_name'      => 'radio',
                'post_content'   => '[savika_radio_directory]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ) );
        } else {
            $directory_id = $directory_page->ID;
            // Ensure shortcode is present
            if ( false === strpos( $directory_page->post_content, '[savika_radio_directory]' ) ) {
                wp_update_post( array(
                    'ID'           => $directory_id,
                    'post_content' => '[savika_radio_directory]',
                    'post_status'  => 'publish',
                ) );
            }
        }

        // 2. Create Apply Page: /radio/apply/
        $apply_page = get_page_by_path( 'radio/apply' );
        if ( ! $apply_page ) {
            // Check flat 'apply' just in case
            $apply_flat = get_page_by_path( 'apply' );
            if ( ! $apply_flat ) {
                wp_insert_post( array(
                    'post_title'     => 'Apply to Broadcast',
                    'post_name'      => 'apply',
                    'post_parent'    => $directory_id,
                    'post_content'   => '[savika_radio_apply]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed',
                ) );
            }
        }

        // 3. Seed starter sample stations if empty
        self::seed_sample_stations();

        flush_rewrite_rules();
    }

    /**
     * Seed initial sample Ghanaian radio stations so the directory is not empty
     */
    public static function seed_sample_stations() {
        $existing = get_posts( array(
            'post_type'      => SNRD_POST_TYPE,
            'posts_per_page' => 1,
            'post_status'    => 'any',
        ) );

        if ( ! empty( $existing ) ) {
            return; // Stations already exist, do not duplicate
        }

        $starter_stations = array(
            array(
                'title'       => 'Kingdom FM 106.1 FM',
                'location'    => 'Mankessim, Central Region, Ghana',
                'genre'       => 'News',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Kingdom FM 106.1 is an authoritative radio station broadcasting from Mankessim, Ghana, delivering the best in news, current affairs, sports, and community discussions.',
            ),
            array(
                'title'       => 'Savika Gospel Radio',
                'location'    => 'Accra, Ghana',
                'genre'       => 'Gospel',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Savika Gospel Radio brings you uplifting praise, worship, inspiring sermons, and spiritual programs 24 hours a day.',
            ),
            array(
                'title'       => 'Savika Sports & Talk',
                'location'    => 'Kumasi, Ghana',
                'genre'       => 'Sports',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Live Ghanaian sports coverage, Ghana Premier League highlights, transfer talk, and international football debates.',
            )
        );

        foreach ( $starter_stations as $st ) {
            $post_id = wp_insert_post( array(
                'post_title'   => $st['title'],
                'post_content' => $st['description'],
                'post_status'  => 'publish',
                'post_type'    => SNRD_POST_TYPE,
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'snrd_location', $st['location'] );
                update_post_meta( $post_id, 'snrd_stream_url', $st['stream_url'] );
                update_post_meta( $post_id, 'snrd_zeno_url', $st['zeno_url'] );
                update_post_meta( $post_id, 'snrd_website_url', home_url( '/' ) );
                update_post_meta( $post_id, 'snrd_contact_email', get_option( 'admin_email' ) );

                // Set genre term
                $term = get_term_by( 'name', $st['genre'], SNRD_TAXONOMY_GENRE );
                if ( $term ) {
                    wp_set_object_terms( $post_id, array( $term->term_id ), SNRD_TAXONOMY_GENRE );
                }
            }
        }
    }

    /**
     * Activation logic
     */
    public function activate() {
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-post-type.php';
        SNRD_Post_Type::register_post_type();
        SNRD_Post_Type::register_taxonomy();
        SNRD_Post_Type::insert_default_genres();

        // Auto create pages and seed initial stations
        self::auto_create_radio_pages();
        update_option( 'snrd_pages_auto_created', '1' );

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
