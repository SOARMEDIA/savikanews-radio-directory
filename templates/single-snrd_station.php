<?php
/**
 * Modern Station Profile Template
 * Inspired by modern editorial profile cards
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
$phone         = get_post_meta( $post_id, 'snrd_contact_phone', true );
$scope         = get_post_meta( $post_id, 'snrd_station_scope', true );
$genres        = get_the_terms( $post_id, SNRD_TAXONOMY_GENRE );
$directory_url = home_url( '/radio/' );
$current_url   = get_permalink();
$share_title   = rawurlencode( get_the_title() . ' — Listen Live on Savika News Radio' );

if ( empty( $location ) ) {
    $location = 'Ghana';
}
if ( empty( $frequency ) ) {
    $frequency = 'Online HD';
}
$scope_label = ( 'diaspora' === $scope ) ? 'Diaspora Broadcaster' : 'Ghana Broadcaster';
?>

<div class="snrd-station-single-wrap">
    <div class="snrd-single-container">

        <!-- Breadcrumbs -->
        <nav class="snrd-breadcrumbs-bar" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <span class="snrd-bread-sep">/</span>
            <a href="<?php echo esc_url( $directory_url ); ?>">Radio Directory</a>
            <span class="snrd-bread-sep">/</span>
            <span class="snrd-bread-current"><?php the_title(); ?></span>
        </nav>

        <!-- Master Station Profile Card -->
        <div class="snrd-pro-card-master">
            
            <!-- Top Card Cover Banner -->
            <div class="snrd-pro-card-cover">
                <div class="snrd-cover-overlay"></div>
                <div class="snrd-cover-top-badges">
                    <span class="snrd-badge-live">
                        <span class="snrd-beacon-dot"></span> LIVE BROADCAST
                    </span>
                    <span class="snrd-badge-dial">
                        <?php echo esc_html( $frequency ); ?>
                    </span>
                </div>
            </div>

            <!-- Profile Identity & Main Deck -->
            <div class="snrd-pro-card-body">
                
                <!-- Circular Avatar Overlapping Cover -->
                <div class="snrd-avatar-container">
                    <div class="snrd-avatar-frame">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium', array( 'class' => 'snrd-avatar-img', 'alt' => esc_attr( get_the_title() ) ) ); ?>
                        <?php else : ?>
                            <div class="snrd-avatar-default">
                                <svg width="42" height="42" viewBox="0 0 24 24" fill="#ffffff"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Station Header Info -->
                <div class="snrd-pro-header-meta">
                    <div class="snrd-title-row">
                        <h1 class="snrd-station-name"><?php the_title(); ?></h1>
                        <span class="snrd-verified-check" title="Verified Broadcast Station">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#006537"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        </span>
                    </div>

                    <div class="snrd-subtitle-strip">
                        <span class="snrd-sub-item location">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?php echo esc_html( $location ); ?>
                        </span>

                        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                            <span class="snrd-sub-sep">•</span>
                            <?php foreach ( $genres as $genre ) : ?>
                                <span class="snrd-genre-tag"><?php echo esc_html( $genre->name ); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ( ! empty( $phone ) ) : ?>
                            <span class="snrd-sub-sep">•</span>
                            <span class="snrd-sub-item phone"><?php echo esc_html( $phone ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3 Metric Pill Stats (Matching Image 1 & 2) -->
                <div class="snrd-metrics-bar">
                    <div class="snrd-metric-pill">
                        <span class="metric-icon star">&#9733;</span>
                        <div class="metric-info">
                            <strong>5.0</strong>
                            <span>Audience Rating</span>
                        </div>
                    </div>

                    <div class="snrd-metric-pill">
                        <span class="metric-icon scope">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        </span>
                        <div class="metric-info">
                            <strong><?php echo esc_html( $scope_label ); ?></strong>
                            <span>Broadcast Scope</span>
                        </div>
                    </div>

                    <div class="snrd-metric-pill">
                        <span class="metric-icon clock">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </span>
                        <div class="metric-info">
                            <strong>24/7 Stream</strong>
                            <span>Online Schedule</span>
                        </div>
                    </div>
                </div>

                <!-- Integrated Live Audio Player Deck -->
                <div class="snrd-profile-audio-deck">
                    <div class="deck-top">
                        <div class="deck-status">
                            <span class="snrd-beacon-dot"></span>
                            <span id="snrd-single-status">Tune In Live Stream</span>
                        </div>
                    </div>

                    <?php if ( ! empty( $stream_url ) ) : ?>
                        <div class="deck-player-wrap">
                            <audio id="snrd-station-audio" controls preload="none" playsinline style="width: 100%; height: 40px;">
                                <source src="<?php echo esc_url( $stream_url ); ?>" />
                                Your browser does not support HTML5 audio playback.
                            </audio>
                        </div>
                    <?php else : ?>
                        <div class="snrd-stream-offline-box">
                            Broadcast stream is currently updating. Check official station page.
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $zeno_url ) ) : ?>
                        <div class="deck-zeno-hint">
                            <span>Experiencing buffering?</span>
                            <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer">Open Zeno.fm Stream Page &rarr;</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action & Social Share Bar -->
                <div class="snrd-action-share-bar">
                    <div class="snrd-main-actions">
                        <?php if ( ! empty( $website_url ) ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-btn-act website">
                                Visit Official Website
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $directory_url ); ?>" class="snrd-btn-act directory">
                            &larr; All Radio Stations
                        </a>
                    </div>

                    <div class="snrd-share-pill-group">
                        <span class="share-label">Share Station:</span>
                        <a href="https://api.whatsapp.com/send?text=<?php echo $share_title . '%20' . rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="share-btn whatsapp" title="Share on WhatsApp">WhatsApp</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="share-btn facebook" title="Share on Facebook">Facebook</a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo $share_title; ?>&url=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="share-btn twitter" title="Share on X">X</a>
                    </div>
                </div>

            </div>

        </div>

        <!-- Editorial About Section & Related Stations Grid -->
        <div class="snrd-two-col-layout">
            
            <!-- Main Column: Editorial Bio -->
            <main class="snrd-main-column">
                <section class="snrd-about-card">
                    <h2 class="snrd-section-title">About <?php the_title(); ?></h2>
                    <div class="snrd-article-body">
                        <?php 
                        $content = get_the_content();
                        if ( ! empty( $content ) ) {
                            the_content();
                        } else {
                            echo '<p>' . esc_html( get_the_title() ) . ' broadcasts live from ' . esc_html( $location ) . ' (' . esc_html( $frequency ) . '), delivering high quality radio programming, news, talk, and music.</p>';
                        }
                        ?>
                    </div>
                </section>
            </main>

            <!-- Sidebar Column: More Station Profile Cards -->
            <aside class="snrd-side-column">
                <div class="snrd-sidebar-card">
                    <h3 class="snrd-sidebar-title">Featured Stations</h3>

                    <?php 
                    $other_stations = new WP_Query( array(
                        'post_type'      => SNRD_POST_TYPE,
                        'post_status'    => 'publish',
                        'post__not_in'   => array( $post_id ),
                        'posts_per_page' => 5,
                        'orderby'        => 'rand'
                    ) );
                    ?>

                    <?php if ( $other_stations->have_posts() ) : ?>
                        <div class="snrd-sidebar-station-list">
                            <?php while ( $other_stations->have_posts() ) : $other_stations->the_post(); 
                                $side_loc  = get_post_meta( get_the_ID(), 'snrd_location', true );
                                $side_freq = get_post_meta( get_the_ID(), 'snrd_frequency', true );
                            ?>
                                <a href="<?php the_permalink(); ?>" class="snrd-sidebar-item">
                                    <div class="snrd-sidebar-thumb">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <?php the_post_thumbnail( 'thumbnail' ); ?>
                                        <?php else : ?>
                                            <div class="snrd-mini-placeholder">RADIO</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="snrd-sidebar-meta">
                                        <h4 class="snrd-sidebar-station-title"><?php the_title(); ?></h4>
                                        <span class="snrd-sidebar-loc">
                                            <?php echo esc_html( $side_freq ? $side_freq : $side_loc ); ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="snrd-sidebar-cta">
                        <a href="<?php echo esc_url( $directory_url ); ?>" class="snrd-btn-all-stations">
                            View All Stations &rarr;
                        </a>
                    </div>
                </div>
            </aside>

        </div>

    </div>
</div>

<?php
get_footer();
