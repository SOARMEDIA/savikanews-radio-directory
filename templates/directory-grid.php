<?php
/**
 * Modern Ghana Radio Style Directory Template
 * Exact layout & features matching radio.modernghana.com
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Query all published radio stations
$all_stations_query = new WP_Query( array(
    'post_type'      => SNRD_POST_TYPE,
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC'
) );

$stations_data = array();
$first_station = null;

if ( $all_stations_query->have_posts() ) {
    while ( $all_stations_query->have_posts() ) {
        $all_stations_query->the_post();
        $pid        = get_the_ID();
        $title      = get_the_title();
        $stream_url = get_post_meta( $pid, 'snrd_stream_url', true );
        $location   = get_post_meta( $pid, 'snrd_location', true );
        $frequency  = get_post_meta( $pid, 'snrd_frequency', true );
        $phone      = get_post_meta( $pid, 'snrd_contact_phone', true );
        $website    = get_post_meta( $pid, 'snrd_website_url', true );
        $scope      = get_post_meta( $pid, 'snrd_station_scope', true ); // 'ghana' or 'diaspora'
        $is_feat    = get_post_meta( $pid, 'snrd_is_featured', true );
        $is_top     = get_post_meta( $pid, 'snrd_is_top', true );

        if ( empty( $location ) ) {
            $location = 'Ghana';
        }
        if ( empty( $phone ) ) {
            $phone = '+233 (0) 24 000 0000';
        }
        if ( empty( $website ) ) {
            $website = 'savikanews.com/radio';
        }
        if ( empty( $scope ) ) {
            $scope = ( false !== stripos( $location, 'UK' ) || false !== stripos( $location, 'US' ) || false !== stripos( $location, 'Diaspora' ) ) ? 'diaspora' : 'ghana';
        }

        $station_item = array(
            'id'        => $pid,
            'title'     => $title,
            'stream'    => $stream_url,
            'location'  => $location,
            'frequency' => $frequency,
            'phone'     => $phone,
            'website'   => $website,
            'scope'     => $scope,
            'featured'  => ( '1' === $is_feat ),
            'top'       => ( '1' === $is_top ),
            'permalink' => get_permalink( $pid ),
        );

        $stations_data[] = $station_item;

        if ( null === $first_station && ! empty( $stream_url ) ) {
            $first_station = $station_item;
        }
    }
    wp_reset_postdata();
}

if ( null === $first_station && ! empty( $stations_data ) ) {
    $first_station = $stations_data[0];
}

// Split stations by scope
$ghana_stations    = array_filter( $stations_data, function( $st ) { return 'diaspora' !== $st['scope']; } );
$diaspora_stations = array_filter( $stations_data, function( $st ) { return 'diaspora' === $st['scope']; } );

// Quick pick station slugs/names
$quick_pick_names = array(
    'PEACE', 'ADOM', 'NHYIRA', 'OKAY', 'OMAN', 'KESSBEN', 'ANGEL', 'HOT', 'FOX', 
    'OTEC', 'HELLO', 'NEAT', 'CITI', 'JOY', 'KINGDOM', 'FLOODLIGHT', 'AMANSAN', 'RAINBOW'
);
?>

<div class="mg-radio-container" id="mg-radio-app">
    
    <div class="mg-layout-row">

        <!-- ============================================== -->
        <!-- LEFT SIDEBAR: FEATURED & TOP STATIONS         -->
        <!-- ============================================== -->
        <aside class="mg-left-sidebar">
            
            <!-- Featured Stations Box -->
            <div class="mg-sidebar-box">
                <div class="mg-sidebar-header">
                    <h3>FEATURED STATIONS</h3>
                </div>
                <ul class="mg-sidebar-list">
                    <?php 
                    $featured_count = 0;
                    foreach ( $stations_data as $st ) :
                        if ( $st['featured'] || $featured_count < 8 ) :
                            $featured_count++;
                    ?>
                        <li>
                            <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                            </a>
                        </li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- Top Stations Box -->
            <div class="mg-sidebar-box">
                <div class="mg-sidebar-header">
                    <h3>TOP STATIONS</h3>
                </div>
                <ul class="mg-sidebar-list">
                    <?php 
                    $top_count = 0;
                    foreach ( $stations_data as $st ) :
                        if ( $st['top'] || $top_count < 14 ) :
                            $top_count++;
                    ?>
                        <li>
                            <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                            </a>
                        </li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- Apply Callout in sidebar -->
            <div class="mg-sidebar-apply">
                <a href="<?php echo esc_url( home_url( '/radio/apply/' ) ); ?>" class="mg-apply-btn">
                    + APPLY TO BROADCAST
                </a>
            </div>

        </aside>

        <!-- ============================================== -->
        <!-- CENTER MAIN CONSOLE: PLAYER, SEARCH, LISTINGS  -->
        <!-- ============================================== -->
        <main class="mg-main-stage">
            
            <!-- Black Master Radio Player Box -->
            <section class="mg-master-console" id="mg-master-console">
                
                <!-- Player Top Bar (Flag + Title + Stars) -->
                <div class="mg-player-top">
                    <div class="mg-title-group">
                        <span class="mg-flag-icon">🇬🇭</span>
                        <h2 class="mg-active-station-title" id="mg-active-title">
                            <?php echo $first_station ? esc_html( strtoupper( $first_station['title'] ) ) : 'SAVIKA NEWS RADIO'; ?>
                        </h2>
                    </div>
                    <div class="mg-rating-stars">
                        ★★★★★
                    </div>
                </div>

                <!-- Player Sub-Info Strip (Location, Phone, Website) -->
                <div class="mg-player-subinfo">
                    <div class="mg-sub-item mg-sub-location" id="mg-active-location">
                        <?php echo $first_station ? esc_html( $first_station['location'] ) : 'Ghana'; ?>
                    </div>
                    <div class="mg-sub-item mg-sub-phone" id="mg-active-phone">
                        <?php echo $first_station ? esc_html( $first_station['phone'] ) : '+233 00 000 0000'; ?>
                    </div>
                    <div class="mg-sub-item mg-sub-website" id="mg-active-website">
                        <?php echo $first_station ? esc_html( $first_station['website'] ) : 'savikanews.com'; ?>
                    </div>
                </div>

                <!-- HTML5 Audio Streaming Deck -->
                <div class="mg-audio-deck">
                    <div class="mg-deck-status">
                        <span class="mg-pulse-dot"></span>
                        <span id="mg-deck-status-text">Live Broadcast</span>
                    </div>

                    <div class="mg-audio-wrap">
                        <audio id="mg-audio-element" controls preload="none" playsinline style="width: 100%; height: 38px;">
                            <source src="<?php echo $first_station ? esc_url( $first_station['stream'] ) : ''; ?>" id="mg-audio-source" />
                            Your browser does not support live HTML5 audio streaming.
                        </audio>
                    </div>
                </div>

            </section>

            <!-- Search Field -->
            <div class="mg-search-bar">
                <input 
                    type="text" 
                    id="mg-search-input" 
                    class="mg-search-field" 
                    placeholder="Search a station from here..." 
                    autocomplete="off"
                />
                <button type="button" class="mg-search-icon-btn" aria-label="Search">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>

            <!-- Quick Station Buttons (Green Pills) -->
            <div class="mg-quick-stations-bar">
                <?php foreach ( $stations_data as $st ) : 
                    $clean_name = strtoupper( trim( preg_replace( '/[0-9\.\s]+FM/i', '', $st['title'] ) ) );
                    $clean_name = strtok( $clean_name, ' ' );
                ?>
                    <button type="button" class="mg-quick-pill mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                        <?php echo esc_html( $clean_name ); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Two Featured Scrollboxes Side-by-Side -->
            <div class="mg-featured-scroll-row">
                
                <!-- Left Box: Featured Ghana Stations -->
                <div class="mg-scroll-box-wrap">
                    <div class="mg-scroll-box-title">
                        FEATURED: GHANA STATIONS
                    </div>
                    <div class="mg-scroll-viewport">
                        <ul class="mg-scroll-list">
                            <?php foreach ( $ghana_stations as $st ) : ?>
                                <li>
                                    <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                        <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Box: Featured Diaspora Stations -->
                <div class="mg-scroll-box-wrap">
                    <div class="mg-scroll-box-title">
                        FEATURED: DIASPORA STATIONS
                    </div>
                    <div class="mg-scroll-viewport">
                        <ul class="mg-scroll-list">
                            <?php foreach ( $diaspora_stations as $st ) : ?>
                                <li>
                                    <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                        <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Tabs: Stations Inside Ghana / Outside Ghana -->
            <div class="mg-scope-tabs">
                <button type="button" class="mg-tab-btn active" data-tab="inside">
                    STATIONS INSIDE GHANA
                </button>
                <button type="button" class="mg-tab-btn" data-tab="outside">
                    STATIONS OUTSIDE GHANA
                </button>
            </div>

            <!-- Tab Content: Inside Ghana 3-Column Directory -->
            <div class="mg-tab-content-panel active" id="mg-panel-inside">
                <div class="mg-directory-table">
                    <?php 
                    $inside_array = array_values( $ghana_stations );
                    $total_inside = count( $inside_array );
                    $cols = 3;
                    $chunk_size = ceil( max( 1, $total_inside ) / $cols );
                    $chunks = array_chunk( $inside_array, $chunk_size );

                    for ( $i = 0; $i < $cols; $i++ ) : 
                        $col_items = isset( $chunks[$i] ) ? $chunks[$i] : array();
                    ?>
                        <div class="mg-table-col">
                            <?php foreach ( $col_items as $st ) : 
                                $is_init_active = ( $first_station && $first_station['id'] === $st['id'] );
                            ?>
                                <div class="mg-directory-row <?php echo $is_init_active ? 'is-playing' : ''; ?>" data-id="<?php echo esc_attr( $st['id'] ); ?>">
                                    <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                        <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Tab Content: Outside Ghana 3-Column Directory -->
            <div class="mg-tab-content-panel" id="mg-panel-outside" style="display:none;">
                <div class="mg-directory-table">
                    <?php 
                    $outside_array = array_values( $diaspora_stations );
                    $total_outside = count( $outside_array );
                    $chunks_out = array_chunk( $outside_array, ceil( max( 1, $total_outside ) / 3 ) );

                    for ( $i = 0; $i < 3; $i++ ) : 
                        $col_items = isset( $chunks_out[$i] ) ? $chunks_out[$i] : array();
                    ?>
                        <div class="mg-table-col">
                            <?php foreach ( $col_items as $st ) : ?>
                                <div class="mg-directory-row" data-id="<?php echo esc_attr( $st['id'] ); ?>">
                                    <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                        <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Empty Search Notice -->
            <div id="mg-search-empty" class="mg-empty-msg" style="display:none;">
                No radio station found matching your search.
            </div>

        </main>

    </div>

</div>
