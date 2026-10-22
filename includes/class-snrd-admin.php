<?php
/**
 * Admin Meta Boxes, Easy Station Manager, and Stream Testing Interface
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNRD_Admin {

    /**
     * Initialize admin hooks
     */
    public static function init() {
        if ( ! is_admin() ) {
            return;
        }

        // Meta boxes
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_' . SNRD_POST_TYPE, array( __CLASS__, 'save_meta_box_data' ) );
        
        // Custom admin columns
        add_filter( 'manage_' . SNRD_POST_TYPE . '_posts_columns', array( __CLASS__, 'set_custom_columns' ) );
        add_action( 'manage_' . SNRD_POST_TYPE . '_posts_custom_column', array( __CLASS__, 'render_custom_columns' ), 10, 2 );

        // Admin assets
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );

        // Add settings submenu for notification email & quick instructions
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_init', array( __CLASS__, 'handle_page_generation_action' ) );

        // Remove unnecessary standard meta boxes on radio stations for a clean, non-distracting UI
        add_action( 'admin_menu', array( __CLASS__, 'remove_unwanted_meta_boxes' ) );
    }

    /**
     * Remove clutter from the station editor
     */
    public static function remove_unwanted_meta_boxes() {
        remove_meta_box( 'postcustom', SNRD_POST_TYPE, 'normal' );
        remove_meta_box( 'commentstatusdiv', SNRD_POST_TYPE, 'normal' );
        remove_meta_box( 'commentsdiv', SNRD_POST_TYPE, 'normal' );
        remove_meta_box( 'slugdiv', SNRD_POST_TYPE, 'normal' );
    }

    /**
     * Register meta boxes
     */
    public static function add_meta_boxes() {
        // Main Station Broadcast Center
        add_meta_box(
            'snrd_station_details',
            __( 'Radio Station Broadcast & Stream Setup', 'savika-news-radio' ),
            array( __CLASS__, 'render_station_meta_box' ),
            SNRD_POST_TYPE,
            'normal',
            'high'
        );

        // Station Logo Quick-Uploader
        add_meta_box(
            'snrd_station_logo_box',
            __( 'Station Logo', 'savika-news-radio' ),
            array( __CLASS__, 'render_logo_meta_box' ),
            SNRD_POST_TYPE,
            'side',
            'high'
        );

        // Applicant Contact Info
        add_meta_box(
            'snrd_station_applicant',
            __( 'Broadcaster Contact Information (Private)', 'savika-news-radio' ),
            array( __CLASS__, 'render_applicant_meta_box' ),
            SNRD_POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Render main meta box
     */
    public static function render_station_meta_box( $post ) {
        wp_nonce_field( 'snrd_save_station_details', 'snrd_meta_box_nonce' );

        $stream_url   = get_post_meta( $post->ID, 'snrd_stream_url', true );
        $location     = get_post_meta( $post->ID, 'snrd_location', true );
        $frequency    = get_post_meta( $post->ID, 'snrd_frequency', true );
        $phone        = get_post_meta( $post->ID, 'snrd_contact_phone', true );
        $scope        = get_post_meta( $post->ID, 'snrd_station_scope', true );
        $is_featured  = get_post_meta( $post->ID, 'snrd_is_featured', true );
        $is_top       = get_post_meta( $post->ID, 'snrd_is_top', true );
        $zeno_url     = get_post_meta( $post->ID, 'snrd_zeno_url', true );
        $website_url  = get_post_meta( $post->ID, 'snrd_website_url', true );

        if ( empty( $scope ) ) {
            $scope = 'ghana';
        }
        ?>
        <div class="snrd-admin-card-wrap">

            <!-- Stream URL & Live Test -->
            <div class="snrd-admin-field-row highlight-box">
                <div class="snrd-admin-field-col full-width">
                    <label for="snrd_stream_url" class="snrd-admin-label">
                        <span class="dashicons dashicons-format-audio" style="color: #c90000; font-size: 20px;"></span>
                        <strong><?php esc_html_e( 'Public Audio Stream URL (MP3 / AAC / HLS .m3u8 / Zeno / StreamGuys / Shoutcast)', 'savika-news-radio' ); ?></strong>
                        <span class="required">*</span>
                    </label>
                    <input type="url" name="snrd_stream_url" id="snrd_stream_url" class="snrd-admin-input large" value="<?php echo esc_url( $stream_url ); ?>" placeholder="https://stream.zeno.fm/xxxxxxxxxx or https://...:8000/stream or https://.../playlist.m3u8" />
                    <p class="description">
                        <?php esc_html_e( 'The direct listener audio stream link. Fully supports direct MP3/AAC streams and HLS (.m3u8) streams.', 'savika-news-radio' ); ?>
                    </p>

                    <!-- Built-in Instant Audio Test Player -->
                    <div class="snrd-admin-player-dock">
                        <div class="snrd-admin-player-header">
                            <span class="dashicons dashicons-controls-volumeon"></span>
                            <strong><?php esc_html_e( 'Instant Stream Verification Player', 'savika-news-radio' ); ?></strong>
                        </div>
                        <div class="snrd-admin-player-controls">
                            <audio id="snrd-admin-audio-preview" controls preload="none">
                                <source src="<?php echo esc_url( $stream_url ); ?>" id="snrd-admin-audio-source" />
                                <?php esc_html_e( 'Your browser does not support HTML5 audio.', 'savika-news-radio' ); ?>
                            </audio>
                            <button type="button" class="button button-primary" id="snrd-reload-stream-btn">
                                <span class="dashicons dashicons-controls-play" style="margin-top: 3px;"></span>
                                <?php esc_html_e( 'Test Stream Now', 'savika-news-radio' ); ?>
                            </button>
                            <span id="snrd-stream-test-status" class="snrd-test-badge"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frequency & Location Row -->
            <div class="snrd-admin-field-row two-cols">
                <div class="snrd-admin-field-col">
                    <label for="snrd_frequency" class="snrd-admin-label">
                        <span class="dashicons dashicons-dashboard"></span>
                        <strong><?php esc_html_e( 'Dial / Frequency / Slogan', 'savika-news-radio' ); ?></strong>
                    </label>
                    <input type="text" name="snrd_frequency" id="snrd_frequency" class="snrd-admin-input" value="<?php echo esc_attr( $frequency ); ?>" placeholder="e.g. 104.3 FM or Online HD" />
                    <p class="description"><?php esc_html_e( 'Shown prominently on cards and the station hero.', 'savika-news-radio' ); ?></p>
                </div>

                <div class="snrd-admin-field-col">
                    <label for="snrd_location" class="snrd-admin-label">
                        <span class="dashicons dashicons-location"></span>
                        <strong><?php esc_html_e( 'Location / Region', 'savika-news-radio' ); ?></strong>
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="snrd_location" id="snrd_location" class="snrd-admin-input" value="<?php echo esc_attr( $location ); ?>" placeholder="e.g. Mile 7, Achimota, Accra - Ghana" />
                    <p class="description"><?php esc_html_e( 'City, region or broadcast territory.', 'savika-news-radio' ); ?></p>
                </div>
            </div>

            <!-- Contact Phone & Scope Row -->
            <div class="snrd-admin-field-row two-cols">
                <div class="snrd-admin-field-col">
                    <label for="snrd_contact_phone" class="snrd-admin-label">
                        <span class="dashicons dashicons-phone"></span>
                        <strong><?php esc_html_e( 'Studio / Contact Phone', 'savika-news-radio' ); ?></strong>
                    </label>
                    <input type="text" name="snrd_contact_phone" id="snrd_contact_phone" class="snrd-admin-input" value="<?php echo esc_attr( $phone ); ?>" placeholder="e.g. 0302782414 or +233..." />
                </div>

                <div class="snrd-admin-field-col">
                    <label for="snrd_station_scope" class="snrd-admin-label">
                        <span class="dashicons dashicons-admin-site-alt3"></span>
                        <strong><?php esc_html_e( 'Broadcast Scope', 'savika-news-radio' ); ?></strong>
                    </label>
                    <select name="snrd_station_scope" id="snrd_station_scope" class="snrd-admin-input">
                        <option value="ghana" <?php selected( $scope, 'ghana' ); ?>><?php esc_html_e( 'Inside Ghana Broadcaster', 'savika-news-radio' ); ?></option>
                        <option value="diaspora" <?php selected( $scope, 'diaspora' ); ?>><?php esc_html_e( 'Diaspora Broadcaster (UK, USA, Europe, Worldwide)', 'savika-news-radio' ); ?></option>
                    </select>
                </div>
            </div>

            <!-- Links Row -->
            <div class="snrd-admin-field-row two-cols">
                <div class="snrd-admin-field-col">
                    <label for="snrd_zeno_url" class="snrd-admin-label">
                        <span class="dashicons dashicons-external"></span>
                        <strong><?php esc_html_e( 'External / Source Profile Link (Optional)', 'savika-news-radio' ); ?></strong>
                    </label>
                    <input type="url" name="snrd_zeno_url" id="snrd_zeno_url" class="snrd-admin-input" value="<?php echo esc_url( $zeno_url ); ?>" placeholder="https://zeno.fm/... or https://radio.modernghana.com/station/..." />
                    <p class="description"><?php esc_html_e( 'Used as a reliable fallback link for listeners.', 'savika-news-radio' ); ?></p>
                </div>

                <div class="snrd-admin-field-col">
                    <label for="snrd_website_url" class="snrd-admin-label">
                        <span class="dashicons dashicons-admin-site"></span>
                        <strong><?php esc_html_e( 'Official Website (Optional)', 'savika-news-radio' ); ?></strong>
                    </label>
                    <input type="url" name="snrd_website_url" id="snrd_website_url" class="snrd-admin-input" value="<?php echo esc_url( $website_url ); ?>" placeholder="https://peacefmonline.com" />
                </div>
            </div>

            <!-- Featured & Top Badges Row -->
            <div class="snrd-admin-field-row two-cols" style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <div class="snrd-admin-field-col">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="snrd_is_featured" value="1" <?php checked( $is_featured, '1' ); ?> />
                        <?php esc_html_e( 'Featured Station (Highlighted in Quick Access and Directory)', 'savika-news-radio' ); ?>
                    </label>
                </div>
                <div class="snrd-admin-field-col">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="snrd_is_top" value="1" <?php checked( $is_top, '1' ); ?> />
                        <?php esc_html_e( 'Top Station (Priority Sorting)', 'savika-news-radio' ); ?>
                    </label>
                </div>
            </div>

        </div>
        <?php
    }

    /**
     * Dedicated Station Logo Meta Box with 1-Click Media Selector
     */
    public static function render_logo_meta_box( $post ) {
        $thumbnail_id = get_post_thumbnail_id( $post->ID );
        $image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
        $custom_logo  = get_post_meta( $post->ID, 'snrd_logo_url', true );
        $display_url  = $image_url ? $image_url : $custom_logo;
        ?>
        <div class="snrd-logo-uploader-widget" id="snrd-logo-uploader-widget">
            <input type="hidden" name="_thumbnail_id" id="_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ? $thumbnail_id : '-1' ); ?>" />

            <div class="snrd-logo-preview-box" id="snrd-logo-preview" style="<?php echo $display_url ? 'display:block;' : 'display:none;'; ?>">
                <img src="<?php echo esc_url( $display_url ); ?>" id="snrd-logo-img" alt="Logo preview" style="max-width: 100%; border-radius: 8px; border: 1px solid #dcdcde; display: block; margin: 0 auto 10px;" />
            </div>

            <div class="snrd-logo-empty-box" id="snrd-logo-empty" style="<?php echo $display_url ? 'display:none;' : 'display:block;'; ?> text-align: center; padding: 25px 10px; background: #f0f0f1; border: 2px dashed #c3c4c7; border-radius: 6px; margin-bottom: 10px;">
                <span class="dashicons dashicons-format-image" style="font-size: 40px; width: 40px; height: 40px; color: #8c8f94;"></span>
                <p style="margin: 8px 0 0; font-size: 13px; color: #646970;"><?php esc_html_e( 'No logo selected', 'savika-news-radio' ); ?></p>
            </div>

            <div class="snrd-logo-buttons" style="display: flex; gap: 8px; margin-bottom: 12px;">
                <button type="button" class="button button-primary" id="snrd-select-logo-btn" style="flex: 1;">
                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                    <?php esc_html_e( 'Upload / Select Logo', 'savika-news-radio' ); ?>
                </button>
                <button type="button" class="button button-link-delete" id="snrd-remove-logo-btn" style="<?php echo $image_url ? 'display:inline-block;' : 'display:none;'; ?>">
                    <?php esc_html_e( 'Remove', 'savika-news-radio' ); ?>
                </button>
            </div>

            <div class="snrd-logo-url-fallback" style="border-top: 1px solid #eee; padding-top: 10px;">
                <label for="snrd_logo_url" style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px;">
                    <?php esc_html_e( 'Or Direct Logo Image URL:', 'savika-news-radio' ); ?>
                </label>
                <input type="url" name="snrd_logo_url" id="snrd_logo_url" class="widefat" value="<?php echo esc_url( $custom_logo ); ?>" placeholder="https://.../logo.png" style="font-size: 12px;" />
                <p class="description" style="margin-top: 4px; font-size: 11px;">
                    <?php esc_html_e( 'Paste direct link to station logo (PNG/JPG). Used automatically if no media library image is uploaded.', 'savika-news-radio' ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render applicant contact info meta box
     */
    public static function render_applicant_meta_box( $post ) {
        $contact_email = get_post_meta( $post->ID, 'snrd_contact_email', true );
        $applied_date  = get_post_meta( $post->ID, 'snrd_applied_date', true );
        $authorized    = get_post_meta( $post->ID, 'snrd_applicant_authorized', true );
        ?>
        <div class="snrd-applicant-info">
            <p>
                <label for="snrd_contact_email"><strong><?php esc_html_e( 'Applicant Email:', 'savika-news-radio' ); ?></strong></label><br>
                <input type="email" name="snrd_contact_email" id="snrd_contact_email" class="widefat" value="<?php echo esc_attr( $contact_email ); ?>" />
                <span class="description" style="font-size: 11px;"><?php esc_html_e( 'Confidential. Never shown on the public directory.', 'savika-news-radio' ); ?></span>
            </p>

            <p style="font-size: 12px;">
                <strong><?php esc_html_e( 'Broadcast Consent:', 'savika-news-radio' ); ?></strong><br>
                <?php echo ( '1' === $authorized ) ? '<span style="color: #008a20; font-weight: bold;">[Confirmed] ' . esc_html__( 'Authorized by submitter', 'savika-news-radio' ) . '</span>' : esc_html__( 'Admin created listing', 'savika-news-radio' ); ?>
            </p>

            <?php if ( $applied_date ) : ?>
                <p style="font-size: 12px;">
                    <strong><?php esc_html_e( 'Submitted On:', 'savika-news-radio' ); ?></strong><br>
                    <code><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $applied_date ) ) ); ?></code>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public static function save_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['snrd_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['snrd_meta_box_nonce'], 'snrd_save_station_details' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['snrd_stream_url'] ) ) {
            update_post_meta( $post_id, 'snrd_stream_url', esc_url_raw( trim( $_POST['snrd_stream_url'] ) ) );
        }

        if ( isset( $_POST['snrd_frequency'] ) ) {
            update_post_meta( $post_id, 'snrd_frequency', sanitize_text_field( trim( $_POST['snrd_frequency'] ) ) );
        }

        if ( isset( $_POST['snrd_location'] ) ) {
            update_post_meta( $post_id, 'snrd_location', sanitize_text_field( trim( $_POST['snrd_location'] ) ) );
        }

        if ( isset( $_POST['snrd_contact_phone'] ) ) {
            update_post_meta( $post_id, 'snrd_contact_phone', sanitize_text_field( trim( $_POST['snrd_contact_phone'] ) ) );
        }

        if ( isset( $_POST['snrd_station_scope'] ) ) {
            $scope = in_array( $_POST['snrd_station_scope'], array( 'ghana', 'diaspora' ), true ) ? $_POST['snrd_station_scope'] : 'ghana';
            update_post_meta( $post_id, 'snrd_station_scope', $scope );
        }

        $is_featured = isset( $_POST['snrd_is_featured'] ) ? '1' : '0';
        update_post_meta( $post_id, 'snrd_is_featured', $is_featured );

        $is_top = isset( $_POST['snrd_is_top'] ) ? '1' : '0';
        update_post_meta( $post_id, 'snrd_is_top', $is_top );

        if ( isset( $_POST['snrd_logo_url'] ) ) {
            update_post_meta( $post_id, 'snrd_logo_url', esc_url_raw( trim( $_POST['snrd_logo_url'] ) ) );
        }

        if ( isset( $_POST['snrd_zeno_url'] ) ) {
            update_post_meta( $post_id, 'snrd_zeno_url', esc_url_raw( trim( $_POST['snrd_zeno_url'] ) ) );
        }

        if ( isset( $_POST['snrd_website_url'] ) ) {
            update_post_meta( $post_id, 'snrd_website_url', esc_url_raw( trim( $_POST['snrd_website_url'] ) ) );
        }

        if ( isset( $_POST['snrd_contact_email'] ) ) {
            update_post_meta( $post_id, 'snrd_contact_email', sanitize_email( trim( $_POST['snrd_contact_email'] ) ) );
        }
    }

    /**
     * Custom columns on Radio Stations list
     */
    public static function set_custom_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $title ) {
            if ( 'title' === $key ) {
                $new_columns['snrd_logo'] = __( 'Logo', 'savika-news-radio' );
            }
            $new_columns[ $key ] = $title;
            if ( 'title' === $key ) {
                $new_columns['snrd_frequency_col'] = __( 'Dial / Frequency', 'savika-news-radio' );
                $new_columns['snrd_location_col']  = __( 'Location', 'savika-news-radio' );
                $new_columns['snrd_stream_col']    = __( 'Live Stream URL', 'savika-news-radio' );
                $new_columns['snrd_email_col']     = __( 'Contact Email', 'savika-news-radio' );
            }
        }
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public static function render_custom_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'snrd_logo':
                if ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'style' => 'border-radius: 6px; object-fit: cover;' ) );
                } else {
                    $custom_logo = get_post_meta( $post_id, 'snrd_logo_url', true );
                    if ( ! empty( $custom_logo ) ) {
                        echo '<img src="' . esc_url( $custom_logo ) . '" style="width:48px;height:48px;border-radius:6px;object-fit:cover;" alt="Logo" />';
                    } else {
                        echo '<span style="display:inline-block;width:40px;height:40px;background:#e2e8f0;border-radius:6px;text-align:center;line-height:40px;color:#718096;"><span class="dashicons dashicons-format-audio" style="line-height:40px;"></span></span>';
                    }
                }
                break;

            case 'snrd_frequency_col':
                $freq = get_post_meta( $post_id, 'snrd_frequency', true );
                echo $freq ? '<strong>' . esc_html( $freq ) . '</strong>' : '<span style="color:#a0aec0;">—</span>';
                break;

            case 'snrd_location_col':
                $loc = get_post_meta( $post_id, 'snrd_location', true );
                echo $loc ? esc_html( $loc ) : '<span style="color:#a0aec0;">—</span>';
                break;

            case 'snrd_stream_col':
                $url = get_post_meta( $post_id, 'snrd_stream_url', true );
                if ( $url ) {
                    echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" style="font-size:12px;font-family:monospace;word-break:break-all;">' . esc_html( wp_trim_words( $url, 4, '...' ) ) . '</a>';
                } else {
                    echo '<span style="color:#e53e3e;font-weight:600;">' . esc_html__( 'No Stream URL', 'savika-news-radio' ) . '</span>';
                }
                break;

            case 'snrd_email_col':
                $email = get_post_meta( $post_id, 'snrd_contact_email', true );
                echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '<span style="color:#a0aec0;">—</span>';
                break;
        }
    }

    /**
     * Enqueue Admin scripts and styles
     */
    public static function enqueue_admin_assets( $hook ) {
        global $post_type;
        if ( SNRD_POST_TYPE !== $post_type ) {
            return;
        }

        // WordPress Media Uploader scripts for 1-click logo selection
        wp_enqueue_media();

        wp_enqueue_style( 'snrd-admin-css', SNRD_PLUGIN_URL . 'assets/css/snrd-admin.css', array(), SNRD_VERSION );
        wp_enqueue_script( 'snrd-admin-js', SNRD_PLUGIN_URL . 'assets/js/snrd-admin.js', array( 'jquery' ), SNRD_VERSION, true );
    }

    /**
     * Add settings submenu under Radio Stations
     */
    public static function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=' . SNRD_POST_TYPE,
            __( 'Radio Settings & Setup Guide', 'savika-news-radio' ),
            __( 'Settings & Guide', 'savika-news-radio' ),
            'manage_options',
            'snrd-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    /**
     * Register settings fields
     */
    public static function register_settings() {
        register_setting( 'snrd_settings_group', 'snrd_admin_notify_email_1', array(
            'sanitize_callback' => 'sanitize_email',
            'default'           => get_option( 'admin_email' )
        ) );

        register_setting( 'snrd_settings_group', 'snrd_admin_notify_email_2', array(
            'sanitize_callback' => 'sanitize_email',
            'default'           => ''
        ) );
    }

    /**
     * Handle manual page generation and 250 stations seed button actions
     */
    public static function handle_page_generation_action() {
        if ( isset( $_GET['snrd_action'] ) && current_user_can( 'manage_options' ) ) {
            if ( 'generate_pages' === $_GET['snrd_action'] ) {
                check_admin_referer( 'snrd_generate_pages_action' );
                Savika_News_Radio_Directory::ensure_core_stations_exist();
                Savika_News_Radio_Directory::auto_create_radio_pages();
                wp_redirect( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_msg' => 'pages_created' ), admin_url( 'edit.php' ) ) );
                exit;
            } elseif ( 'seed_50_stations' === $_GET['snrd_action'] || 'seed_250_stations' === $_GET['snrd_action'] || 'seed_1000_stations' === $_GET['snrd_action'] ) {
                check_admin_referer( 'snrd_seed_50_action' );
                $count = Savika_News_Radio_Directory::seed_sample_stations( true );
                wp_redirect( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_msg' => 'stations_synced', 'count' => $count ), admin_url( 'edit.php' ) ) );
                exit;
            }
        }
    }

    /**
     * Render Settings and Setup Guide page
     */
    public static function render_settings_page() {
        $gen_url       = wp_nonce_url( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_action' => 'generate_pages' ), admin_url( 'edit.php' ) ), 'snrd_generate_pages_action' );
        $seed_1000_url = wp_nonce_url( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_action' => 'seed_1000_stations' ), admin_url( 'edit.php' ) ), 'snrd_seed_50_action' );
        $radio_url     = home_url( '/radio/' );
        $apply_url     = home_url( '/radio/apply/' );
        $synced_cnt    = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 1050;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Savika News Radio Directory — Configuration & Guide', 'savika-news-radio' ); ?></h1>
            <hr>

            <?php if ( isset( $_GET['snrd_msg'] ) && 'stations_synced' === $_GET['snrd_msg'] ) : ?>
                <div class="notice notice-success is-dismissible" style="padding: 12px;">
                    <p style="font-size: 15px; margin: 0 0 8px;">
                        <strong><?php printf( esc_html__( '%d Verified Ghanaian Radio Stations were successfully imported and updated across all 16 regions.', 'savika-news-radio' ), $synced_cnt ); ?></strong>
                    </p>
                    <p style="margin: 0;">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SNRD_POST_TYPE ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'View All Stations in Admin', 'savika-news-radio' ); ?> &rarr;
                        </a>
                        <a href="<?php echo esc_url( $radio_url ); ?>" target="_blank" class="button button-secondary" style="margin-left: 8px;">
                            <?php esc_html_e( 'Open Public Radio Directory', 'savika-news-radio' ); ?> &rarr;
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ( isset( $_GET['snrd_msg'] ) && 'pages_created' === $_GET['snrd_msg'] ) : ?>
                <div class="notice notice-success is-dismissible" style="padding: 12px;">
                    <p style="font-size: 15px; margin: 0 0 8px;"><strong><?php esc_html_e( 'Radio Directory and Application pages were successfully created and verified.', 'savika-news-radio' ); ?></strong></p>
                    <p style="margin: 0;">
                        <a href="<?php echo esc_url( $radio_url ); ?>" target="_blank" class="button button-primary">
                            <?php esc_html_e( 'Open Radio Directory Page', 'savika-news-radio' ); ?> &rarr;
                        </a>
                        <a href="<?php echo esc_url( $apply_url ); ?>" target="_blank" class="button button-secondary" style="margin-left: 8px;">
                            <?php esc_html_e( 'Open Apply Page', 'savika-news-radio' ); ?> &rarr;
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- 1-Click 1,000+ Stations Sync -->
            <div style="background: #ffffff; border: 1px solid #c3c4c7; border-left: 4px solid #006537; padding: 20px; margin: 20px 0; border-radius: 6px; max-width: 850px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="margin: 0 0 6px; color: #1a202c;"><?php esc_html_e( 'One-Click Station Importer: 1,000+ Verified Ghanaian Stations', 'savika-news-radio' ); ?></h2>
                        <p style="margin: 0; font-size: 14px; color: #4a5568;">
                            <?php esc_html_e( 'Instantly populate your directory with 1,000+ radio stations across all 16 regions of Ghana (Greater Accra, Ashanti, Western, Central, Eastern, Volta, Northern, Upper East, Upper West, Bono, Bono East, Ahafo, Oti, Savannah, North East, Western North, and the Ghanaian Diaspora) with live streams, dial frequencies, locations, and logos.', 'savika-news-radio' ); ?>
                        </p>
                    </div>
                </div>
                <p style="margin-top: 15px;">
                    <a href="<?php echo esc_url( $seed_1000_url ); ?>" class="button button-primary button-hero" style="background: #006537; border-color: #004d2a; box-shadow: none;">
                        <span class="dashicons dashicons-update" style="margin-top: 4px; margin-right: 4px;"></span>
                        <?php esc_html_e( 'Sync / Import 1,000+ Ghanaian Radio Stations Now', 'savika-news-radio' ); ?>
                    </a>
                </p>
                <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #edf2f7; font-size: 12px; color: #718096;">
                    <?php esc_html_e( 'Safe to run anytime: updates stream URLs and artwork for existing stations and adds missing ones without deleting your custom stations.', 'savika-news-radio' ); ?>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #c3c4c7; border-left: 4px solid #1a73e8; padding: 20px; margin: 20px 0; border-radius: 6px; max-width: 850px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0; color: #1a202c;"><?php esc_html_e( 'Google Search & SEO Indexing (1,000+ Stations)', 'savika-news-radio' ); ?></h2>
                <p style="font-size: 14px; color: #4a5568;">
                    <?php esc_html_e( 'Your directory automatically generates Google Schema.org structured data (RadioStation & ListenAction) on every single station page so Google can rank your site and display live listen buttons in search results. Submit your dedicated XML Radio Sitemap below directly to Google Search Console to index all 1,000+ stations.', 'savika-news-radio' ); ?>
                </p>
                <div style="background: #f8fafc; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 4px; margin: 12px 0;">
                    <strong><?php esc_html_e( 'Your Dedicated Radio XML Sitemap:', 'savika-news-radio' ); ?></strong><br>
                    <a href="<?php echo esc_url( home_url( '/radio-sitemap.xml' ) ); ?>" target="_blank" style="font-size: 14px; font-weight: 700; color: #1a73e8;">
                        <code><?php echo esc_html( home_url( '/radio-sitemap.xml' ) ); ?></code>
                    </a>
                </div>
                <p style="margin: 0;">
                    <a href="<?php echo esc_url( home_url( '/radio-sitemap.xml' ) ); ?>" target="_blank" class="button button-secondary">
                        <?php esc_html_e( 'Open XML Sitemap in Browser', 'savika-news-radio' ); ?> &rarr;
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'post_type' => SNRD_POST_TYPE, 'page' => 'snrd-settings', 'snrd_action' => 'flush_sitemap' ), admin_url( 'edit.php' ) ), 'snrd_sitemap_flush' ) ); ?>" class="button button-secondary" style="margin-left: 8px;">
                        <?php esc_html_e( 'Refresh Sitemap Cache', 'savika-news-radio' ); ?>
                    </a>
                </p>
            </div>

            <div style="background: #ffffff; border: 1px solid #c3c4c7; border-left: 4px solid #c90000; padding: 20px; margin: 20px 0; border-radius: 6px; max-width: 850px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0; color: #1a202c;"><?php esc_html_e( 'Page Verification & Generator', 'savika-news-radio' ); ?></h2>
                <p style="font-size: 14px;"><?php esc_html_e( 'Click the button below anytime to verify or auto-generate the public Radio Directory and Application pages with the proper shortcodes.', 'savika-news-radio' ); ?></p>
                <p>
                    <a href="<?php echo esc_url( $gen_url ); ?>" class="button button-primary" style="background: #c90000; border-color: #a00000; box-shadow: none;">
                        <?php esc_html_e( 'Generate / Repair Radio Pages', 'savika-news-radio' ); ?>
                    </a>
                </p>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #edf2f7; font-size: 13px; color: #646970;">
                    <strong><?php esc_html_e( 'Direct Public Links:', 'savika-news-radio' ); ?></strong><br>
                    Main Directory: <a href="<?php echo esc_url( $radio_url ); ?>" target="_blank"><code><?php echo esc_html( $radio_url ); ?></code></a><br>
                    Application Form: <a href="<?php echo esc_url( $apply_url ); ?>" target="_blank"><code><?php echo esc_html( $apply_url ); ?></code></a>
                </div>
            </div>

            <form method="post" action="options.php" style="margin-bottom: 30px; max-width: 850px; background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 6px;">
                <h2 style="margin-top: 0;"><?php esc_html_e( 'Administrative Approval Email Notifications', 'savika-news-radio' ); ?></h2>
                <p style="font-size: 13px; color: #555;">
                    <?php esc_html_e( 'Enter two administrator / editor email addresses below. Whenever a radio station application is submitted, both addresses will immediately receive the approval notification email with the 1-click review link.', 'savika-news-radio' ); ?>
                </p>
                <?php
                settings_fields( 'snrd_settings_group' );
                $notify_email_1 = get_option( 'snrd_admin_notify_email_1', get_option( 'snrd_admin_notify_email', get_option( 'admin_email' ) ) );
                $notify_email_2 = get_option( 'snrd_admin_notify_email_2', '' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="snrd_admin_notify_email_1"><?php esc_html_e( 'Primary Approval Email', 'savika-news-radio' ); ?> <span style="color:#c90000;">*</span></label>
                        </th>
                        <td>
                            <input type="email" name="snrd_admin_notify_email_1" id="snrd_admin_notify_email_1" class="regular-text" value="<?php echo esc_attr( $notify_email_1 ); ?>" required />
                            <p class="description"><?php esc_html_e( 'Primary admin email to receive broadcaster submission alerts (e.g. admin@savikanews.com).', 'savika-news-radio' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="snrd_admin_notify_email_2"><?php esc_html_e( 'Secondary Approval Email', 'savika-news-radio' ); ?></label>
                        </th>
                        <td>
                            <input type="email" name="snrd_admin_notify_email_2" id="snrd_admin_notify_email_2" class="regular-text" value="<?php echo esc_attr( $notify_email_2 ); ?>" placeholder="e.g. editor@savikanews.com" />
                            <p class="description"><?php esc_html_e( 'Second administrator or supervisor email address to also receive submission alerts.', 'savika-news-radio' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save Approval Emails', 'savika-news-radio' ) ); ?>
            </form>
        </div>
        <?php
    }
}
