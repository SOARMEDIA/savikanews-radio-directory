<?php
/**
 * Professional Single Station Template (News Portal Style)
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
$current_url   = get_permalink();
$share_title   = rawurlencode( get_the_title() . ' — Listen Live on Savika News Radio' );
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

        <!-- Station Profile Header Card -->
        <header class="snrd-profile-card">
            <div class="snrd-profile-grid">
                
                <!-- Station Logo Column -->
                <div class="snrd-profile-logo-col">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'large', array( 'class' => 'snrd-profile-img', 'alt' => esc_attr( get_the_title() ) ) ); ?>
                    <?php else : ?>
                        <div class="snrd-profile-placeholder-box">
                            <svg viewBox="0 0 24 24" width="56" height="56" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Station Info & Player Column -->
                <div class="snrd-profile-details-col">
                    
                    <div class="snrd-profile-meta-tags">
                        <span class="snrd-live-tag">
                            <span class="snrd-live-dot"></span> LIVE BROADCAST
                        </span>

                        <?php if ( ! empty( $frequency ) ) : ?>
                            <span class="snrd-meta-pill dial"><?php echo esc_html( $frequency ); ?></span>
                        <?php endif; ?>

                        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                            <?php foreach ( $genres as $genre ) : ?>
                                <span class="snrd-meta-pill genre"><?php echo esc_html( $genre->name ); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ( ! empty( $location ) ) : ?>
                            <span class="snrd-meta-pill location">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <?php echo esc_html( $location ); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="snrd-profile-name"><?php the_title(); ?></h1>

                    <!-- Live Stream Player Section -->
                    <div class="snrd-live-player-box">
                        <div class="snrd-player-label">
                            <strong>Live Stream Audio</strong>
                        </div>

                        <?php if ( ! empty( $stream_url ) ) : ?>
                            <div class="snrd-stream-player-wrap">
                                <audio class="snrd-master-single-player" controls preload="none" playsinline>
                                    <source src="<?php echo esc_url( $stream_url ); ?>" />
                                    <?php esc_html_e( 'Audio playback not supported on your browser.', 'savika-news-radio' ); ?>
                                </audio>
                            </div>
                        <?php else : ?>
                            <div class="snrd-stream-pending-msg">
                                <?php esc_html_e( 'Live broadcast stream is currently being updated.', 'savika-news-radio' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $zeno_url ) ) : ?>
                            <div class="snrd-fallback-bar">
                                <span><?php esc_html_e( 'Audio buffering?', 'savika-news-radio' ); ?></span>
                                <a href="<?php echo esc_url( $zeno_url ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Open Official Zeno.fm Listening Page', 'savika-news-radio' ); ?> &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Clean Social Share Bar -->
                    <div class="snrd-share-row">
                        <span class="snrd-share-heading"><?php esc_html_e( 'Share:', 'savika-news-radio' ); ?></span>
                        
                        <a href="https://api.whatsapp.com/send?text=<?php echo $share_title . '%20' . rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-link whatsapp">
                            WhatsApp
                        </a>

                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-link facebook">
                            Facebook
                        </a>

                        <a href="https://twitter.com/intent/tweet?text=<?php echo $share_title; ?>&url=<?php echo rawurlencode( $current_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-link twitter">
                            X
                        </a>

                        <?php if ( ! empty( $website_url ) ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer" class="snrd-share-link website">
                                Official Website
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </header>

        <!-- Editorial Content & Sidebar -->
        <div class="snrd-two-col-layout">
            
            <main class="snrd-main-column">
                <section class="snrd-about-card">
                    <h2 class="snrd-section-title"><?php esc_html_e( 'About', 'savika-news-radio' ); ?> <?php the_title(); ?></h2>
                    <div class="snrd-article-body">
                        <?php the_content(); ?>
                    </div>
                </section>
            </main>

            <aside class="snrd-side-column">
                <div class="snrd-sidebar-card">
                    <h3 class="snrd-sidebar-title"><?php esc_html_e( 'More Radio Stations', 'savika-news-radio' ); ?></h3>

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
                        <div class="snrd-sidebar-station-list">
                            <?php while ( $other_stations->have_posts() ) : $other_stations->the_post(); 
                                $side_loc = get_post_meta( get_the_ID(), 'snrd_location', true );
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
                                        <?php if ( $side_loc ) : ?>
                                            <span class="snrd-sidebar-loc"><?php echo esc_html( $side_loc ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="snrd-sidebar-cta">
                        <a href="<?php echo esc_url( $directory_url ); ?>" class="snrd-btn-all-stations">
                            &larr; <?php esc_html_e( 'All Radio Stations', 'savika-news-radio' ); ?>
                        </a>
                    </div>
                </div>
            </aside>

        </div>

    </div>
</div>

<?php
get_footer();
