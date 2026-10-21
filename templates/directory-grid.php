<?php
/**
 * Modern Ghana Radio Directory Layout + Station Profiles Grid
 * Faithful editorial design matching radio.modernghana.com + modern profile card view
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
    'order'          => 'ASC',
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
        $desc       = get_the_excerpt();
        $thumb_url  = get_the_post_thumbnail_url( $pid, 'medium' );
        $genres     = get_the_terms( $pid, SNRD_TAXONOMY_GENRE );
        $genre_name = ( ! empty( $genres ) && ! is_wp_error( $genres ) ) ? $genres[0]->name : 'General';

        if ( empty( $location ) ) {
            $location = 'Ghana';
        }
        if ( empty( $frequency ) ) {
            $frequency = 'Online HD';
        }
        if ( empty( $phone ) ) {
            $phone = '+233 (0) 24 000 0000';
        }
        if ( empty( $website ) ) {
            $website = 'savikanews.com/radio';
        }
        if ( empty( $desc ) ) {
            $desc = wp_strip_all_tags( get_the_content() );
            $desc = wp_trim_words( $desc, 14, '...' );
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
            'desc'      => $desc,
            'thumb'     => $thumb_url,
            'genre'     => $genre_name,
            'featured'  => ( '1' === $is_feat ),
            'top'       => ( '1' === $is_top ),
            'permalink' => get_permalink( $pid ),
        );

        $stations_data[] = $station_item;
    }
    wp_reset_postdata();
}

// Specifically prioritize Floodlight Radio as the default playing station
foreach ( $stations_data as $idx => $st ) {
    if ( false !== stripos( $st['title'], 'Floodlight' ) ) {
        $first_station = $st;
        unset( $stations_data[$idx] );
        array_unshift( $stations_data, $st );
        break;
    }
}

if ( null === $first_station && ! empty( $stations_data ) ) {
    $first_station = $stations_data[0];
}

// Safe default to Modern Ghana's Floodlight Radio live stream
if ( null === $first_station ) {
    $first_station = array(
        'id'        => 0,
        'title'     => 'Floodlight Radio',
        'stream'    => 'https://stream.zeno.fm/yecemx18mzzuv',
        'location'  => 'Kumasi - Ghana',
        'frequency' => 'Online HD',
        'phone'     => '0248850040',
        'website'   => 'https://floodlightmediagh.com',
        'scope'     => 'ghana',
        'desc'      => 'Floodlight Radio broadcasts live from Kumasi, delivering the best in talk shows, sports updates, and contemporary Ghanaian music.',
        'thumb'     => '',
        'genre'     => 'Talk',
        'featured'  => true,
        'top'       => true,
        'permalink' => '#',
    );
}

// Split stations by scope
$ghana_stations    = array_filter( $stations_data, function( $st ) { return 'diaspora' !== $st['scope']; } );
$diaspora_stations = array_filter( $stations_data, function( $st ) { return 'diaspora' === $st['scope']; } );

// Fallback curated quick-access names for row 1 and row 2
$quick_pills_row1 = array(
    'PEACE', 'ADOM', 'NHYIRA', 'OKAY', 'OMAN', 'KESSBEN', 
    'ANGEL', 'HOT', 'FOX', 'OTEC', 'HELLO', 'NEAT', 'CITI', 'JOY'
);

$quick_pills_row2 = array(
    'ASEMPA', 'OYEREPA', 'WONTUMI', 'KASAPA', 'PURE', 'HITZ', 
    'ASHH', 'STARR', 'SILVER', 'LUV', '3 FM', 'RADIO GOLD', 'FLOODLIGHT'
);
?>

<div class="mg-radio-container" id="mg-radio-app">
    
    <div class="mg-layout-row">

        <!-- ============================================== -->
        <!-- LEFT SIDEBAR (.content-left)                   -->
        <!-- ============================================== -->
        <aside class="content-left-wrapper">
            
            <!-- Featured Stations Box -->
            <div class="content-left">
                <h3>Featured Stations</h3>
                <ul>
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

            <!-- Apply to Broadcast Banner -->
            <div class="content-left-cta">
                <a href="<?php echo esc_url( home_url( '/radio/apply/' ) ); ?>" class="mg-apply-sidebar-btn">
                    + SUBMIT YOUR STATION
                </a>
            </div>

            <!-- Top Stations Box -->
            <div class="content-left">
                <h3>Top Stations</h3>
                <ul>
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

        </aside>

        <!-- ============================================== -->
        <!-- CENTER MAIN CONSOLE (.content-middle)          -->
        <!-- ============================================== -->
        <main class="content-middle">
            
            <!-- Master Console Container -->
            <div class="fm-master-console">
                
                <!-- FM1 Row: Country Flag + Station Title + 5 Star Rating -->
                <div class="row fm1">
                    <div class="col-xs-12 col-sm-8 col-md-8 fm-name">
                        <span class="mg-flag-badge" title="Ghana">
                            <svg width="26" height="18" viewBox="0 0 600 400" aria-label="Ghana Flag" style="vertical-align: middle; border: 1px solid #444; border-radius: 2px;">
                                <rect width="600" height="133.33" fill="#D80027"/>
                                <rect y="133.33" width="600" height="133.33" fill="#FFDA44"/>
                                <rect y="266.66" width="600" height="133.33" fill="#496E2D"/>
                                <polygon points="300,145 311,178 345,178 318,198 328,231 300,211 272,231 282,198 255,178 289,178" fill="#000000"/>
                            </svg>
                        </span>
                        <h1 id="mg-active-title">
                            <?php echo $first_station ? esc_html( strtoupper( $first_station['title'] ) ) : 'FLOODLIGHT RADIO'; ?>
                        </h1>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-4 rates">
                        <ul class="rating-star-left">
                            <li class="active"><i class="mg-star">&#9733;</i></li>
                            <li class="active"><i class="mg-star">&#9733;</i></li>
                            <li class="active"><i class="mg-star">&#9733;</i></li>
                            <li class="active"><i class="mg-star">&#9733;</i></li>
                            <li class="active"><i class="mg-star">&#9733;</i></li>
                        </ul>
                    </div>
                </div>

                <!-- FM2 Row: Location, Phone, Website Strip -->
                <div class="row fm2">
                    <div class="fm-info clearfix">
                        <div class="col-xs-12 col-sm-4 col-md-4 location">
                            <span id="mg-active-location">
                                <?php echo $first_station ? esc_html( $first_station['location'] ) : 'Kumasi - Ghana'; ?>
                            </span>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-4 phone">
                            <span id="mg-active-phone">
                                <?php echo $first_station ? esc_html( $first_station['phone'] ) : '0248850040'; ?>
                            </span>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-4 website">
                            <span id="mg-active-website">
                                <?php echo $first_station ? esc_html( $first_station['website'] ) : 'savikanews.com/radio'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- FM2 Row: Live Audio Deck & Playback Controls -->
                <div class="row fm2">
                    <div class="fm-play-push clearfix">
                        <div class="col-xs-12 col-sm-6 col-md-6 text-left fm-controls-side">
                            <a id="mg-status-indicator" href="javascript:void(0);">
                                <span class="mg-live-dot"></span>
                                <span id="mg-status-label">Live Broadcast</span>
                            </a>
                            <a id="mg-start-btn" class="mg-ctrl-btn" href="javascript:void(0);">
                                <span class="mg-icon-symbol">&#9658;</span>
                                <span>Start Radio</span>
                            </a>
                            <a id="mg-pause-btn" class="mg-ctrl-btn" href="javascript:void(0);" style="display:none;">
                                <span class="mg-icon-symbol">&#10074;&#10074;</span>
                                <span>Pause</span>
                            </a>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6 text-right fm-audio-side">
                            <audio id="mg-audio-element" controls preload="none" playsinline style="width: 100%; height: 34px;">
                                <source src="<?php echo $first_station ? esc_url( $first_station['stream'] ) : ''; ?>" id="mg-audio-source" />
                                Your browser does not support HTML5 audio streaming.
                            </audio>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Search Bar (#searchbar / #findradio) -->
            <div class="search-rating clearfix">
                <div id="searchbar">
                    <input 
                        id="findradio" 
                        type="search" 
                        placeholder="Search a station from here..." 
                        autocomplete="off"
                    />
                    <span class="mg-search-icon">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#777" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                </div>
            </div>

            <!-- Quick Station Buttons Row 1 (.fm-country) -->
            <div id="fm-settings-row1" class="fm-country clearfix">
                <ul>
                    <?php 
                    $shown_names = array();
                    foreach ( $quick_pills_row1 as $target_name ) :
                        $matched_st = null;
                        foreach ( $stations_data as $s ) {
                            if ( false !== stripos( $s['title'], $target_name ) ) {
                                $matched_st = $s;
                                break;
                            }
                        }
                        if ( ! $matched_st && ! empty( $stations_data ) ) {
                            foreach ( $stations_data as $s ) {
                                if ( ! in_array( $s['id'], $shown_names, true ) ) {
                                    $matched_st = $s;
                                    break;
                                }
                            }
                        }
                        if ( $matched_st ) :
                            $shown_names[] = $matched_st['id'];
                            $pill_display = ucfirst( strtolower( $target_name ) );
                    ?>
                        <li>
                            <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $matched_st['id'] ); ?>" data-title="<?php echo esc_attr( $matched_st['title'] ); ?>" data-stream="<?php echo esc_url( $matched_st['stream'] ); ?>" data-location="<?php echo esc_attr( $matched_st['location'] ); ?>" data-phone="<?php echo esc_attr( $matched_st['phone'] ); ?>" data-website="<?php echo esc_attr( $matched_st['website'] ); ?>">
                                <?php echo esc_html( $pill_display ); ?>
                            </a>
                        </li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- Quick Station Buttons Row 2 (.fm-settings) -->
            <div id="fm-settings-row2" class="fm-settings clearfix">
                <ul>
                    <?php 
                    foreach ( $quick_pills_row2 as $target_name ) :
                        $matched_st = null;
                        foreach ( $stations_data as $s ) {
                            if ( false !== stripos( $s['title'], $target_name ) ) {
                                $matched_st = $s;
                                break;
                            }
                        }
                        if ( ! $matched_st && ! empty( $stations_data ) ) {
                            foreach ( $stations_data as $s ) {
                                if ( ! in_array( $s['id'], $shown_names, true ) ) {
                                    $matched_st = $s;
                                    break;
                                }
                            }
                        }
                        if ( $matched_st ) :
                            $shown_names[] = $matched_st['id'];
                            $pill_display = ucfirst( strtolower( $target_name ) );
                    ?>
                        <li>
                            <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $matched_st['id'] ); ?>" data-title="<?php echo esc_attr( $matched_st['title'] ); ?>" data-stream="<?php echo esc_url( $matched_st['stream'] ); ?>" data-location="<?php echo esc_attr( $matched_st['location'] ); ?>" data-phone="<?php echo esc_attr( $matched_st['phone'] ); ?>" data-website="<?php echo esc_attr( $matched_st['website'] ); ?>">
                                <?php echo esc_html( $pill_display ); ?>
                            </a>
                        </li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- View Switcher & Filter Bar -->
            <div class="mg-view-toolbar">
                <div class="mg-view-tabs">
                    <button type="button" class="mg-view-btn active" data-view="grid">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 10h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 16h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4z"/></svg>
                        Station Profiles
                    </button>
                    <button type="button" class="mg-view-btn" data-view="table">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm0 7h18v2H3zm0 7h18v2H3z"/></svg>
                        Directory Table
                    </button>
                </div>
                <div class="mg-scope-chips">
                    <button type="button" class="mg-scope-chip active" data-filter="all">All Stations</button>
                    <button type="button" class="mg-scope-chip" data-filter="ghana">Inside Ghana</button>
                    <button type="button" class="mg-scope-chip" data-filter="diaspora">Diaspora</button>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- VIEW 1: MODERN STATION PROFILES GRID (MATCHING SCREENSHOTS) -->
            <!-- ======================================================== -->
            <div id="snrd-view-grid" class="snrd-view-container active">
                <div class="snrd-profiles-grid">
                    <?php foreach ( $stations_data as $st ) : 
                        $is_playing_init = ( $first_station && $first_station['id'] === $st['id'] );
                    ?>
                        <div class="snrd-profile-card-item <?php echo $is_playing_init ? 'selected is-playing' : ''; ?>" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-scope="<?php echo esc_attr( $st['scope'] ); ?>">
                            
                            <!-- Card Header Cover -->
                            <div class="snrd-card-top-cover">
                                <span class="snrd-card-status-pill">
                                    <span class="snrd-mini-beacon"></span> LIVE
                                </span>
                                <span class="snrd-card-freq-pill">
                                    <?php echo esc_html( $st['frequency'] ); ?>
                                </span>
                            </div>

                            <!-- Circular Logo Frame Overlapping Cover -->
                            <div class="snrd-card-avatar-wrap">
                                <div class="snrd-card-avatar">
                                    <?php if ( ! empty( $st['thumb'] ) ) : ?>
                                        <img src="<?php echo esc_url( $st['thumb'] ); ?>" alt="<?php echo esc_attr( $st['title'] ); ?>" />
                                    <?php else : ?>
                                        <div class="snrd-card-avatar-placeholder">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#ffffff"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Station Name & Info -->
                            <div class="snrd-card-content">
                                <h3 class="snrd-card-title" title="<?php echo esc_attr( $st['title'] ); ?>">
                                    <a href="<?php echo esc_url( $st['permalink'] ); ?>">
                                        <?php echo esc_html( $st['title'] ); ?>
                                    </a>
                                </h3>

                                <div class="snrd-card-location">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span><?php echo esc_html( $st['location'] ); ?></span>
                                </div>

                                <div class="snrd-card-tags">
                                    <span class="snrd-tag-genre"><?php echo esc_html( $st['genre'] ); ?></span>
                                    <span class="snrd-tag-scope"><?php echo ( 'diaspora' === $st['scope'] ) ? 'Diaspora' : 'Ghana'; ?></span>
                                </div>

                                <p class="snrd-card-excerpt">
                                    <?php echo esc_html( $st['desc'] ); ?>
                                </p>

                                <!-- Card Action Buttons -->
                                <div class="snrd-card-actions">
                                    <button type="button" class="snrd-btn-listen mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                        &#9658; Listen Live
                                    </button>
                                    <a href="<?php echo esc_url( $st['permalink'] ); ?>" class="snrd-btn-profile">
                                        View Profile
                                    </a>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- VIEW 2: MODERN GHANA CLASSIC DIRECTORY TABLE VIEW        -->
            <!-- ======================================================== -->
            <div id="snrd-view-table" class="snrd-view-container" style="display:none;">
                
                <!-- Dual Featured Scrollboxes Side-by-Side (.radio-feature) -->
                <div id="search" class="radio-feature clearfix">
                    <div class="row feature-grid-row">
                        
                        <!-- Left: Featured Ghana Stations -->
                        <div class="col-xs-12 col-sm-6 col-md-6 inside-ghana">
                            <h3>FEATURED: GHANA STATIONS</h3>
                            <ul id="search1" class="mg-featured-scroll-list">
                                <?php 
                                $idx_gh = 0;
                                foreach ( $ghana_stations as $st ) : 
                                    $row_cls = ( $idx_gh % 2 === 1 ) ? 'bg-dark-gray' : '';
                                    $idx_gh++;
                                ?>
                                    <li class="<?php echo esc_attr( $row_cls ); ?>">
                                        <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                            <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Right: Featured Diaspora Stations -->
                        <div class="col-xs-12 col-sm-6 col-md-6 outside-ghana">
                            <h3>FEATURED: DIASPORA STATIONS</h3>
                            <ul id="search2" class="mg-featured-scroll-list">
                                <?php 
                                $idx_di = 0;
                                foreach ( $diaspora_stations as $st ) : 
                                    $row_cls = ( $idx_di % 2 === 1 ) ? 'bg-dark-gray' : '';
                                    $idx_di++;
                                ?>
                                    <li class="<?php echo esc_attr( $row_cls ); ?>">
                                        <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                            <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- Scope Tabs & 3-Column Directory (.stations-tab) -->
                <div class="stations-tab clearfix">
                    <div class="panel with-nav-tabs panel-primary">
                        
                        <div class="panel-heading">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab1primary" class="mg-tab-link active" data-tab="inside">STATIONS INSIDE GHANA</a>
                                </li>
                                <li>
                                    <a href="#tab2primary" class="mg-tab-link" data-tab="outside">STATIONS OUTSIDE GHANA</a>
                                </li>
                            </ul>
                        </div>

                        <div class="panel-body">
                            
                            <!-- Panel: Stations Inside Ghana (3 Columns) -->
                            <div id="tab1primary" class="mg-tab-panel active">
                                <div class="row stations-scroll">
                                    <?php 
                                    $inside_items = array_values( $ghana_stations );
                                    $total_in = count( $inside_items );
                                    $chunk_size = ceil( max( 1, $total_in ) / 3 );
                                    $cols_data = array_chunk( $inside_items, max( 1, $chunk_size ) );

                                    for ( $c = 0; $c < 3; $c++ ) : 
                                        $col_list = isset( $cols_data[$c] ) ? $cols_data[$c] : array();
                                    ?>
                                        <div class="col-xs-12 col-sm-4 col-md-4 mg-table-col">
                                            <ul class="mg-stations-column-list">
                                                <?php foreach ( $col_list as $st ) : 
                                                    $is_active_init = ( $first_station && $first_station['id'] === $st['id'] );
                                                ?>
                                                    <li class="mg-station-row <?php echo $is_active_init ? 'selected is-playing' : ''; ?>" data-id="<?php echo esc_attr( $st['id'] ); ?>">
                                                        <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                                            <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Panel: Stations Outside Ghana (3 Columns) -->
                            <div id="tab2primary" class="mg-tab-panel" style="display:none;">
                                <div class="row stations-scroll">
                                    <?php 
                                    $outside_items = array_values( $diaspora_stations );
                                    $total_out = count( $outside_items );
                                    $chunk_size_out = ceil( max( 1, $total_out ) / 3 );
                                    $cols_data_out = array_chunk( $outside_items, max( 1, $chunk_size_out ) );

                                    for ( $c = 0; $c < 3; $c++ ) : 
                                        $col_list = isset( $cols_data_out[$c] ) ? $cols_data_out[$c] : array();
                                    ?>
                                        <div class="col-xs-12 col-sm-4 col-md-4 mg-table-col">
                                            <ul class="mg-stations-column-list">
                                                <?php foreach ( $col_list as $st ) : ?>
                                                    <li class="mg-station-row" data-id="<?php echo esc_attr( $st['id'] ); ?>">
                                                        <a href="#" class="mg-station-trigger" data-id="<?php echo esc_attr( $st['id'] ); ?>" data-title="<?php echo esc_attr( $st['title'] ); ?>" data-stream="<?php echo esc_url( $st['stream'] ); ?>" data-location="<?php echo esc_attr( $st['location'] ); ?>" data-phone="<?php echo esc_attr( $st['phone'] ); ?>" data-website="<?php echo esc_attr( $st['website'] ); ?>">
                                                            <?php echo esc_html( strtoupper( $st['title'] ) ); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Empty Search Notice -->
            <div id="mg-search-empty-state" class="mg-empty-alert" style="display:none;">
                No radio stations found matching your search.
            </div>

        </main>

    </div>

</div>
