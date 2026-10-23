<?php
/**
 * SEO & Schema.org Rich Results Engine for Radio Stations
 * Provides JSON-LD structured data (RadioStation, ListenAction), targeted meta tags,
 * OpenGraph, Twitter Cards, and dedicated XML Radio Sitemap for Google indexing.
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SNRD_SEO' ) ) :

class SNRD_SEO {

    /**
     * Initialize SEO hooks
     */
    public static function init() {
        // Output Meta tags, OpenGraph, and JSON-LD Schema in <head>
        add_action( 'wp_head', array( __CLASS__, 'output_head_seo' ), 1 );

        // Filter Document Title for Google search rankings
        add_filter( 'document_title_parts', array( __CLASS__, 'filter_document_title' ), 20 );
        add_filter( 'pre_get_document_title', array( __CLASS__, 'filter_pre_document_title' ), 20 );

        // Dynamic XML Radio Sitemap
        add_action( 'init', array( __CLASS__, 'register_sitemap_rewrite' ) );
        add_action( 'template_redirect', array( __CLASS__, 'render_radio_sitemap' ) );

        // Add sitemap notice and ping button in WP Admin
        add_action( 'admin_init', array( __CLASS__, 'handle_sitemap_flush' ) );
    }

    /**
     * Filter Document Title for Station Single Pages & Directory Page
     */
    public static function filter_document_title( $title_parts ) {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            $post_id   = get_the_ID();
            $st_title  = get_the_title( $post_id );
            $freq      = get_post_meta( $post_id, 'snrd_frequency', true );
            $location  = get_post_meta( $post_id, 'snrd_location', true );
            
            $dial_str = ! empty( $freq ) ? "({$freq})" : '';
            $title_parts['title'] = "{$st_title} {$dial_str} Live Online Broadcast";
            $title_parts['site']  = 'Listen on Savika Radio Ghana';
        }
        return $title_parts;
    }

    /**
     * Fallback document title
     */
    public static function filter_pre_document_title( $title ) {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            $post_id   = get_the_ID();
            $st_title  = get_the_title( $post_id );
            $freq      = get_post_meta( $post_id, 'snrd_frequency', true );
            $dial_str  = ! empty( $freq ) ? " ({$freq})" : '';
            return "{$st_title}{$dial_str} Live Online Broadcast | Listen on Savika Radio Ghana";
        }
        return $title;
    }

    /**
     * Output Meta Tags, OpenGraph, and Schema.org JSON-LD in <head>
     */
    public static function output_head_seo() {
        if ( is_singular( SNRD_POST_TYPE ) ) {
            self::output_single_station_seo();
        } elseif ( is_page( 'radio' ) || is_post_type_archive( SNRD_POST_TYPE ) ) {
            self::output_directory_seo();
        }
    }

    /**
     * Single Station Meta & Schema.org JSON-LD
     */
    private static function output_single_station_seo() {
        $post_id     = get_the_ID();
        $title       = get_the_title( $post_id );
        $permalink   = get_permalink( $post_id );
        $freq        = get_post_meta( $post_id, 'snrd_frequency', true );
        $location    = get_post_meta( $post_id, 'snrd_location', true );
        $stream_url  = get_post_meta( $post_id, 'snrd_stream_url', true );
        $logo_url    = get_post_meta( $post_id, 'snrd_logo_url', true );
        $phone       = get_post_meta( $post_id, 'snrd_contact_phone', true );
        $scope       = get_post_meta( $post_id, 'snrd_station_scope', true );
        $terms       = get_the_terms( $post_id, SNRD_TAXONOMY_GENRE );
        $genre       = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'General';
        
        if ( empty( $logo_url ) && has_post_thumbnail( $post_id ) ) {
            $logo_url = get_the_post_thumbnail_url( $post_id, 'full' );
        }
        if ( empty( $logo_url ) ) {
            $logo_url = 'https://storage.googleapis.com/player-logos/logos/Peacefm-MG-Modern-Ghana-1736268249658.png';
        }
        if ( empty( $location ) ) {
            $location = 'Ghana';
        }
        if ( empty( $freq ) ) {
            $freq = 'Online HD';
        }

        $meta_desc = sprintf(
            'Listen live to %s (%s) broadcasting from %s. High quality live audio streaming, news, music, sports, and talk shows on Savika News Radio Directory.',
            $title,
            $freq,
            $location
        );
        ?>

        <!-- Savika Radio Station SEO & OpenGraph -->
        <meta name="description" content="<?php echo esc_attr( $meta_desc ); ?>" />
        <meta name="keywords" content="<?php echo esc_attr( "{$title}, {$title} live, {$title} online, {$title} stream, {$freq}, Ghana radio online, Savika Radio, listen live Ghana" ); ?>" />
        <link rel="canonical" href="<?php echo esc_url( $permalink ); ?>" />

        <!-- OpenGraph -->
        <meta property="og:type" content="music.radio_station" />
        <meta property="og:site_name" content="Savika News Radio Directory" />
        <meta property="og:title" content="<?php echo esc_attr( "{$title} ({$freq}) Live Stream" ); ?>" />
        <meta property="og:description" content="<?php echo esc_attr( $meta_desc ); ?>" />
        <meta property="og:url" content="<?php echo esc_url( $permalink ); ?>" />
        <meta property="og:image" content="<?php echo esc_url( $logo_url ); ?>" />
        <?php if ( ! empty( $stream_url ) ) : ?>
            <meta property="og:audio" content="<?php echo esc_url( $stream_url ); ?>" />
        <?php endif; ?>

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="<?php echo esc_attr( "{$title} ({$freq}) Live Stream | Savika Radio" ); ?>" />
        <meta name="twitter:description" content="<?php echo esc_attr( $meta_desc ); ?>" />
        <meta name="twitter:image" content="<?php echo esc_url( $logo_url ); ?>" />

        <!-- Google Schema.org Structured Data (RadioStation & ListenAction) -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "RadioStation",
            "@id": <?php echo wp_json_encode( $permalink . '#station' ); ?>,
            "name": <?php echo wp_json_encode( $title ); ?>,
            "alternateName": [
                <?php echo wp_json_encode( $title . ' Ghana' ); ?>,
                <?php echo wp_json_encode( $title . ' Live' ); ?>
            ],
            "url": <?php echo wp_json_encode( $permalink ); ?>,
            "logo": <?php echo wp_json_encode( $logo_url ); ?>,
            "image": <?php echo wp_json_encode( $logo_url ); ?>,
            "description": <?php echo wp_json_encode( $meta_desc ); ?>,
            "broadcastFrequency": <?php echo wp_json_encode( $freq ); ?>,
            "broadcastChannelId": <?php echo wp_json_encode( $freq ); ?>,
            "broadcastServiceTier": "Free",
            "genre": <?php echo wp_json_encode( array( $genre, 'African Music', 'Ghana News' ) ); ?>,
            "inLanguage": ["en", "ak", "tw"],
            "address": {
                "@type": "PostalAddress",
                "addressLocality": <?php echo wp_json_encode( $location ); ?>,
                "addressCountry": "GH"
            },
            "parentOrganization": {
                "@type": "NewsMediaOrganization",
                "name": "Savika News",
                "url": "https://savikanews.com/"
            },
            <?php if ( ! empty( $stream_url ) ) : ?>
            "potentialAction": {
                "@type": "ListenAction",
                "target": [
                    {
                        "@type": "EntryPoint",
                        "urlTemplate": <?php echo wp_json_encode( $permalink . '?autoplay=1' ); ?>,
                        "actionPlatform": [
                            "http://schema.org/DesktopWebPlatform",
                            "http://schema.org/MobileWebPlatform",
                            "http://schema.org/IOSPlatform",
                            "http://schema.org/AndroidPlatform"
                        ]
                    }
                ],
                "expectsAcceptanceOf": {
                    "@type": "Offer",
                    "category": "free",
                    "price": "0",
                    "priceCurrency": "GHS"
                }
            }
            <?php endif; ?>
        }
        </script>
        <?php
    }

    /**
     * Directory Archive Meta & Schema.org JSON-LD
     */
    private static function output_directory_seo() {
        $canonical = home_url( '/radio/' );
        $title     = 'Ghana Radio Stations Live — Over 1,000+ Online FM Broadcasts | Savika News';
        $desc      = 'Listen live to over 1,000 Ghanaian radio stations online. Stream Peace FM, Joy FM, Adom FM, Citi FM, Okay FM, Kessben FM, and regional broadcasts across Ghana on Savika Radio.';
        ?>
        <meta name="description" content="<?php echo esc_attr( $desc ); ?>" />
        <meta name="keywords" content="Ghana radio online, listen live Ghana radio, Ghana FM stations, Peace FM live, Joy FM online, Adom FM stream, Citi FM live, Savika Radio Directory" />
        <link rel="canonical" href="<?php echo esc_url( $canonical ); ?>" />

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Savika News" />
        <meta property="og:title" content="<?php echo esc_attr( $title ); ?>" />
        <meta property="og:description" content="<?php echo esc_attr( $desc ); ?>" />
        <meta property="og:url" content="<?php echo esc_url( $canonical ); ?>" />

        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "CollectionPage",
            "name": <?php echo wp_json_encode( $title ); ?>,
            "description": <?php echo wp_json_encode( $desc ); ?>,
            "url": <?php echo wp_json_encode( $canonical ); ?>,
            "publisher": {
                "@type": "NewsMediaOrganization",
                "name": "Savika News",
                "url": "https://savikanews.com/"
            }
        }
        </script>
        <?php
    }

    /**
     * Register rewrite rule for /radio-sitemap.xml
     */
    public static function register_sitemap_rewrite() {
        add_rewrite_rule( '^radio-sitemap\.xml$', 'index.php?snrd_sitemap=1', 'top' );
        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'snrd_sitemap';
            return $vars;
        } );
    }

    /**
     * Flush rewrites on admin notice if needed
     */
    public static function handle_sitemap_flush() {
        if ( isset( $_GET['snrd_action'] ) && 'flush_sitemap' === $_GET['snrd_action'] && current_user_can( 'manage_options' ) ) {
            flush_rewrite_rules();
            wp_redirect( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_msg' => 'sitemap_flushed' ), admin_url( 'edit.php' ) ) );
            exit;
        }
    }

    /**
     * Render high-performance XML Radio Sitemap for Googlebot & Google Search Console
     */
    public static function render_radio_sitemap() {
        if ( '1' !== get_query_var( 'snrd_sitemap' ) && false === strpos( $_SERVER['REQUEST_URI'] ?? '', 'radio-sitemap.xml' ) ) {
            return;
        }

        // Fetch all published radio stations
        global $wpdb;
        $stations = $wpdb->get_results( $wpdb->prepare(
            "SELECT ID, post_title, post_name, post_modified_gmt 
             FROM {$wpdb->posts} 
             WHERE post_type = %s AND post_status = 'publish' 
             ORDER BY post_modified_gmt DESC 
             LIMIT 3000",
            SNRD_POST_TYPE
        ) );

        if ( ! headers_sent() ) {
            header( 'Content-Type: application/xml; charset=utf-8' );
            header( 'X-Robots-Tag: noindex, follow', true );
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( includes_url( 'wlwmanifest.xml' ) ) . '"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Directory index page
        $dir_url = home_url( '/radio/' );
        echo "  <url>\n";
        echo "    <loc>" . esc_url( $dir_url ) . "</loc>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>1.0</priority>\n";
        echo "  </url>\n";

        // Live TV Portal page
        $tv_url = home_url( '/tv/' );
        echo "  <url>\n";
        echo "    <loc>" . esc_url( $tv_url ) . "</loc>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";

        foreach ( $stations as $st ) {
            $loc = get_permalink( $st->ID );
            $lastmod = ! empty( $st->post_modified_gmt ) ? date( 'c', strtotime( $st->post_modified_gmt ) ) : date( 'c' );
            $logo = get_post_meta( $st->ID, 'snrd_logo_url', true );

            echo "  <url>\n";
            echo "    <loc>" . esc_url( $loc ) . "</loc>\n";
            echo "    <lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            if ( ! empty( $logo ) ) {
                echo "    <image:image>\n";
                echo "      <image:loc>" . esc_url( $logo ) . "</image:loc>\n";
                echo "      <image:title>" . esc_html( $st->post_title ) . "</image:title>\n";
                echo "    </image:image>\n";
            }
            echo "  </url>\n";
        }

        echo '</urlset>';
        exit;
    }
}

endif;

