<?php
/**
 * Modern Studio-Grade Single Radio Station Template
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$post_id       = get_the_ID();
$location      = get_post_meta( $post_id, 'snrd_location', true );
$frequency     = get_post_meta( $post_id, 'snrd_frequency', true );
$stream_url    = get_post_meta( $post_id, 'snrd_stream_url', true );
$zeno_url      = get_post_meta( $post_id, 'snrd_zeno_url', true );
$website_url   = get_post_meta( $post_id, 'snrd_website_url', true );
$genres        = get_the_terms( $post_id, SNRD_TAXONOMY_GENRE );
$directory_url = home_url( '/radio/' );
$logo_url      = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'large' ) : '';
$current_url   = get_permalink();
$share_title   = rawurlencode( get_the_title() . ' — Listen Live on Savika News Radio' );
?>

<div class="snrd-studio-page-wrapper">
    
    <!-- Studio Hero Atmosphere -->
    <div class="snrd-studio-hero">
        <?php if ( ! empty( $logo_url ) ) : ?>
            <div class="snrd-hero-backdrop" style="background-image: url('<?php echo esc_url( $logo_url ); ?>');"></div>
        <?php else : ?>
            <div class="snrd-hero-backdrop-default"></div>
        <?php endif; ?>
        <div class="snrd-hero-overlay"></div>

        <div class="snrd-studio-container">

            <!-- Breadcrumbs -->
            <nav class="snrd-studio-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
                <span class="sep">/</span>
                <a href="<?php echo esc_url( $directory_url ); ?>">Radio Directory</a>
                <span class="sep">/</span>
                <span class="current"><?php the_title(); ?></span>
            </nav>

            <div class="snrd-studio-stage">
                
                <!-- Station Artwork -->
                <div class="snrd-studio-art-wrap">
                    <?php if ( ! empty( $logo_url ) ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="snrd-studio-art-img" />
                    <?php else : ?>
                        <div class="snrd-studio-art-placeholder">
                            <svg viewBox="0 0 24 24" width="70" height="70" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="snrd-studio-live-badge">
                        <span class="dot"></span> ON AIR
                    </div>
                </div>

                <!-- Station Header Info & Master Player -->
                <div class="snrd-studio-meta-wrap">
                    
                    <div class="snrd-studio-tags">
                        <?php if ( ! empty( $frequency ) ) : ?>
                            <span class="snrd-tag-frequency">📻 <?php echo esc_html( $frequency ); ?></span>
                        <?php endif; ?>

                        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                            <?php foreach ( $genres as $genre ) : ?>
                                <span class="snrd-tag-genre"><?php echo esc_html( $genre->name ); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ( ! empty( $location ) ) : ?>
                            <span class="snrd-tag-location">📍 <?php echo esc_html( $location ); ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 class="snrd-studio-title"><?php the_title(); ?></h1>

                    <!-- Giant Master Live Player Card -->
                    <div class="snrd-master-player-card">
                        <?php if ( ! empty( $stream_url ) ) : ?>
                            <div class="snrd-player-row">
                                
                                <button type="button" class="snrd-master-play-btn" id="snrd-single-play-btn" data-stream="<?php echo esc_url( $stream_url ); ?>" aria-label="<?php esc_attr_e( 'Play Live Stream', 'savika-news-radio' ); ?>">
                                    <svg class="snrd-play-icon" viewBox="0 0 24 24" width="36" height="36" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    <svg class="snrd-pause-icon" viewBox="0 0 24 24" width="36" height="36" fill="currentColor" style="display:none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>

                                <div class="snrd-player-meta-box">
                                    <div class="snrd-player-status-line">
                                        <span class="snrd-pulse-indicator"></span>
                                        <span id="snrd-stream-status-text"><?php esc_html_e( 'Click Play to Listen Live', 'savika-news-radio' ); ?></span>
                                    </div>
                                    
                                    <!-- Animated Sound Waves -->
                                    <div class="snrd-waveform-animation" id="snrd-waveform" style="display:none;">
                                        <span class="wave wave-1"></span>
                                        <span class="wave wave-2"></span>
                                        <span class="wave wave-3"></span>
                                        <span class="wave wave-4"></span>
                                        <span class="wave wave-5"></span>
                                        <span class="wave wave-6"></span>
                                        <span class="wave wave-7"></span>
                                    </div>
                                </div>

                                <div class="snrd-player-volume-control">
                                    <span class="vol-icon">🔊</span>
                                    <input type="range" id="snrd-single-vol" min="0" max="1" step="0.05" value="1" aria-label="Volume" />
                                </div>

                            </div>

                            <audio id="snrd-single-audio-engine" preload="none">
                                <source src="<?php echo esc_url( $stream_url ); ?>" />
                            </audio>

                        <?php else : ?>
                            <div class="snrd-stream-unavailable">
                                <p>⚠️ <?php esc_html_e( 'Broadcast stream link is being scheduled. Please check back shortly.', 'savika-news-radio' ); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <div class="snrd-fallback-bar">
                                <span><?php esc_html_e( 'Stream buffering or interrupted?', 'savika-news-radio' ); ?></span>
                                <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Open Official Zeno.fm Player ↗', 'savika-news-radio' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Social Share Buttons -->
                    <div class="snrd-social-share-strip">
                        <span class="share-label"><?php esc_html_e( 'Share Station:', 'savika-news-radio' ); ?></span>
                        
                        <!-- WhatsApp Share -->
                        <a href="https://api.whatsapp.com/send?text=<?php echo $share_title . '%20' . rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-pill whatsapp">
                            <span>💬 WhatsApp</span>
                        </a>

                        <!-- Facebook Share -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-pill facebook">
                            <span>f Facebook</span>
                        </a>

                        <!-- X / Twitter Share -->
                        <a href="https://twitter.com/intent/tweet?text=<?php echo $share_title; ?>&url=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-pill twitter">
                            <span>𝕏 Post</span>
                        </a>

                        <?php if ( ! empty( $website_url ) ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-pill website">
                                <span>🌐 Official Site</span>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Station Story & Related Stations -->
    <div class="snrd-studio-container snrd-content-body-wrap">
        
        <div class="snrd-studio-main-col">
            <section class="snrd-bio-card">
                <h2><?php esc_html_e( 'About', 'savika-news-radio' ); ?> <?php the_title(); ?></h2>
                <div class="snrd-bio-prose">
                    <?php the_content(); ?>
                </div>
            </section>
        </div>

        <!-- Sidebar: More Live Stations -->
        <aside class="snrd-studio-side-col">
            <div class="snrd-more-stations-card">
                <h3><?php esc_html_e( 'Explore More Stations', 'savika-news-radio' ); ?></h3>

                <?php 
                $other_stations = new WP_Query( array(
                    'post_type'      => SNRD_POST_TYPE,
                    'post_status'    => 'publish',
                    'post__not_in'   => array( $post_id ),
                    'posts_per_page' => 4,
                    'orderby'        => 'rand'
                ) );
                ?>

                <?php if ( $other_stations->have_posts() ) : ?>
                    <div class="snrd-mini-station-list">
                        <?php while ( $other_stations->have_posts() ) : $other_stations->the_post(); 
                            $side_logo = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : '';
                            $side_loc  = get_post_meta( get_the_ID(), 'snrd_location', true );
                        ?>
                            <a href="<?php the_permalink(); ?>" class="snrd-mini-item">
                                <div class="mini-art">
                                    <?php if ( $side_logo ) : ?>
                                        <img src="<?php echo esc_url( $side_logo ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" />
                                    <?php else : ?>
                                        <div class="mini-placeholder">📻</div>
                                    <?php endif; ?>
                                </div>
                                <div class="mini-meta">
                                    <h4><?php the_title(); ?></h4>
                                    <?php if ( $side_loc ) : ?>
                                        <small><?php echo esc_html( $side_loc ); ?></small>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php endif; ?>

                <div class="snrd-all-stations-btn-wrap">
                    <a href="<?php echo esc_url( $directory_url ); ?>" class="snrd-btn-view-all">
                        ← <?php esc_html_e( 'Back to All Radio Stations', 'savika-news-radio' ); ?>
                    </a>
                </div>
            </div>
        </aside>

    </div>

</div>

<?php
get_footer();
