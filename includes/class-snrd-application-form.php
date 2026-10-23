<?php
/**
 * Broadcaster Application Form Handler
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SNRD_Application_Form' ) ) :

class SNRD_Application_Form {

    private static $errors = array();
    private static $success = false;

    /**
     * Initialize form handler
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'process_submission' ) );
    }

    /**
     * Process frontend form submission
     */
    public static function process_submission() {
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['snrd_submit_application'] ) ) {
            return;
        }

        // Verify Honeypot (Spam Trap)
        if ( ! empty( $_POST['snrd_hp_website'] ) ) {
            // Bot detected, silently exit
            wp_die( esc_html__( 'Spam submission detected.', 'savika-news-radio' ) );
        }

        // Verify Nonce
        if ( ! isset( $_POST['snrd_application_nonce'] ) || ! wp_verify_nonce( $_POST['snrd_application_nonce'], 'snrd_submit_radio_application' ) ) {
            self::$errors[] = __( 'Security verification failed. Please refresh the page and try again.', 'savika-news-radio' );
            return;
        }

        // 1. Validate Station Name
        $station_name = isset( $_POST['snrd_station_name'] ) ? sanitize_text_field( trim( $_POST['snrd_station_name'] ) ) : '';
        if ( empty( $station_name ) ) {
            self::$errors[] = __( 'Station Name is required.', 'savika-news-radio' );
        } elseif ( strlen( $station_name ) < 2 || strlen( $station_name ) > 120 ) {
            self::$errors[] = __( 'Station Name must be between 2 and 120 characters.', 'savika-news-radio' );
        }

        // 2. Validate Location
        $location = isset( $_POST['snrd_location'] ) ? sanitize_text_field( trim( $_POST['snrd_location'] ) ) : '';
        if ( empty( $location ) ) {
            self::$errors[] = __( 'Station Location / Region is required.', 'savika-news-radio' );
        }

        // 3. Validate Genre
        $genre_term_id = isset( $_POST['snrd_genre'] ) ? absint( $_POST['snrd_genre'] ) : 0;
        if ( empty( $genre_term_id ) ) {
            self::$errors[] = __( 'Please select a primary genre for your station.', 'savika-news-radio' );
        }

        // 4. Validate Stream URL
        $stream_url = isset( $_POST['snrd_stream_url'] ) ? esc_url_raw( trim( $_POST['snrd_stream_url'] ) ) : '';
        if ( empty( $stream_url ) ) {
            self::$errors[] = __( 'Public Audio Stream URL is required.', 'savika-news-radio' );
        } elseif ( ! filter_var( $stream_url, FILTER_VALIDATE_URL ) ) {
            self::$errors[] = __( 'Please provide a valid audio stream URL (starting with http:// or https://).', 'savika-news-radio' );
        }

        // 5. Validate Contact Email
        $contact_email = isset( $_POST['snrd_contact_email'] ) ? sanitize_email( trim( $_POST['snrd_contact_email'] ) ) : '';
        if ( empty( $contact_email ) || ! is_email( $contact_email ) ) {
            self::$errors[] = __( 'A valid contact email address is required.', 'savika-news-radio' );
        }

        // 6. Validate Description
        $description = isset( $_POST['snrd_description'] ) ? wp_kses_post( trim( $_POST['snrd_description'] ) ) : '';
        if ( empty( $description ) ) {
            self::$errors[] = __( 'Station description is required.', 'savika-news-radio' );
        }

        // 7. Validate Authorization Consent
        $authorized = isset( $_POST['snrd_authorized'] ) ? true : false;
        if ( ! $authorized ) {
            self::$errors[] = __( 'You must confirm that you are authorized to submit this radio station.', 'savika-news-radio' );
        }

        // Optional fields
        $zeno_url    = isset( $_POST['snrd_zeno_url'] ) ? esc_url_raw( trim( $_POST['snrd_zeno_url'] ) ) : '';
        $website_url = isset( $_POST['snrd_website_url'] ) ? esc_url_raw( trim( $_POST['snrd_website_url'] ) ) : '';

        // Validate File Upload if present
        $uploaded_file = isset( $_FILES['snrd_station_logo'] ) ? $_FILES['snrd_station_logo'] : null;
        if ( $uploaded_file && ! empty( $uploaded_file['name'] ) ) {
            $allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );
            $file_type     = wp_check_filetype( $uploaded_file['name'] );
            $max_size      = 3 * 1024 * 1024; // 3MB

            if ( ! in_array( $uploaded_file['type'], $allowed_types, true ) ) {
                self::$errors[] = __( 'Station logo must be a valid JPG, PNG, or WebP image.', 'savika-news-radio' );
            } elseif ( $uploaded_file['size'] > $max_size ) {
                self::$errors[] = __( 'Station logo file size exceeds the 3MB limit.', 'savika-news-radio' );
            }
        }

        // If there are errors, halt and display
        if ( ! empty( self::$errors ) ) {
            return;
        }

        // Create new Station Post in 'pending' status
        $post_data = array(
            'post_title'   => $station_name,
            'post_content' => $description,
            'post_status'  => 'pending', // Strictly pending until admin approval
            'post_type'    => SNRD_POST_TYPE,
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            self::$errors[] = __( 'An error occurred while saving your application. Please try again.', 'savika-news-radio' );
            return;
        }

        // Save Custom Metadata
        update_post_meta( $post_id, 'snrd_location', $location );
        update_post_meta( $post_id, 'snrd_stream_url', $stream_url );
        update_post_meta( $post_id, 'snrd_contact_email', $contact_email );
        update_post_meta( $post_id, 'snrd_zeno_url', $zeno_url );
        update_post_meta( $post_id, 'snrd_website_url', $website_url );
        update_post_meta( $post_id, 'snrd_applicant_authorized', '1' );
        update_post_meta( $post_id, 'snrd_applied_date', current_time( 'mysql' ) );

        // Assign Genre Taxonomy Term
        if ( $genre_term_id > 0 ) {
            wp_set_object_terms( $post_id, array( $genre_term_id ), SNRD_TAXONOMY_GENRE );
        }

        // Handle Logo Upload via WordPress Media API
        if ( $uploaded_file && ! empty( $uploaded_file['name'] ) && empty( $uploaded_file['error'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_id = media_handle_upload( 'snrd_station_logo', $post_id );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        // Send Administrative Email Notification
        self::send_admin_notification( $post_id, $station_name, $location, $stream_url, $contact_email );

        self::$success = true;
    }

    /**
     * Send email notification to site admin(s) - Supports two approval email addresses
     */
    private static function send_admin_notification( $post_id, $station_name, $location, $stream_url, $contact_email ) {
        // Collect both notification emails
        $recipients = array();

        $email_1 = get_option( 'snrd_admin_notify_email_1', get_option( 'snrd_admin_notify_email', get_option( 'admin_email' ) ) );
        if ( ! empty( $email_1 ) && is_email( $email_1 ) ) {
            $recipients[] = $email_1;
        }

        $email_2 = get_option( 'snrd_admin_notify_email_2', '' );
        if ( ! empty( $email_2 ) && is_email( $email_2 ) ) {
            $recipients[] = $email_2;
        }

        // Fallback to site admin email if none configured
        if ( empty( $recipients ) ) {
            $recipients[] = get_option( 'admin_email' );
        }

        // Remove any duplicates
        $recipients = array_unique( $recipients );

        $subject = sprintf( __( '[Savika News Radio] New Station Application: %s', 'savika-news-radio' ), $station_name );
        $edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

        $body  = "Hello Administrator,\r\n\r\n";
        $body .= "A new radio station application has been submitted to Savika News Radio Directory and is awaiting your review.\r\n\r\n";
        $body .= "Station Application Details:\r\n";
        $body .= "--------------------------------------\r\n";
        $body .= "Station Name:  " . $station_name . "\r\n";
        $body .= "Location:      " . $location . "\r\n";
        $body .= "Contact Email: " . $contact_email . "\r\n";
        $body .= "Stream URL:    " . $stream_url . "\r\n\r\n";
        $body .= "Next Step:\r\n";
        $body .= "Click the direct link below to verify the live stream and approve or publish the station:\r\n";
        $body .= $edit_url . "\r\n\r\n";
        $body .= "Thank you,\r\n";
        $body .= "Savika News Radio Directory System\r\n";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        wp_mail( $recipients, $subject, $body, $headers );
    }

    /**
     * Get error messages
     */
    public static function get_errors() {
        return self::$errors;
    }

    /**
     * Get success status
     */
    public static function is_success() {
        return self::$success;
    }
}

endif;
