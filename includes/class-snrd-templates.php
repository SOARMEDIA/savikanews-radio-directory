<?php
/**
 * Single Station and Directory Template Loader
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SNRD_Templates' ) ) :

class SNRD_Templates {

    /**
     * Initialize template hooks
     */
    public static function init() {
        add_filter( 'template_include', array( __CLASS__, 'load_single_template' ) );
        add_filter( 'pre_get_document_title', array( __CLASS__, 'custom_station_title' ), 20 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_single_assets' ) );
    }

    /**
     * Load single station template
     */
    public static function load_single_template( $template ) {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            // Check if current active theme has an override
            $theme_template = locate_template( array( 'single-snrd_station.php' ) );
            if ( ! empty( $theme_template ) ) {
                return $theme_template;
            }

            // Fallback to plugin template
            $plugin_template = SNRD_PLUGIN_DIR . 'templates/single-snrd_station.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }

        return $template;
    }

    /**
     * Custom SEO Title for Single Radio Station
     * Example: "Kingdom FM 106.1 FM — Listen Live | Savika News Radio"
     */
    public static function custom_station_title( $title ) {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            $station_name = get_the_title();
            return sprintf( __( '%s — Listen Live | Savika News Radio', 'savika-news-radio' ), $station_name );
        }
        return $title;
    }

    /**
     * Enqueue assets if viewing a single station
     */
    public static function enqueue_single_assets() {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            wp_enqueue_style( 'snrd-public-css' );
            wp_enqueue_script( 'snrd-public-js' );
        }
    }
}

endif;
