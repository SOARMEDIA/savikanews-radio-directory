<?php
/**
 * Plugin Name: Savika News Radio Directory
 * Plugin URI: https://savikanews.com/
 * Description: Dedicated online radio directory plugin for Savika News. Manages live radio station listings, public directory, broadcaster applications, stream testing, and HTML5 audio playback.
 * Version: 1.0.3
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

if ( ! defined( 'SNRD_VERSION' ) ) {
    define( 'SNRD_VERSION', '1.0.3' );
}
if ( ! defined( 'SNRD_PLUGIN_FILE' ) ) {
    define( 'SNRD_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'SNRD_PLUGIN_DIR' ) ) {
    define( 'SNRD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SNRD_PLUGIN_URL' ) ) {
    define( 'SNRD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SNRD_POST_TYPE' ) ) {
    define( 'SNRD_POST_TYPE', 'snrd_station' );
}
if ( ! defined( 'SNRD_TAXONOMY_GENRE' ) ) {
    define( 'SNRD_TAXONOMY_GENRE', 'snrd_genre' );
}

// Guard against duplicate class declaration if another folder/copy of the plugin is already loaded
if ( class_exists( 'Savika_News_Radio_Directory' ) ) {
    return;
}

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
        require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-seo.php';
    }

    /**
     * Hook into WordPress
     */
    private function init_hooks() {
        // Activation / Deactivation hooks bound to __FILE__
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Ensure pages exist on admin init if missing
        add_action( 'admin_init', array( $this, 'check_and_create_pages' ) );

        // Enqueue frontend scripts & styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Initialize sub-components directly so hooks are always registered immediately
        $this->init_components();
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
        SNRD_SEO::init();
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

        wp_register_style(
            'snrd-tv-css',
            SNRD_PLUGIN_URL . 'assets/css/snrd-tv.css',
            array(),
            SNRD_VERSION
        );

        wp_register_script(
            'snrd-hls-js',
            'https://cdn.jsdelivr.net/npm/hls.js@1.5.8/dist/hls.min.js',
            array(),
            '1.5.8',
            true
        );

        wp_register_script(
            'snrd-public-js',
            SNRD_PLUGIN_URL . 'assets/js/snrd-public.js',
            array( 'jquery', 'snrd-hls-js' ),
            SNRD_VERSION,
            true
        );

        wp_register_script(
            'snrd-tv-js',
            SNRD_PLUGIN_URL . 'assets/js/snrd-tv.js',
            array( 'jquery', 'snrd-hls-js' ),
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
     * Automatically ensure /radio/, /radio/apply/, and /tv/ pages exist and Core Stations (Floodlight & Peace FM) are seeded
     */
    public function check_and_create_pages() {
        self::ensure_core_stations_exist();

        if ( ! get_option( 'snrd_pages_auto_created' ) ) {
            self::auto_create_radio_pages();
            update_option( 'snrd_pages_auto_created', '1' );
        }

        // Auto ensure Live TV page (/tv/) is created
        if ( ! get_option( 'snrd_tv_page_auto_created' ) ) {
            self::ensure_tv_page_exists();
            update_option( 'snrd_tv_page_auto_created', '1' );
        }
    }

    /**
     * Ensure Core Stations (Floodlight Radio & Peace 104.3 Fm) exist with verified live stream URLs from Modern Ghana
     */
    public static function ensure_core_stations_exist() {
        // 1. Floodlight Radio (from Modern Ghana)
        $fl_stream = 'https://stream.zeno.fm/yecemx18mzzuv';
        $fl_title  = 'Floodlight Radio';
        
        $fl_existing = get_posts( array(
            'post_type'      => SNRD_POST_TYPE,
            'title'          => $fl_title,
            'post_status'    => 'any',
            'posts_per_page' => 1,
        ) );

        if ( empty( $fl_existing ) ) {
            $fl_existing = get_posts( array(
                'post_type'      => SNRD_POST_TYPE,
                's'              => 'Floodlight',
                'post_status'    => 'any',
                'posts_per_page' => 1,
            ) );
        }

        if ( ! empty( $fl_existing ) ) {
            $post_id = $fl_existing[0]->ID;
            update_post_meta( $post_id, 'snrd_stream_url', $fl_stream );
            update_post_meta( $post_id, 'snrd_location', 'Kumasi - Ghana' );
            update_post_meta( $post_id, 'snrd_contact_phone', '0248850040' );
            update_post_meta( $post_id, 'snrd_website_url', 'https://floodlightmediagh.com' );
            update_post_meta( $post_id, 'snrd_frequency', 'Online HD' );
            update_post_meta( $post_id, 'snrd_station_scope', 'ghana' );
            update_post_meta( $post_id, 'snrd_is_featured', '1' );
            update_post_meta( $post_id, 'snrd_is_top', '1' );
        } else {
            $post_id = wp_insert_post( array(
                'post_title'   => $fl_title,
                'post_content' => 'Floodlight Radio broadcasts live from Kumasi, delivering insightful talk shows, sports updates, and contemporary Ghanaian music.',
                'post_status'  => 'publish',
                'post_type'    => SNRD_POST_TYPE,
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'snrd_stream_url', $fl_stream );
                update_post_meta( $post_id, 'snrd_location', 'Kumasi - Ghana' );
                update_post_meta( $post_id, 'snrd_contact_phone', '0248850040' );
                update_post_meta( $post_id, 'snrd_website_url', 'https://floodlightmediagh.com' );
                update_post_meta( $post_id, 'snrd_zeno_url', 'https://zeno.fm' );
                update_post_meta( $post_id, 'snrd_frequency', 'Online HD' );
                update_post_meta( $post_id, 'snrd_station_scope', 'ghana' );
                update_post_meta( $post_id, 'snrd_is_featured', '1' );
                update_post_meta( $post_id, 'snrd_is_top', '1' );

                $term = get_term_by( 'name', 'Talk', SNRD_TAXONOMY_GENRE );
                if ( $term ) {
                    wp_set_object_terms( $post_id, array( $term->term_id ), SNRD_TAXONOMY_GENRE );
                }
            }
        }

        // 2. Peace 104.3 Fm (Modern Ghana stream and artwork)
        $peace_stream = 'https://peacefm-atunwadigital.streamguys1.com/peacefm';
        $peace_logo   = 'https://storage.googleapis.com/player-logos/logos/Peacefm-MG-Modern-Ghana-1736268249658.png';
        $peace_title  = 'Peace 104.3 Fm';

        $peace_existing = get_posts( array(
            'post_type'      => SNRD_POST_TYPE,
            's'              => 'Peace',
            'post_status'    => 'any',
            'posts_per_page' => 1,
        ) );

        if ( ! empty( $peace_existing ) ) {
            $p_id = $peace_existing[0]->ID;
            update_post_meta( $p_id, 'snrd_stream_url', $peace_stream );
            update_post_meta( $p_id, 'snrd_logo_url', $peace_logo );
            update_post_meta( $p_id, 'snrd_location', 'Mile 7, Achimota, Accra - Ghana' );
            update_post_meta( $p_id, 'snrd_contact_phone', '0302782414' );
            update_post_meta( $p_id, 'snrd_website_url', 'https://www.peacefmonline.com' );
            update_post_meta( $p_id, 'snrd_frequency', '104.3 FM' );
            update_post_meta( $p_id, 'snrd_station_scope', 'ghana' );
            update_post_meta( $p_id, 'snrd_is_featured', '1' );
            update_post_meta( $p_id, 'snrd_is_top', '1' );
        } else {
            $p_id = wp_insert_post( array(
                'post_title'   => $peace_title,
                'post_content' => 'Peace FM 104.3 is Ghana flagship private commercial broadcaster based in Mile 7, Achimota, Accra, renowned for the Kokrokoo morning show, breaking national news, and trusted discussions.',
                'post_status'  => 'publish',
                'post_type'    => SNRD_POST_TYPE,
            ) );

            if ( $p_id && ! is_wp_error( $p_id ) ) {
                update_post_meta( $p_id, 'snrd_stream_url', $peace_stream );
                update_post_meta( $p_id, 'snrd_logo_url', $peace_logo );
                update_post_meta( $p_id, 'snrd_location', 'Mile 7, Achimota, Accra - Ghana' );
                update_post_meta( $p_id, 'snrd_contact_phone', '0302782414' );
                update_post_meta( $p_id, 'snrd_website_url', 'https://www.peacefmonline.com' );
                update_post_meta( $p_id, 'snrd_zeno_url', 'https://radio.modernghana.com/station/peace1043fm' );
                update_post_meta( $p_id, 'snrd_frequency', '104.3 FM' );
                update_post_meta( $p_id, 'snrd_station_scope', 'ghana' );
                update_post_meta( $p_id, 'snrd_is_featured', '1' );
                update_post_meta( $p_id, 'snrd_is_top', '1' );

                $term = get_term_by( 'name', 'News', SNRD_TAXONOMY_GENRE );
                if ( $term ) {
                    wp_set_object_terms( $p_id, array( $term->term_id ), SNRD_TAXONOMY_GENRE );
                }
            }
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

        // 3. Create Live TV Page: /tv/
        self::ensure_tv_page_exists();

        // 4. Seed starter sample stations if empty
        self::seed_sample_stations();

        flush_rewrite_rules();
    }

    /**
     * Auto Create Live TV Page: /tv/ with [savika_live_tv]
     */
    public static function ensure_tv_page_exists() {
        $tv_page = get_page_by_path( 'tv' );
        if ( empty( $tv_page ) ) {
            wp_insert_post( array(
                'post_title'     => 'Live TV',
                'post_name'      => 'tv',
                'post_content'   => '[savika_live_tv]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ) );
        } elseif ( is_object( $tv_page ) && isset( $tv_page->post_content ) ) {
            if ( false === strpos( $tv_page->post_content, '[savika_live_tv]' ) && false === strpos( $tv_page->post_content, '[savika_tv_directory]' ) ) {
                wp_update_post( array(
                    'ID'           => $tv_page->ID,
                    'post_content' => '[savika_live_tv]',
                    'post_status'  => 'publish',
                ) );
            }
        }
    }

    /**
     * Seed sample Ghanaian radio stations from verified Modern Ghana database (1000+ stations)
     *
     * @param bool $force_sync Whether to force-update existing stations and seed all stations.
     * @return int Number of stations processed.
     */
    public static function seed_sample_stations( $force_sync = false ) {
        global $wpdb;

        $existing = get_posts( array(
            'post_type'      => SNRD_POST_TYPE,
            'posts_per_page' => 1,
            'post_status'    => 'any',
        ) );

        if ( ! empty( $existing ) && ! $force_sync ) {
            return 0;
        }

        // Increase limits for processing 1000+ stations
        @set_time_limit( 600 );
        @ini_set( 'memory_limit', '512M' );
        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );

        $json_file = SNRD_PLUGIN_DIR . 'includes/data/stations_seed_1000.json';
        if ( ! file_exists( $json_file ) ) {
            $json_file = SNRD_PLUGIN_DIR . 'includes/data/stations_seed_250.json';
        }
        if ( ! file_exists( $json_file ) ) {
            $json_file = SNRD_PLUGIN_DIR . 'includes/data/stations_seed_50.json';
        }
        $starter_stations = array();

        if ( file_exists( $json_file ) ) {
            $raw_json = file_get_contents( $json_file );
            $decoded  = json_decode( $raw_json, true );
            if ( is_array( $decoded ) && ! empty( $decoded ) ) {
                $starter_stations = $decoded;
            }
        }

        if ( empty( $starter_stations ) ) {
            $starter_stations = array(
                array(
                    'title'       => 'Floodlight Radio',
                    'frequency'   => 'Online HD',
                    'location'    => 'Kumasi - Ghana',
                    'phone'       => '0248850040',
                    'genre'       => 'Talk',
                    'scope'       => 'ghana',
                    'streamUrl'   => 'https://stream.zeno.fm/yecemx18mzzuv',
                    'logoUrl'     => 'https://cdn.modernghana.com/radio_logo/floodlightradio.jpg',
                ),
                array(
                    'title'       => 'Peace 104.3 Fm',
                    'frequency'   => '104.3 FM',
                    'location'    => 'Mile 7, Achimota, Accra - Ghana',
                    'phone'       => '0302782414',
                    'genre'       => 'News',
                    'scope'       => 'ghana',
                    'streamUrl'   => 'https://peacefm-atunwadigital.streamguys1.com/peacefm',
                    'logoUrl'     => 'https://storage.googleapis.com/player-logos/logos/Peacefm-MG-Modern-Ghana-1736268249658.png',
                )
            );
        }

        // Single optimized query to pre-index all existing stations by slug and lower-case title
        $existing_rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type = %s", SNRD_POST_TYPE ) );
        $existing_by_slug  = array();
        $existing_by_title = array();
        if ( ! empty( $existing_rows ) ) {
            foreach ( $existing_rows as $row ) {
                $existing_by_slug[ $row->post_name ] = intval( $row->ID );
                $existing_by_title[ strtolower( trim( $row->post_title ) ) ] = intval( $row->ID );
            }
        }

        $count = 0;
        foreach ( $starter_stations as $st ) {
            $title      = ! empty( $st['title'] ) ? sanitize_text_field( $st['title'] ) : 'Radio Station';
            $stream_url = ! empty( $st['streamUrl'] ) ? esc_url_raw( $st['streamUrl'] ) : ( ! empty( $st['stream_url'] ) ? esc_url_raw( $st['stream_url'] ) : '' );
            $logo_url   = ! empty( $st['logoUrl'] ) ? esc_url_raw( $st['logoUrl'] ) : ( ! empty( $st['logo_url'] ) ? esc_url_raw( $st['logo_url'] ) : '' );
            $frequency  = ! empty( $st['frequency'] ) ? sanitize_text_field( $st['frequency'] ) : 'Online HD';
            $location   = ! empty( $st['location'] ) ? sanitize_text_field( $st['location'] ) : 'Ghana';
            $phone      = ! empty( $st['phone'] ) ? sanitize_text_field( $st['phone'] ) : '+233 30 200 0000';
            $genre      = ! empty( $st['genre'] ) ? sanitize_text_field( $st['genre'] ) : 'News';
            $scope      = ! empty( $st['scope'] ) ? sanitize_text_field( $st['scope'] ) : 'ghana';
            $slug       = ! empty( $st['slug'] ) ? sanitize_title( $st['slug'] ) : sanitize_title( $title );
            $desc       = ! empty( $st['description'] ) ? sanitize_text_field( $st['description'] ) : sprintf( '%s broadcasts live from %s with top news, music, sports, and discussions.', $title, $location );

            // Check if station already exists via our fast pre-indexed map
            $title_key = strtolower( trim( $title ) );
            $post_id   = 0;

            if ( isset( $existing_by_slug[ $slug ] ) ) {
                $post_id = $existing_by_slug[ $slug ];
            } elseif ( isset( $existing_by_title[ $title_key ] ) ) {
                $post_id = $existing_by_title[ $title_key ];
            }

            if ( $post_id > 0 ) {
                if ( ! empty( $stream_url ) ) {
                    update_post_meta( $post_id, 'snrd_stream_url', $stream_url );
                }
                if ( ! empty( $logo_url ) ) {
                    update_post_meta( $post_id, 'snrd_logo_url', $logo_url );
                }
                update_post_meta( $post_id, 'snrd_frequency', $frequency );
                update_post_meta( $post_id, 'snrd_location', $location );
                update_post_meta( $post_id, 'snrd_station_scope', $scope );
                update_post_meta( $post_id, 'snrd_is_featured', '1' );
                update_post_meta( $post_id, 'snrd_is_top', '1' );
                $count++;
            } else {
                $post_id = wp_insert_post( array(
                    'post_title'   => $title,
                    'post_name'    => $slug,
                    'post_content' => $desc,
                    'post_status'  => 'publish',
                    'post_type'    => SNRD_POST_TYPE,
                ) );

                if ( $post_id && ! is_wp_error( $post_id ) ) {
                    $existing_by_slug[ $slug ]         = $post_id;
                    $existing_by_title[ $title_key ]   = $post_id;

                    update_post_meta( $post_id, 'snrd_stream_url', $stream_url );
                    update_post_meta( $post_id, 'snrd_logo_url', $logo_url );
                    update_post_meta( $post_id, 'snrd_frequency', $frequency );
                    update_post_meta( $post_id, 'snrd_location', $location );
                    update_post_meta( $post_id, 'snrd_contact_phone', $phone );
                    update_post_meta( $post_id, 'snrd_station_scope', $scope );
                    update_post_meta( $post_id, 'snrd_is_featured', '1' );
                    update_post_meta( $post_id, 'snrd_is_top', '1' );
                    update_post_meta( $post_id, 'snrd_website_url', home_url( '/' ) );
                    update_post_meta( $post_id, 'snrd_contact_email', get_option( 'admin_email' ) );

                    wp_set_object_terms( $post_id, array( $genre ), SNRD_TAXONOMY_GENRE );
                    $count++;
                }
            }
        }

        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );

        return $count;
    }

    /**
     * Activation logic
     */
    public function activate() {
        try {
            require_once SNRD_PLUGIN_DIR . 'includes/class-snrd-post-type.php';
            SNRD_Post_Type::register_post_type();
            SNRD_Post_Type::register_taxonomy();
            SNRD_Post_Type::insert_default_genres();

            // Ensure core stations and pages exist
            self::ensure_core_stations_exist();
            self::auto_create_radio_pages();
            update_option( 'snrd_pages_auto_created', '1' );

            flush_rewrite_rules();
        } catch ( \Throwable $e ) {
            // Prevent fatal errors from halting WordPress
            error_log( 'SNRD Activation error: ' . $e->getMessage() );
        }
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
