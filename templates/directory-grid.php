<?php
/**
 * Professional Radio Directory Template (Modern News Portal Style)
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$genres = get_terms( array(
    'taxonomy'   => SNRD_TAXONOMY_GENRE,
    'hide_empty' => false,
) );
?>

<div class="snrd-portal-wrap" id="snrd-radio-portal">

    <!-- Directory Header -->
    <header class="snrd-portal-header">
        <div class="snrd-header-badge">
            <span class="snrd-live-dot"></span>
            <span>LIVE BROADCAST NETWORK</span>
        </div>
        <h1 class="snrd-header-title"><?php esc_html_e( 'Savika News Radio Directory', 'savika-news-radio' ); ?></h1>
        <p class="snrd-header-desc">
            <?php esc_html_e( 'Discover and listen to live Ghanaian and international radio stations online.', 'savika-news-radio' ); ?>
        </p>

        <!-- Search Bar -->
        <div class="snrd-search-container">
            <div class="snrd-search-box">
                <svg class="snrd-icon-search" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input 
                    type="text" 
                    id="snrd-station-search" 
                    class="snrd-search-input" 
                    placeholder="<?php esc_attr_e( 'Search radio station by name, frequency, or city...', 'savika-news-radio' ); ?>" 
                    autocomplete="off"
                />
                <button type="button" id="snrd-search-clear" class="snrd-search-clear-btn" style="display:none;" aria-label="Clear search">&times;</button>
            </div>
        </div>
    </header>

    <!-- Genre Filter Tabs -->
    <nav class="snrd-filter-nav" aria-label="Filter by genre">
        <div class="snrd-filter-tabs">
            <button type="button" class="snrd-filter-tab active" data-genre="all">
                <?php esc_html_e( 'All Stations', 'savika-news-radio' ); ?>
            </button>
            <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                <?php foreach ( $genres as $genre ) : ?>
                    <button type="button" class="snrd-filter-tab" data-genre="<?php echo esc_attr( $genre->slug ); ?>">
                        <?php echo esc_html( $genre->name ); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Stations Grid -->
    <div class="snrd-stations-grid" id="snrd-station-grid">
        <?php if ( $stations_query->have_posts() ) : ?>
            <?php while ( $stations_query->have_posts() ) : $stations_query->the_post(); 
                $post_id       = get_the_ID();
                $location      = get_post_meta( $post_id, 'snrd_location', true );
                $frequency     = get_post_meta( $post_id, 'snrd_frequency', true );
                $stream_url    = get_post_meta( $post_id, 'snrd_stream_url', true );
                $zeno_url      = get_post_meta( $post_id, 'snrd_zeno_url', true );
                $station_terms = get_the_terms( $post_id, SNRD_TAXONOMY_GENRE );
                $genre_slugs   = array();
                $genre_names   = array();

                if ( ! empty( $station_terms ) && ! is_wp_error( $station_terms ) ) {
                    foreach ( $station_terms as $term ) {
                        $genre_slugs[] = $term->slug;
                        $genre_names[] = $term->name;
                    }
                }
                $genres_data_attr = implode( ' ', $genre_slugs );
            ?>
                <article 
                    class="snrd-station-card" 
                    data-station-title="<?php echo esc_attr( strtolower( get_the_title() ) ); ?>" 
                    data-location="<?php echo esc_attr( strtolower( $location ) ); ?>" 
                    data-frequency="<?php echo esc_attr( strtolower( $frequency ) ); ?>"
                    data-genres="<?php echo esc_attr( $genres_data_attr ); ?>"
                >
                    <div class="snrd-card-header">
                        <div class="snrd-card-thumb">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', array( 'class' => 'snrd-station-image', 'alt' => esc_attr( get_the_title() ) ) ); ?>
                            <?php else : ?>
                                <div class="snrd-thumb-placeholder">
                                    <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="snrd-card-title-wrap">
                            <div class="snrd-card-meta-line">
                                <?php if ( ! empty( $frequency ) ) : ?>
                                    <span class="snrd-dial-badge"><?php echo esc_html( $frequency ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $genre_names ) ) : ?>
                                    <span class="snrd-genre-tag"><?php echo esc_html( $genre_names[0] ); ?></span>
                                <?php endif; ?>
                            </div>

                            <h2 class="snrd-station-name">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <?php if ( ! empty( $location ) ) : ?>
                                <div class="snrd-station-location">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span><?php echo esc_html( $location ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Direct Native HTML5 Audio Stream Player (100% Reliable Playback) -->
                    <div class="snrd-card-player-area">
                        <?php if ( ! empty( $stream_url ) ) : ?>
                            <div class="snrd-audio-control-wrap">
                                <audio class="snrd-live-audio" controls preload="none" playsinline>
                                    <source src="<?php echo esc_url( $stream_url ); ?>" />
                                    <?php esc_html_e( 'Audio playback not supported on your browser.', 'savika-news-radio' ); ?>
                                </audio>
                            </div>
                        <?php else : ?>
                            <div class="snrd-no-stream-msg">
                                <?php esc_html_e( 'Stream link pending verification.', 'savika-news-radio' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Footer Links -->
                    <div class="snrd-card-footer">
                        <a href="<?php the_permalink(); ?>" class="snrd-footer-btn-details">
                            <?php esc_html_e( 'Station Profile', 'savika-news-radio' ); ?> &rarr;
                        </a>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-footer-btn-zeno" title="<?php esc_attr_e( 'Open on Zeno.fm', 'savika-news-radio' ); ?>">
                                Zeno.fm
                            </a>
                        <?php endif; ?>
                    </div>

                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="snrd-empty-directory">
                <p><strong><?php esc_html_e( 'No radio stations are published yet.', 'savika-news-radio' ); ?></strong></p>
                <p><?php esc_html_e( 'Please check back shortly or submit your station application.', 'savika-news-radio' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- No Search Results Found Notice -->
    <div id="snrd-no-search-results" class="snrd-empty-directory" style="display:none;">
        <p><strong><?php esc_html_e( 'No radio stations matched your search query.', 'savika-news-radio' ); ?></strong></p>
        <p><?php esc_html_e( 'Try different search terms or select "All Stations".', 'savika-news-radio' ); ?></p>
    </div>

    <!-- Broadcaster Callout Banner -->
    <section class="snrd-broadcast-callout">
        <div class="snrd-callout-content">
            <h3 class="snrd-callout-heading"><?php esc_html_e( 'Broadcast Your Radio Station on Savika News', 'savika-news-radio' ); ?></h3>
            <p class="snrd-callout-desc">
                <?php esc_html_e( 'Reach thousands of online listeners worldwide. We offer free directory listings for licensed FM stations, online broadcasters, gospel channels, and community stations.', 'savika-news-radio' ); ?>
            </p>
        </div>
        <div class="snrd-callout-action">
            <a href="<?php echo esc_url( home_url( '/radio/apply/' ) ); ?>" class="snrd-callout-button">
                <?php esc_html_e( 'Submit Station Listing', 'savika-news-radio' ); ?> &rarr;
            </a>
        </div>
    </section>

</div>
