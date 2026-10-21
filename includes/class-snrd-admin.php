<?php
/**
 * Admin Meta Boxes, Columns, and Stream Testing Interface
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
    }

    /**
     * Register meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'snrd_station_details',
            __( 'Radio Station Stream & Broadcast Details', 'savika-news-radio' ),
            array( __CLASS__, 'render_station_meta_box' ),
            SNRD_POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'snrd_station_applicant',
            __( 'Broadcaster Application & Contact Info (Private)', 'savika-news-radio' ),
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
        $zeno_url     = get_post_meta( $post->ID, 'snrd_zeno_url', true );
        $website_url  = get_post_meta( $post->ID, 'snrd_website_url', true );
        ?>
        <table class="form-table snrd-admin-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="snrd_stream_url"><strong><?php esc_html_e( 'Public Audio Stream URL', 'savika-news-radio' ); ?></strong></label>
                    </th>
                    <td>
                        <input type="url" name="snrd_stream_url" id="snrd_stream_url" class="large-text" value="<?php echo esc_url( $stream_url ); ?>" placeholder="https://stream.zeno.fm/..." />
                        <p class="description">
                            <?php esc_html_e( 'Direct live audio stream endpoint (MP3/AAC Icecast/Shoutcast/Zeno.fm stream). Must start with http:// or https:// (HTTPS strongly recommended).', 'savika-news-radio' ); ?>
                        </p>
                        
                        <!-- Admin Stream Quick-Test Player -->
                        <div class="snrd-admin-test-player" style="margin-top: 10px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                            <strong><?php esc_html_e( 'Admin Stream Test:', 'savika-news-radio' ); ?></strong>
                            <p style="margin: 5px 0; font-size: 13px; color: #555;">
                                <?php esc_html_e( 'Always test stream playback before approving and publishing.', 'savika-news-radio' ); ?>
                            </p>
                            <audio id="snrd-admin-audio-preview" controls preload="none" style="width: 100%; max-width: 450px; margin-top: 6px; display: block;">
                                <source src="<?php echo esc_url( $stream_url ); ?>" id="snrd-admin-audio-source" />
                                <?php esc_html_e( 'Your browser does not support HTML5 audio.', 'savika-news-radio' ); ?>
                            </audio>
                            <button type="button" class="button button-secondary" id="snrd-reload-stream-btn" style="margin-top: 8px;">
                                <span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php esc_html_e( 'Load & Test Stream URL', 'savika-news-radio' ); ?>
                            </button>
                            <span id="snrd-stream-test-status" style="margin-left: 10px; font-weight: 500;"></span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="snrd_location"><strong><?php esc_html_e( 'Station Location / Region', 'savika-news-radio' ); ?></strong></label>
                    </th>
                    <td>
                        <input type="text" name="snrd_location" id="snrd_location" class="regular-text" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'e.g. Accra, Ghana or Mankessim, Ghana', 'savika-news-radio' ); ?>" />
                        <p class="description"><?php esc_html_e( 'City, region, or country where the station broadcasts from.', 'savika-news-radio' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="snrd_zeno_url"><?php esc_html_e( 'Zeno.fm Listening Page (Optional)', 'savika-news-radio' ); ?></label>
                    </th>
                    <td>
                        <input type="url" name="snrd_zeno_url" id="snrd_zeno_url" class="large-text" value="<?php echo esc_url( $zeno_url ); ?>" placeholder="https://zeno.fm/radio/station-slug/" />
                        <p class="description"><?php esc_html_e( 'Official Zeno.fm station profile page used as a fallback link for listeners.', 'savika-news-radio' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="snrd_website_url"><?php esc_html_e( 'Official Website (Optional)', 'savika-news-radio' ); ?></label>
                    </th>
                    <td>
                        <input type="url" name="snrd_website_url" id="snrd_website_url" class="large-text" value="<?php echo esc_url( $website_url ); ?>" placeholder="https://example.com" />
                    </td>
                </tr>
            </tbody>
        </table>
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
                <span class="description"><?php esc_html_e( 'Private: never shown publicly to visitors.', 'savika-news-radio' ); ?></span>
            </p>

            <p>
                <strong><?php esc_html_e( 'Authorized Submitter:', 'savika-news-radio' ); ?></strong><br>
                <?php echo ( '1' === $authorized ) ? '<span style="color: green; font-weight: bold;">✔ ' . esc_html__( 'Confirmed Yes', 'savika-news-radio' ) . '</span>' : esc_html__( 'Manual / Admin entry', 'savika-news-radio' ); ?>
            </p>

            <?php if ( $applied_date ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Submitted On:', 'savika-news-radio' ); ?></strong><br>
                    <code><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $applied_date ) ) ); ?></code>
                </p>
            <?php endif; ?>

            <div style="background: #fff8e5; border-left: 4px solid #ffb900; padding: 8px; margin-top: 10px; font-size: 12px;">
                <strong><?php esc_html_e( 'Review Policy:', 'savika-news-radio' ); ?></strong>
                <p style="margin: 4px 0 0;">
                    <?php esc_html_e( 'Stations remain in "Pending Review" until you verify the stream and click "Publish".', 'savika-news-radio' ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public static function save_meta_box_data( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['snrd_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['snrd_meta_box_nonce'], 'snrd_save_station_details' ) ) {
            return;
        }

        // Avoid autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user capabilities
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sanitize and save fields
        if ( isset( $_POST['snrd_stream_url'] ) ) {
            update_post_meta( $post_id, 'snrd_stream_url', esc_url_raw( trim( $_POST['snrd_stream_url'] ) ) );
        }

        if ( isset( $_POST['snrd_location'] ) ) {
            update_post_meta( $post_id, 'snrd_location', sanitize_text_field( trim( $_POST['snrd_location'] ) ) );
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
                $new_columns['snrd_location_col'] = __( 'Location', 'savika-news-radio' );
                $new_columns['snrd_stream_col']   = __( 'Live Stream URL', 'savika-news-radio' );
                $new_columns['snrd_email_col']    = __( 'Contact Email', 'savika-news-radio' );
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
                    echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'style' => 'border-radius: 4px; object-fit: cover;' ) );
                } else {
                    echo '<span style="display:inline-block;width:40px;height:40px;background:#e2e8f0;border-radius:4px;text-align:center;line-height:40px;color:#718096;"><span class="dashicons dashicons-format-audio" style="line-height:40px;"></span></span>';
                }
                break;

            case 'snrd_location_col':
                $loc = get_post_meta( $post_id, 'snrd_location', true );
                echo $loc ? esc_html( $loc ) : '<span style="color:#a0aec0;">—</span>';
                break;

            case 'snrd_stream_col':
                $url = get_post_meta( $post_id, 'snrd_stream_url', true );
                if ( $url ) {
                    echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" style="font-size:12px;word-break:break-all;">' . esc_html( wp_trim_words( $url, 5, '...' ) ) . '</a>';
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
        register_setting( 'snrd_settings_group', 'snrd_admin_notify_email', array(
            'sanitize_callback' => 'sanitize_email',
            'default'           => get_option( 'admin_email' )
        ) );
    }

    /**
     * Render Settings and Setup Guide page
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Savika News Radio Directory — Configuration & Guide', 'savika-news-radio' ); ?></h1>
            <hr>

            <form method="post" action="options.php" style="margin-bottom: 30px;">
                <?php
                settings_fields( 'snrd_settings_group' );
                $notify_email = get_option( 'snrd_admin_notify_email', get_option( 'admin_email' ) );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="snrd_admin_notify_email"><?php esc_html_e( 'Notification Email for New Applications', 'savika-news-radio' ); ?></label>
                        </th>
                        <td>
                            <input type="email" name="snrd_admin_notify_email" id="snrd_admin_notify_email" class="regular-text" value="<?php echo esc_attr( $notify_email ); ?>" />
                            <p class="description"><?php esc_html_e( 'Email address that receives an alert whenever a broadcaster submits an application.', 'savika-news-radio' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save Settings', 'savika-news-radio' ) ); ?>
            </form>

            <div class="card" style="max-width: 800px; padding: 20px;">
                <h2><?php esc_html_e( 'Elementor Page Setup & Shortcodes', 'savika-news-radio' ); ?></h2>
                <p><?php esc_html_e( 'To display the radio directory and application form on your website using Elementor or standard pages, use these shortcodes:', 'savika-news-radio' ); ?></p>

                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Page', 'savika-news-radio' ); ?></th>
                            <th><?php esc_html_e( 'Suggested URL', 'savika-news-radio' ); ?></th>
                            <th><?php esc_html_e( 'Shortcode to insert in Elementor', 'savika-news-radio' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e( 'Main Radio Directory', 'savika-news-radio' ); ?></strong></td>
                            <td><code>/radio/</code></td>
                            <td><code>[savika_radio_directory]</code></td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e( 'Broadcaster Application Form', 'savika-news-radio' ); ?></strong></td>
                            <td><code>/radio/apply/</code></td>
                            <td><code>[savika_radio_apply]</code></td>
                        </tr>
                    </tbody>
                </table>

                <h3 style="margin-top: 25px;"><?php esc_html_e( 'How to test Zeno.fm Streams with BUTT', 'savika-news-radio' ); ?></h3>
                <ol>
                    <li><?php esc_html_e( 'Broadcasters configure BUTT using their private Zeno.fm server, port, mountpoint and password.', 'savika-news-radio' ); ?></li>
                    <li><?php esc_html_e( 'Once broadcasting starts, copy the public listener link from Zeno.fm (e.g. https://stream.zeno.fm/xxxx or Icecast mount URL).', 'savika-news-radio' ); ?></li>
                    <li><?php esc_html_e( 'Paste it into the application form or station record.', 'savika-news-radio' ); ?></li>
                    <li><?php esc_html_e( 'Use the "Load & Test Stream URL" button in the station editor to confirm audio plays before clicking Publish.', 'savika-news-radio' ); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }
}
