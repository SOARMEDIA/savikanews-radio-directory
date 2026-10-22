<?php
/**
 * Shortcodes Handler
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNRD_Shortcodes {

    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode( 'savika_radio_directory', array( __CLASS__, 'render_directory' ) );
        add_shortcode( 'savika_radio_apply', array( __CLASS__, 'render_application_form' ) );
    }

    /**
     * Render Radio Directory Shortcode: [savika_radio_directory]
     */
    public static function render_directory( $atts ) {
        wp_enqueue_style( 'snrd-public-css' );
        wp_enqueue_script( 'snrd-public-js' );

        $atts = shortcode_atts( array(
            'per_page' => 300,
            'genre'    => '',
        ), $atts, 'savika_radio_directory' );

        $genres = get_terms( array(
            'taxonomy'   => SNRD_TAXONOMY_GENRE,
            'hide_empty' => false,
        ) );

        $query_args = array(
            'post_type'      => SNRD_POST_TYPE,
            'post_status'    => 'publish', // Only published stations
            'posts_per_page' => intval( $atts['per_page'] ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if ( ! empty( $atts['genre'] ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => SNRD_TAXONOMY_GENRE,
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $atts['genre'] ),
                ),
            );
        }

        $stations_query = new WP_Query( $query_args );

        ob_start();
        include SNRD_PLUGIN_DIR . 'templates/directory-grid.php';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Render Application Form Shortcode: [savika_radio_apply]
     */
    public static function render_application_form( $atts ) {
        wp_enqueue_style( 'snrd-public-css' );

        $genres = get_terms( array(
            'taxonomy'   => SNRD_TAXONOMY_GENRE,
            'hide_empty' => false,
        ) );

        ob_start();
        include SNRD_PLUGIN_DIR . 'templates/application-form.php';
        return ob_get_clean();
    }
}
