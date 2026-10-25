<?php
/**
 * Savika Live TV Portal — 2cTV Edition
 * Interactive Live Television Player & 1-Click Channel Switcher Matrix
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$channels_json_file = SNRD_PLUGIN_DIR . 'includes/data/tv_channels.json';
$channels = array();
if ( file_exists( $channels_json_file ) ) {
    $raw_json = file_get_contents( $channels_json_file );
    $decoded  = json_decode( $raw_json, true );
    if ( is_array( $decoded ) && ! empty( $decoded ) ) {
        $channels = $decoded;
    }
}

// Default first channel: UTV Ghana
$first_channel = ! empty( $channels ) ? $channels[0] : array(
    'id'          => 'utv',
    'name'        => 'UTV Ghana',
    'dial'        => 'CH 01',
    'network'     => 'Despite Media',
    'category'    => 'Entertainment',
    'type'        => 'embed',
    'streamUrl'   => 'https://www.youtube-nocookie.com/embed/5ZROafBOnjo?autoplay=1&enablejsapi=1',
    'logo'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/United_Television_Ghana_logo.jpg/320px-United_Television_Ghana_logo.jpg',
    'description' => 'United Television (UTV) is Ghana\'s premier Akan entertainment, movie, drama, and news station broadcasting 24/7 from Ship House, Accra.'
);

$channels_total = count( $channels );
$radio_url = home_url( '/radio/' );
$tv_url    = home_url( '/tv/' );
?>

<div class="snrd-tv-wrapper" id="snrd-tv-app">
    
    <!-- Top Navigation Switcher: Radio vs Live TV -->
    <div class="snrd-media-nav-bar">
        <div class="snrd-nav-pills">
            <a href="<?php echo esc_url( $radio_url ); ?>" class="snrd-nav-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                Radio Directory (1,050+ Stations)
            </a>
            <a href="<?php echo esc_url( $tv_url ); ?>" class="snrd-nav-pill active">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"/></svg>
                Live TV (<?php echo esc_html( $channels_total ); ?> Channels)
            </a>
        </div>
        <div class="snrd-live-status-pill">
            <span class="snrd-tv-beacon"></span>
            <span>Broadcasting <?php echo esc_html( $channels_total ); ?> Verified Channels Free-to-Air</span>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- 1. CINEMA THEATRE SCREEN (TOP DECK)            -->
    <!-- ============================================== -->
    <div class="snrd-theatre-deck" id="snrd-theatre-screen">
        
        <!-- Theatre Header Bar -->
        <div class="snrd-theatre-header">
            <div class="snrd-theatre-channel-info">
                <div class="snrd-theatre-logo-wrap">
                    <img id="snrd-active-tv-logo" src="<?php echo esc_url( $first_channel['logo'] ); ?>" alt="<?php echo esc_attr( $first_channel['name'] ); ?>" />
                </div>
                <div>
                    <div class="snrd-theatre-channel-title-row">
                        <span class="snrd-tv-dial-badge" id="snrd-active-tv-dial"><?php echo esc_html( $first_channel['dial'] ); ?></span>
                        <h1 class="snrd-theatre-channel-title" id="snrd-active-tv-title"><?php echo esc_html( $first_channel['name'] ); ?></h1>
                        <span class="snrd-tv-live-badge">
                            <span class="snrd-tv-dot"></span> LIVE
                        </span>
                    </div>
                    <div class="snrd-theatre-meta-sub">
                        <span id="snrd-active-tv-network"><?php echo esc_html( $first_channel['network'] ); ?></span>
                        <span class="snrd-dot-sep">•</span>
                        <span id="snrd-active-tv-category"><?php echo esc_html( $first_channel['category'] ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Theatre Action Controls -->
            <div class="snrd-theatre-controls">
                <button type="button" class="snrd-tv-btn" id="snrd-tv-theatre-toggle" title="Toggle Theatre Mode">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    <span>Theatre</span>
                </button>
                <button type="button" class="snrd-tv-btn" id="snrd-tv-fullscreen-btn" title="Toggle Fullscreen">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                    <span>Fullscreen</span>
                </button>
            </div>
        </div>

        <!-- Responsive 16:9 Video Player Screen -->
        <div class="snrd-video-viewport" id="snrd-video-viewport">
            
            <!-- Native HTML5 Video Element (For HLS .m3u8 Streams) -->
            <video 
                id="snrd-tv-video-element" 
                class="snrd-tv-display" 
                controls 
                autoplay 
                playsinline 
                preload="metadata"
                style="<?php echo ( 'hls' === $first_channel['type'] ) ? '' : 'display:none;'; ?>"
            >
                <source src="<?php echo ( 'hls' === $first_channel['type'] ) ? esc_url( $first_channel['streamUrl'] ) : ''; ?>" type="application/x-mpegURL" />
                Your browser does not support HLS video playback.
            </video>

            <!-- Responsive Embed Player Frame (For YouTube / Cloud Web Feeds) -->
            <iframe 
                id="snrd-tv-iframe-element" 
                class="snrd-tv-display" 
                src="<?php echo ( 'embed' === $first_channel['type'] ) ? esc_url( $first_channel['streamUrl'] ) : ''; ?>" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                allowfullscreen
                style="<?php echo ( 'embed' === $first_channel['type'] ) ? '' : 'display:none;'; ?>"
            ></iframe>

            <!-- TV Tuning Overlay / Spinner -->
            <div class="snrd-tv-loader-overlay" id="snrd-tv-loader" style="display:none;">
                <div class="snrd-tv-spinner"></div>
                <span>Tuning Channel Signal...</span>
            </div>

            <!-- TV Stream Error / Signal Offline Notice Overlay -->
            <div class="snrd-tv-error-overlay" id="snrd-tv-error" style="display:none;">
                <div class="snrd-tv-error-badge">Signal Notice</div>
                <h3 class="snrd-tv-error-title">Transmission Temporarily Offline</h3>
                <p class="snrd-tv-error-desc">This broadcast stream is temporarily offline or unreachable at origin. Please select another channel from the guide below.</p>
                <button type="button" class="snrd-tv-error-retry-btn" id="snrd-tv-retry-btn">Retry Channel</button>
            </div>

        </div>

        <!-- Video Bottom Info Bar -->
        <div class="snrd-theatre-footer">
            <p class="snrd-theatre-desc" id="snrd-active-tv-desc">
                <?php echo esc_html( $first_channel['description'] ); ?>
            </p>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- 2. CHANNEL MATRIX SWITCHER (2cTV APP STYLE)    -->
    <!-- ============================================== -->
    <div class="snrd-channels-section">
        
        <div class="snrd-section-header-bar">
            <div>
                <h2 class="snrd-channels-heading">Channel Guide (<?php echo esc_html( $channels_total ); ?> Channels – 1-Click Switcher)</h2>
                <p class="snrd-channels-subtext">Click any television channel below to tune in instantly without reloading.</p>
            </div>

            <!-- Instant TV Channel Search Bar -->
            <div class="snrd-tv-search-container">
                <div class="snrd-tv-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="snrd-tv-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="snrd-tv-search-input" class="snrd-tv-search-input" placeholder="Search <?php echo esc_attr( $channels_total ); ?> TV stations by name, dial, network, or category..." autocomplete="off" />
                    <button type="button" id="snrd-tv-search-clear" class="snrd-tv-search-clear" style="display:none;" title="Clear search">&times;</button>
                </div>
                <div class="snrd-tv-counter-badge" id="snrd-tv-counter">
                    Showing <strong id="snrd-visible-count">48</strong> of <?php echo esc_html( $channels_total ); ?> Channels
                </div>
            </div>

            <!-- Category Filter Chips -->
            <div class="snrd-tv-category-chips">
                <button type="button" class="snrd-tv-chip active" data-filter="all">All Channels (<?php echo esc_html( $channels_total ); ?>)</button>
                <button type="button" class="snrd-tv-chip" data-filter="News">News</button>
                <button type="button" class="snrd-tv-chip" data-filter="Entertainment">Entertainment</button>
                <button type="button" class="snrd-tv-chip" data-filter="Sports">Sports</button>
                <button type="button" class="snrd-tv-chip" data-filter="Faith">Faith</button>
                <button type="button" class="snrd-tv-chip" data-filter="Music">Music</button>
                <button type="button" class="snrd-tv-chip" data-filter="Movies">Movies</button>
                <button type="button" class="snrd-tv-chip" data-filter="Kids">Kids</button>
            </div>
        </div>

        <!-- Channels Grid Matrix -->
        <div class="snrd-channel-matrix" id="snrd-channel-grid">
            <?php foreach ( $channels as $ch ) : 
                $is_current = ( $first_channel['id'] === $ch['id'] );
            ?>
                <div 
                    class="snrd-channel-card <?php echo $is_current ? 'is-active is-playing' : ''; ?>"
                    data-id="<?php echo esc_attr( $ch['id'] ); ?>"
                    data-name="<?php echo esc_attr( $ch['name'] ); ?>"
                    data-dial="<?php echo esc_attr( $ch['dial'] ); ?>"
                    data-network="<?php echo esc_attr( $ch['network'] ); ?>"
                    data-category="<?php echo esc_attr( $ch['category'] ); ?>"
                    data-type="<?php echo esc_attr( $ch['type'] ); ?>"
                    data-stream="<?php echo esc_url( $ch['streamUrl'] ); ?>"
                    data-logo="<?php echo esc_url( $ch['logo'] ); ?>"
                    data-desc="<?php echo esc_attr( $ch['description'] ); ?>"
                >
                    <!-- Card Top Header -->
                    <div class="snrd-channel-card-top">
                        <span class="snrd-card-dial"><?php echo esc_html( $ch['dial'] ); ?></span>
                        <span class="snrd-card-playing-indicator">
                            <span class="snrd-card-dot"></span>
                            <span class="snrd-card-status-text"><?php echo $is_current ? 'PLAYING NOW' : 'LIVE'; ?></span>
                        </span>
                    </div>

                    <!-- Channel Logo Frame -->
                    <div class="snrd-channel-card-logo-wrap">
                        <div class="snrd-channel-card-logo">
                            <img src="<?php echo esc_url( $ch['logo'] ); ?>" alt="<?php echo esc_attr( $ch['name'] ); ?>" loading="lazy" decoding="async" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                            <div class="snrd-channel-logo-placeholder" style="display:none;">
                                <span><?php echo esc_html( substr( $ch['name'], 0, 3 ) ); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Channel Name & Category -->
                    <div class="snrd-channel-card-body">
                        <h3 class="snrd-channel-card-title"><?php echo esc_html( $ch['name'] ); ?></h3>
                        <p class="snrd-channel-card-network"><?php echo esc_html( $ch['network'] ); ?></p>
                        <div class="snrd-channel-card-pill-wrap">
                            <span class="snrd-channel-category-tag"><?php echo esc_html( $ch['category'] ); ?></span>
                        </div>
                    </div>

                    <!-- 1-Click Tune Button -->
                    <div class="snrd-channel-card-action">
                        <button type="button" class="snrd-btn-tune">
                            <span class="snrd-tune-icon">&#9658;</span>
                            <span class="snrd-tune-text"><?php echo $is_current ? 'Now Playing' : 'Watch Live'; ?></span>
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Progressive Load More Button for 530+ Channels -->
        <div class="snrd-tv-load-more-wrap" id="snrd-tv-load-more-wrap">
            <button type="button" class="snrd-btn-load-more" id="snrd-tv-load-more-btn">
                <span>Load More Channels</span>
                <span class="snrd-btn-load-badge" id="snrd-load-remaining">(484 Remaining)</span>
            </button>
        </div>

    </div>

</div>

