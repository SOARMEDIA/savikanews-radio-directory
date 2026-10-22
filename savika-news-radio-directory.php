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

define( 'SNRD_VERSION', '1.0.3' );
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
     * Automatically ensure /radio/ and /radio/apply/ pages exist and Core Stations (Floodlight & Peace FM) are seeded
     */
    public function check_and_create_pages() {
        self::ensure_core_stations_exist();

        if ( ! get_option( 'snrd_pages_auto_created' ) ) {
            self::auto_create_radio_pages();
            update_option( 'snrd_pages_auto_created', '1' );
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
            return;
        }

        $starter_stations = array(
            array(
                'title'       => 'Floodlight Radio',
                'frequency'   => 'Online HD',
                'location'    => 'Kumasi - Ghana',
                'phone'       => '0248850040',
                'genre'       => 'Talk',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/yecemx18mzzuv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Floodlight Radio broadcasts live from Kumasi, delivering insightful talk shows, sports updates, and contemporary Ghanaian music.',
            ),
            array(
                'title'       => 'Peace 104.3 Fm',
                'frequency'   => '104.3 FM',
                'location'    => 'Mile 7, Achimota, Accra - Ghana',
                'phone'       => '0302782414',
                'genre'       => 'News',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://peacefm-atunwadigital.streamguys1.com/peacefm',
                'logo_url'    => 'https://storage.googleapis.com/player-logos/logos/Peacefm-MG-Modern-Ghana-1736268249658.png',
                'zeno_url'    => 'https://radio.modernghana.com/station/peace1043fm',
                'description' => 'Peace FM 104.3 is Ghana flagship private commercial broadcaster based in Mile 7, Achimota, Accra, known for flagship show Kokrokoo.',
            ),
            array(
                'title'       => 'Adom FM 106.3 FM',
                'frequency'   => '106.3 FM',
                'location'    => 'Tema / Accra - Ghana',
                'phone'       => '0302211600',
                'genre'       => 'Talk',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Adom FM delivers trusted Akan news, entertainment, community programming, and morning devotional shows across Ghana.',
            ),
            array(
                'title'       => 'Joy 99.7 FM',
                'frequency'   => '99.7 FM',
                'location'    => 'Accra - Ghana',
                'phone'       => '0302211600',
                'genre'       => 'News',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Joy FM is Ghana premier English-speaking radio broadcaster delivering breaking news, comprehensive analysis, and current affairs.',
            ),
            array(
                'title'       => 'Citi FM 97.3 FM',
                'frequency'   => '97.3 FM',
                'location'    => 'Adabraka, Accra - Ghana',
                'phone'       => '0302226013',
                'genre'       => 'News',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Citi FM is an authoritative English radio network in Accra, recognized for the Citi Breakfast Show and Eyewitness News.',
            ),
            array(
                'title'       => 'Kessben 93.3 FM',
                'frequency'   => '93.3 FM',
                'location'    => 'Kumasi - Ghana',
                'phone'       => '0322044455',
                'genre'       => 'Sports',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Kessben FM 93.3 is a powerhouse broadcast station in Kumasi, Ashanti Region, famed for authoritative sports news and politics.',
            ),
            array(
                'title'       => 'Okay FM 101.7 FM',
                'frequency'   => '101.7 FM',
                'location'    => 'Accra - Ghana',
                'phone'       => '0302782414',
                'genre'       => 'Talk',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Okay FM provides urban contemporary Akan talk, entertainment news, drive-time music, and community service programs.',
            ),
            array(
                'title'       => 'Oman FM 107.1 FM',
                'frequency'   => '107.1 FM',
                'location'    => 'Madina, Accra - Ghana',
                'phone'       => '0302500000',
                'genre'       => 'Talk',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Oman FM 107.1 is a prominent political talk and news station operating from Madina, Greater Accra.',
            ),
            array(
                'title'       => 'Kingdom FM 106.1 FM',
                'frequency'   => '106.1 FM',
                'location'    => 'Mankessim - Ghana',
                'phone'       => '0240000000',
                'genre'       => 'News',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Kingdom FM 106.1 is an authoritative commercial radio station broadcasting from Mankessim, Central Region.',
            ),
            array(
                'title'       => 'Nhyira FM 104.5 FM',
                'frequency'   => '104.5 FM',
                'location'    => 'Kumasi - Ghana',
                'phone'       => '0322030000',
                'genre'       => 'Community',
                'scope'       => 'ghana',
                'featured'    => '1',
                'top'         => '1',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Nhyira FM 104.5 is a prominent radio station in Kumasi, delivering high-impact social and investigative programming.',
            ),
            array(
                'title'       => 'Fawohodie Radio',
                'frequency'   => 'Diaspora Online',
                'location'    => 'United States - Diaspora',
                'phone'       => '+18005550199',
                'genre'       => 'Talk',
                'scope'       => 'diaspora',
                'featured'    => '1',
                'top'         => '0',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Fawohodie Radio connects Ghanaians living in North America with news, discussions, and highlife music from home.',
            ),
            array(
                'title'       => 'Amansan FM UK',
                'frequency'   => 'London UK',
                'location'    => 'London - UK',
                'phone'       => '+442079460991',
                'genre'       => 'Community',
                'scope'       => 'diaspora',
                'featured'    => '1',
                'top'         => '0',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Amansan FM UK is an established diaspora broadcaster serving the Ghanaian community in London and Europe.',
            ),
            array(
                'title'       => 'Rainbow Radio UK',
                'frequency'   => 'DAB London',
                'location'    => 'London - UK',
                'phone'       => '+44208000000',
                'genre'       => 'News',
                'scope'       => 'diaspora',
                'featured'    => '1',
                'top'         => '0',
                'stream_url'  => 'https://stream.zeno.fm/f3wvbbqmdg8uv',
                'zeno_url'    => 'https://zeno.fm',
                'description' => 'Rainbow Radio UK broadcasts on DAB and online, bridging the gap between Ghana and the diaspora worldwide.',
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
                update_post_meta( $post_id, 'snrd_frequency', $st['frequency'] );
                update_post_meta( $post_id, 'snrd_contact_phone', $st['phone'] );
                update_post_meta( $post_id, 'snrd_station_scope', $st['scope'] );
                update_post_meta( $post_id, 'snrd_is_featured', $st['featured'] );
                update_post_meta( $post_id, 'snrd_is_top', $st['top'] );
                update_post_meta( $post_id, 'snrd_stream_url', $st['stream_url'] );
                if ( ! empty( $st['logo_url'] ) ) {
                    update_post_meta( $post_id, 'snrd_logo_url', $st['logo_url'] );
                }
                update_post_meta( $post_id, 'snrd_zeno_url', $st['zeno_url'] );
                update_post_meta( $post_id, 'snrd_website_url', home_url( '/' ) );
                update_post_meta( $post_id, 'snrd_contact_email', get_option( 'admin_email' ) );

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

        // Ensure core stations (Floodlight Radio & Peace FM) exist
        self::ensure_core_stations_exist();

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
