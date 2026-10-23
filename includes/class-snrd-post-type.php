<?php
/**
 * Post Type and Taxonomy Registration
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SNRD_Post_Type' ) ) :

class SNRD_Post_Type {

    /**
     * Initialize post type hooks
     */
    public static function init() {
        if ( did_action( 'init' ) ) {
            self::register_post_type();
            self::register_taxonomy();
        } else {
            add_action( 'init', array( __CLASS__, 'register_post_type' ) );
            add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
        }
    }

    /**
     * Register Radio Station CPT
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Radio Stations', 'Post type general name', 'savika-news-radio' ),
            'singular_name'         => _x( 'Radio Station', 'Post type singular name', 'savika-news-radio' ),
            'menu_name'             => _x( 'Radio Stations', 'Admin Menu text', 'savika-news-radio' ),
            'name_admin_bar'        => _x( 'Radio Station', 'Add New on Toolbar', 'savika-news-radio' ),
            'add_new'               => __( 'Add New Station', 'savika-news-radio' ),
            'add_new_item'          => __( 'Add New Radio Station', 'savika-news-radio' ),
            'new_item'              => __( 'New Radio Station', 'savika-news-radio' ),
            'edit_item'             => __( 'Edit Radio Station', 'savika-news-radio' ),
            'view_item'             => __( 'View Radio Station', 'savika-news-radio' ),
            'all_items'             => __( 'All Stations', 'savika-news-radio' ),
            'search_items'          => __( 'Search Radio Stations', 'savika-news-radio' ),
            'not_found'             => __( 'No radio stations found.', 'savika-news-radio' ),
            'not_found_in_trash'    => __( 'No radio stations found in Trash.', 'savika-news-radio' ),
            'featured_image'        => __( 'Station Logo', 'savika-news-radio' ),
            'set_featured_image'    => __( 'Set station logo', 'savika-news-radio' ),
            'remove_featured_image' => __( 'Remove station logo', 'savika-news-radio' ),
            'use_featured_image'    => __( 'Use as station logo', 'savika-news-radio' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'radio-station', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => false, // Disabled archive so Elementor page /radio/ works cleanly
            'hierarchical'       => false,
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-format-audio',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
        );

        register_post_type( SNRD_POST_TYPE, $args );
    }

    /**
     * Register Genre Taxonomy
     */
    public static function register_taxonomy() {
        $labels = array(
            'name'              => _x( 'Genres', 'taxonomy general name', 'savika-news-radio' ),
            'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'savika-news-radio' ),
            'search_items'      => __( 'Search Genres', 'savika-news-radio' ),
            'all_items'         => __( 'All Genres', 'savika-news-radio' ),
            'parent_item'       => __( 'Parent Genre', 'savika-news-radio' ),
            'parent_item_colon' => __( 'Parent Genre:', 'savika-news-radio' ),
            'edit_item'         => __( 'Edit Genre', 'savika-news-radio' ),
            'update_item'       => __( 'Update Genre', 'savika-news-radio' ),
            'add_new_item'      => __( 'Add New Genre', 'savika-news-radio' ),
            'new_item_name'     => __( 'New Genre Name', 'savika-news-radio' ),
            'menu_name'         => __( 'Genres', 'savika-news-radio' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'radio-genre' ),
            'show_in_rest'      => true,
        );

        register_taxonomy( SNRD_TAXONOMY_GENRE, array( SNRD_POST_TYPE ), $args );
    }

    /**
     * Insert initial default genres if empty
     */
    public static function insert_default_genres() {
        $default_genres = array(
            'News',
            'Talk',
            'Gospel',
            'Sports',
            'Music',
            'Community',
            'Educational',
            'Other'
        );

        foreach ( $default_genres as $genre ) {
            if ( ! term_exists( $genre, SNRD_TAXONOMY_GENRE ) ) {
                wp_insert_term( $genre, SNRD_TAXONOMY_GENRE );
            }
        }
    }
}

endif;
