<?php
/**
 * Modern High-Impact Radio Directory Grid Template
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

$total_stations = $stations_query->found_posts;
?>

<div class="snrd-modern-app" id="snrd-radio-app">

    <!-- Top Hero Banner -->
    <header class="snrd-hero-header">
        <div class="snrd-hero-content">
            <div class="snrd-live-indicator">
                <span class="snrd-pulse-orb"></span>
                <span class="snrd-pulse-text"><?php esc_html_e( 'SAVIKA NEWS RADIO NETWORK', 'savika-news-radio' ); ?></span>
            </div>
            <h1 class="snrd-hero-title"><?php esc_html_e( 'Discover & Listen to Live Radio Stations', 'savika-news-radio' ); ?></h1>
            <p class="snrd-hero-subtitle">
                <?php esc_html_e( 'Stream Ghana\'s top news, talk, gospel, sports, and music broadcasters in high-definition audio.', 'savika-news-radio' ); ?>
            </p>

            <!-- Search Bar Inside Hero -->
            <div class="snrd-hero-search-box">
                <div class="snrd-search-inner">
                    <span class="snrd-search-svg-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input 
                        type="text" 
                        id="snrd-station-search" 
                        class="snrd-search-input-field" 
                        placeholder="<?php esc_attr_e( 'Search by station name, dial (e.g. 106.1), or city (e.g. Mankessim, Accra)...', 'savika-news-radio' ); ?>" 
                        autocomplete="off"
                    />
                    <button type="button" id="snrd-search-clear" class="snrd-clear-btn" style="display:none;" title="<?php esc_attr_e( 'Clear search', 'savika-news-radio' ); ?>">&times;</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Genre Category Tabs & Counter -->
    <div class="snrd-toolbar-section">
        <div class="snrd-genre-scroll-wrapper">
            <div class="snrd-genre-tabs" role="tablist">
                <button type="button" class="snrd-tab-pill active" data-genre="all">
                    <span class="snrd-tab-icon">📻</span>
                    <span><?php esc_html_e( 'All Stations', 'savika-news-radio' ); ?></span>
                </button>
                <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                    <?php 
                    $genre_icons = array(
                        'news'        => '📰',
                        'talk'        => '🎙️',
                        'gospel'      => '🙏',
                        'sports'      => '⚽',
                        'music'       => '🎵',
                        'community'   => '🌍',
                        'educational' => '🎓',
                        'other'       => '📡'
                    );
                    foreach ( $genres as $genre ) : 
                        $icon = isset( $genre_icons[ strtolower( $genre->slug ) ] ) ? $genre_icons[ strtolower( $genre->slug ) ] : '📻';
                    ?>
                        <button type="button" class="snrd-tab-pill" data-genre="<?php echo esc_attr( $genre->slug ); ?>">
                            <span class="snrd-tab-icon"><?php echo esc_html( $icon ); ?></span>
                            <span><?php echo esc_html( $genre->name ); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="snrd-stations-count">
            <span id="snrd-count-display"><?php echo intval( $total_stations ); ?></span> <?php esc_html_e( 'Stations Available', 'savika-news-radio' ); ?>
        </div>
    </div>

    <!-- Stations Grid -->
    <main class="snrd-grid-container" id="snrd-station-grid">
        <?php if ( $stations_query->have_posts() ) : ?>
            <?php while ( $stations_query->have_posts() ) : $stations_query->the_post(); 
                $post_id     = get_the_ID();
                $location    = get_post_meta( $post_id, 'snrd_location', true );
                $frequency   = get_post_meta( $post_id, 'snrd_frequency', true );
                $stream_url  = get_post_meta( $post_id, 'snrd_stream_url', true );
                $zeno_url    = get_post_meta( $post_id, 'snrd_zeno_url', true );
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
                $logo_url = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'medium' ) : '';
            ?>
                <article 
                    class="snrd-card" 
                    data-station-title="<?php echo esc_attr( strtolower( get_the_title() ) ); ?>" 
                    data-location="<?php echo esc_attr( strtolower( $location ) ); ?>" 
                    data-frequency="<?php echo esc_attr( strtolower( $frequency ) ); ?>"
                    data-genres="<?php echo esc_attr( $genres_data_attr ); ?>"
                    data-stream-url="<?php echo esc_url( $stream_url ); ?>"
                    data-station-name="<?php echo esc_attr( get_the_title() ); ?>"
                    data-station-logo="<?php echo esc_url( $logo_url ); ?>"
                    data-station-permalink="<?php the_permalink(); ?>"
                >
                    <!-- Card Artwork / Logo -->
                    <div class="snrd-card-visual">
                        <div class="snrd-card-artwork">
                            <?php if ( ! empty( $logo_url ) ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="snrd-artwork-img" />
                            <?php else : ?>
                                <div class="snrd-artwork-placeholder">
                                    <svg viewBox="0 0 24 24" width="44" height="44" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                                </div>
                            <?php endif; ?>

                            <!-- Live On-Air Pill -->
                            <div class="snrd-live-tag">
                                <span class="snrd-red-dot"></span> LIVE
                            </div>

                            <!-- Big Play Overlay Trigger -->
                            <?php if ( ! empty( $stream_url ) ) : ?>
                                <button type="button" class="snrd-play-overlay-btn" aria-label="<?php esc_attr_e( 'Play Station', 'savika-news-radio' ); ?>">
                                    <svg class="snrd-icon-play" viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    <svg class="snrd-icon-pause" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" style="display:none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="snrd-card-body">
                        <div class="snrd-card-badges">
                            <?php if ( ! empty( $frequency ) ) : ?>
                                <span class="snrd-badge-dial"><?php echo esc_html( $frequency ); ?></span>
                            <?php endif; ?>
                            <?php if ( ! empty( $genre_names ) ) : ?>
                                <span class="snrd-badge-genre"><?php echo esc_html( $genre_names[0] ); ?></span>
                            <?php endif; ?>
                        </div>

                        <h3 class="snrd-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <?php if ( ! empty( $location ) ) : ?>
                            <div class="snrd-card-location">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span><?php echo esc_html( $location ); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Equalizer Waveform Animation (Visible while playing) -->
                        <div class="snrd-equalizer-bars" style="display:none;">
                            <span class="eq-bar bar-1"></span>
                            <span class="eq-bar bar-2"></span>
                            <span class="eq-bar bar-3"></span>
                            <span class="eq-bar bar-4"></span>
                            <span class="eq-bar bar-5"></span>
                            <small class="eq-status-text"><?php esc_html_e( 'Streaming Live', 'savika-news-radio' ); ?></small>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="snrd-card-actions">
                        <a href="<?php the_permalink(); ?>" class="snrd-action-link primary">
                            <?php esc_html_e( 'Station Details', 'savika-news-radio' ); ?> →
                        </a>
                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-action-link zeno" title="<?php esc_attr_e( 'Official Zeno.fm Page', 'savika-news-radio' ); ?>">
                                📻 Zeno.fm
                            </a>
                        <?php endif; ?>
                    </div>

                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="snrd-empty-notice">
                <div class="snrd-empty-icon">📻</div>
                <h3><?php esc_html_e( 'No Stations Found', 'savika-news-radio' ); ?></h3>
                <p><?php esc_html_e( 'Radio stations are being added. Check back shortly or apply to list your station below.', 'savika-news-radio' ); ?></p>
            </div>
        <?php endif; ?>
    </main>

    <!-- No Live Search Results Message -->
    <div id="snrd-no-search-results" class="snrd-empty-notice" style="display:none;">
        <div class="snrd-empty-icon">🔍</div>
        <h3><?php esc_html_e( 'No stations matched your search', 'savika-news-radio' ); ?></h3>
        <p><?php esc_html_e( 'Try different keywords, frequency numbers, or click "All Stations" to reset filters.', 'savika-news-radio' ); ?></p>
    </div>

    <!-- Apply to Broadcast Callout Banner -->
    <section class="snrd-apply-banner">
        <div class="snrd-apply-inner">
            <div class="snrd-apply-copy">
                <span class="snrd-tag-broadcast"><?php esc_html_e( 'FOR BROADCASTERS & STUDIOS', 'savika-news-radio' ); ?></span>
                <h2><?php esc_html_e( 'Broadcast Your Radio Station on Savika News', 'savika-news-radio' ); ?></h2>
                <p><?php esc_html_e( 'Are you an FM station, online broadcaster, church, or community station? Apply for a free verified listing on Savika News Radio and reach listeners worldwide.', 'savika-news-radio' ); ?></p>
            </div>
            <div class="snrd-apply-action">
                <a href="<?php echo esc_url( home_url( '/radio/apply/' ) ); ?>" class="snrd-btn-apply-cta">
                    <span class="dashicons dashicons-microphone"></span>
                    <?php esc_html_e( 'Apply for Free Listing →', 'savika-news-radio' ); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Persistent Bottom Sticky Player Dock (Appears when any station is playing) -->
    <div class="snrd-sticky-player-bar" id="snrd-sticky-player" style="display:none;">
        <div class="snrd-sticky-inner">
            
            <div class="snrd-sticky-station-info">
                <img id="snrd-sticky-logo" src="" alt="Station artwork" class="snrd-sticky-artwork" />
                <div class="snrd-sticky-text">
                    <h4 id="snrd-sticky-title">Station Name</h4>
                    <span id="snrd-sticky-status" class="snrd-sticky-status-badge">
                        <span class="snrd-pulse-dot"></span> LIVE ON AIR
                    </span>
                </div>
            </div>

            <!-- Master Playback Controls -->
            <div class="snrd-sticky-controls">
                <button type="button" id="snrd-sticky-play-btn" class="snrd-sticky-play-trigger" aria-label="<?php esc_attr_e( 'Toggle Playback', 'savika-news-radio' ); ?>">
                    <svg class="snrd-sticky-icon-play" viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    <svg class="snrd-sticky-icon-pause" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" style="display:none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>
            </div>

            <!-- Volume Control & Station Link -->
            <div class="snrd-sticky-extras">
                <div class="snrd-volume-wrap">
                    <span class="snrd-vol-icon">🔊</span>
                    <input type="range" id="snrd-sticky-volume" min="0" max="1" step="0.05" value="1" aria-label="<?php esc_attr_e( 'Volume', 'savika-news-radio' ); ?>" />
                </div>
                <a href="#" id="snrd-sticky-details-link" class="snrd-sticky-page-btn">
                    <?php esc_html_e( 'Station Page', 'savika-news-radio' ); ?> →
                </a>
                <button type="button" id="snrd-sticky-close-btn" class="snrd-sticky-close" title="<?php esc_attr_e( 'Close Player', 'savika-news-radio' ); ?>">&times;</button>
            </div>

        </div>
        <!-- Hidden Master Audio Element -->
        <audio id="snrd-master-audio" preload="none"></audio>
    </div>

</div>
