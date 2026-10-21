<?php
/**
 * Directory Grid Template
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="snrd-directory-container" id="snrd-directory">

    <!-- Directory Controls: Search and Genre Filter -->
    <div class="snrd-controls-bar">
        <div class="snrd-search-wrapper">
            <span class="snrd-search-icon">🔍</span>
            <input 
                type="text" 
                id="snrd-station-search" 
                class="snrd-search-input" 
                placeholder="<?php esc_attr_e( 'Search radio station by name or location...', 'savika-news-radio' ); ?>" 
                aria-label="<?php esc_attr_e( 'Search radio station', 'savika-news-radio' ); ?>"
            />
            <button type="button" id="snrd-search-clear" class="snrd-search-clear" style="display:none;" aria-label="<?php esc_attr_e( 'Clear search', 'savika-news-radio' ); ?>">&times;</button>
        </div>

        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
            <div class="snrd-genre-filters" role="tablist" aria-label="<?php esc_attr_e( 'Filter by genre', 'savika-news-radio' ); ?>">
                <button type="button" class="snrd-genre-pill active" data-genre="all">
                    <?php esc_html_e( 'All Stations', 'savika-news-radio' ); ?>
                </button>
                <?php foreach ( $genres as $genre ) : ?>
                    <button type="button" class="snrd-genre-pill" data-genre="<?php echo esc_attr( $genre->slug ); ?>">
                        <?php echo esc_html( $genre->name ); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stations Grid -->
    <div class="snrd-station-grid" id="snrd-station-grid">
        <?php if ( $stations_query->have_posts() ) : ?>
            <?php while ( $stations_query->have_posts() ) : $stations_query->the_post(); 
                $post_id     = get_the_ID();
                $location    = get_post_meta( $post_id, 'snrd_location', true );
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
            ?>
                <article class="snrd-station-card" data-station-title="<?php echo esc_attr( strtolower( get_the_title() ) ); ?>" data-location="<?php echo esc_attr( strtolower( $location ) ); ?>" data-genres="<?php echo esc_attr( $genres_data_attr ); ?>">
                    
                    <div class="snrd-card-top">
                        <div class="snrd-logo-wrap">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', array( 'class' => 'snrd-station-logo', 'alt' => esc_attr( get_the_title() ) ) ); ?>
                            <?php else : ?>
                                <div class="snrd-logo-placeholder">
                                    <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="snrd-card-header-info">
                            <h3 class="snrd-station-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <?php if ( ! empty( $location ) ) : ?>
                                <div class="snrd-station-meta snrd-location">
                                    <span class="snrd-meta-icon">📍</span>
                                    <span><?php echo esc_html( $location ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $genre_names ) ) : ?>
                                <div class="snrd-genre-badges">
                                    <?php foreach ( $genre_names as $name ) : ?>
                                        <span class="snrd-badge"><?php echo esc_html( $name ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ( has_excerpt() || ! empty( get_the_content() ) ) : ?>
                        <div class="snrd-station-description">
                            <?php echo wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 18, '...' ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Audio Player Section -->
                    <div class="snrd-player-box">
                        <?php if ( ! empty( $stream_url ) ) : ?>
                            <div class="snrd-audio-wrapper">
                                <audio class="snrd-audio-player" controls preload="none" playsinline>
                                    <source src="<?php echo esc_url( $stream_url ); ?>">
                                    <?php esc_html_e( 'Your browser does not support HTML5 audio streaming.', 'savika-news-radio' ); ?>
                                </audio>
                            </div>
                        <?php else : ?>
                            <div class="snrd-stream-missing">
                                <small><?php esc_html_e( 'Stream link pending verification.', 'savika-news-radio' ); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer actions -->
                    <div class="snrd-card-footer">
                        <a href="<?php the_permalink(); ?>" class="snrd-btn-details">
                            <?php esc_html_e( 'Station Page & Info →', 'savika-news-radio' ); ?>
                        </a>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-btn-zeno" title="<?php esc_attr_e( 'Open Zeno.fm page', 'savika-news-radio' ); ?>">
                                <?php esc_html_e( 'Zeno.fm Profile', 'savika-news-radio' ); ?> ↗
                            </a>
                        <?php endif; ?>
                    </div>

                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="snrd-no-stations">
                <p><strong><?php esc_html_e( 'No radio stations are published at the moment.', 'savika-news-radio' ); ?></strong></p>
                <p><?php esc_html_e( 'Are you an online radio broadcaster or station manager? Apply for your free listing below!', 'savika-news-radio' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Live Search No Matches Notice (Hidden by default) -->
    <div id="snrd-no-search-results" class="snrd-no-stations" style="display: none;">
        <p><strong><?php esc_html_e( 'No stations found matching your search.', 'savika-news-radio' ); ?></strong></p>
        <p><?php esc_html_e( 'Try different keywords or view all categories.', 'savika-news-radio' ); ?></p>
    </div>

    <!-- Apply To Broadcast Callout Banner -->
    <div class="snrd-apply-cta-card">
        <div class="snrd-cta-text">
            <h3><?php esc_html_e( 'Broadcast Your Radio Station on Savika News', 'savika-news-radio' ); ?></h3>
            <p><?php esc_html_e( 'Reach thousands of online listeners worldwide. Free directory listing for online radio, community stations, gospel and talk shows.', 'savika-news-radio' ); ?></p>
        </div>
        <div class="snrd-cta-action">
            <a href="<?php echo esc_url( home_url( '/radio/apply/' ) ); ?>" class="snrd-cta-button">
                <?php esc_html_e( 'Apply for Free Listing →', 'savika-news-radio' ); ?>
            </a>
        </div>
    </div>

</div>
