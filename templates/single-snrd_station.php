<?php
/**
 * Single Station Template
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$post_id      = get_the_ID();
$location     = get_post_meta( $post_id, 'snrd_location', true );
$stream_url   = get_post_meta( $post_id, 'snrd_stream_url', true );
$zeno_url     = get_post_meta( $post_id, 'snrd_zeno_url', true );
$website_url  = get_post_meta( $post_id, 'snrd_website_url', true );
$genres       = get_the_terms( $post_id, SNRD_TAXONOMY_GENRE );
$directory_url = home_url( '/radio/' );
?>

<div class="snrd-single-station-wrapper">
    <div class="snrd-container">

        <!-- Breadcrumbs -->
        <nav class="snrd-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'savika-news-radio' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'savika-news-radio' ); ?></a>
            <span class="snrd-sep">›</span>
            <a href="<?php echo esc_url( $directory_url ); ?>"><?php esc_html_e( 'Radio Directory', 'savika-news-radio' ); ?></a>
            <span class="snrd-sep">›</span>
            <span class="snrd-current"><?php the_title(); ?></span>
        </nav>

        <article class="snrd-station-profile">
            <div class="snrd-profile-hero">
                
                <!-- Logo -->
                <div class="snrd-profile-logo-wrap">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'large', array( 'class' => 'snrd-profile-logo', 'alt' => esc_attr( get_the_title() ) ) ); ?>
                    <?php else : ?>
                        <div class="snrd-profile-placeholder">
                            <svg viewBox="0 0 24 24" width="64" height="64" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Header -->
                <div class="snrd-profile-header-details">
                    <h1 class="snrd-profile-title"><?php the_title(); ?></h1>

                    <div class="snrd-profile-meta-tags">
                        <?php if ( ! empty( $location ) ) : ?>
                            <span class="snrd-tag-location">
                                📍 <?php echo esc_html( $location ); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                            <?php foreach ( $genres as $genre ) : ?>
                                <span class="snrd-tag-genre"><?php echo esc_html( $genre->name ); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <span class="snrd-tag-live">
                            <span class="snrd-live-dot"></span> <?php esc_html_e( 'LIVE STREAM', 'savika-news-radio' ); ?>
                        </span>
                    </div>

                    <!-- Custom HTML5 Audio Player Card -->
                    <div class="snrd-profile-player-card">
                        <div class="snrd-player-label">
                            <strong><?php esc_html_e( 'Live Audio Broadcast', 'savika-news-radio' ); ?></strong>
                        </div>

                        <?php if ( ! empty( $stream_url ) ) : ?>
                            <audio class="snrd-audio-player snrd-single-audio" controls preload="none" playsinline>
                                <source src="<?php echo esc_url( $stream_url ); ?>" />
                                <?php esc_html_e( 'Your browser does not support HTML5 live audio streaming.', 'savika-news-radio' ); ?>
                            </audio>
                        <?php else : ?>
                            <div class="snrd-alert snrd-alert-warning" style="margin: 0;">
                                <?php esc_html_e( 'Stream is currently being scheduled or updated.', 'savika-news-radio' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <div class="snrd-fallback-notice">
                                <small>
                                    <?php esc_html_e( 'Experiencing buffering or silence?', 'savika-news-radio' ); ?> 
                                    <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'Open official Zeno.fm player page ↗', 'savika-news-radio' ); ?>
                                    </a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Links (Website, Zeno.fm) -->
                    <div class="snrd-profile-links">
                        <?php if ( ! empty( $website_url ) ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-btn-outline">
                                🌐 <?php esc_html_e( 'Official Website', 'savika-news-radio' ); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-btn-outline">
                                📻 <?php esc_html_e( 'Zeno.fm Profile', 'savika-news-radio' ); ?>
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo esc_url( $directory_url ); ?>" class="snrd-btn-outline">
                            ← <?php esc_html_e( 'All Stations', 'savika-news-radio' ); ?>
                        </a>
                    </div>

                </div>

            </div>

            <!-- Station Description & Bio -->
            <div class="snrd-profile-content-section">
                <h2><?php esc_html_e( 'About', 'savika-news-radio' ); ?> <?php the_title(); ?></h2>
                <div class="snrd-station-bio">
                    <?php 
                    the_content(); 
                    ?>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="snrd-station-disclaimer">
                <p>
                    <small>
                        <?php esc_html_e( 'Savika News Radio Directory is an open platform directory. Audio feeds and broadcast content are hosted and transmitted directly by the respective broadcasters and their streaming providers (Zeno.fm/Icecast).', 'savika-news-radio' ); ?>
                    </small>
                </p>
            </div>

        </article>

    </div>
</div>

<?php
get_footer();
