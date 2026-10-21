<?php
/**
 * Professional Broadcaster Application Form Template
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_success = SNRD_Application_Form::is_success();
$errors     = SNRD_Application_Form::get_errors();
?>

<div class="snrd-portal-wrap snrd-form-wrap">

    <?php if ( $is_success ) : ?>
        <div class="snrd-notice snrd-notice-success">
            <h3 class="snrd-notice-title"><?php esc_html_e( 'Application Successfully Submitted', 'savika-news-radio' ); ?></h3>
            <p>
                <?php esc_html_e( 'Thank you for applying to join Savika News Radio Directory. Your station submission has been received and is currently under review by our editorial team. We will verify your public stream link before approving the listing.', 'savika-news-radio' ); ?>
            </p>
            <div style="margin-top: 18px;">
                <a href="<?php echo esc_url( home_url( '/radio/' ) ); ?>" class="snrd-btn-solid">
                    &larr; <?php esc_html_e( 'Return to Radio Directory', 'savika-news-radio' ); ?>
                </a>
            </div>
        </div>
    <?php else : ?>

        <header class="snrd-form-header">
            <h1 class="snrd-form-title"><?php esc_html_e( 'Apply to Broadcast on Savika News Radio', 'savika-news-radio' ); ?></h1>
            <p class="snrd-form-desc">
                <?php esc_html_e( 'Broadcasters, radio stations, and online streams are invited to submit their station for a verified listing in the Savika News Radio Directory. All submissions are reviewed prior to publication.', 'savika-news-radio' ); ?>
            </p>
        </header>

        <?php if ( ! empty( $errors ) ) : ?>
            <div class="snrd-notice snrd-notice-error">
                <strong><?php esc_html_e( 'Please correct the following errors:', 'savika-news-radio' ); ?></strong>
                <ul>
                    <?php foreach ( $errors as $error ) : ?>
                        <li><?php echo esc_html( $error ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="snrd-pro-form">
            <?php wp_nonce_field( 'snrd_submit_radio_application', 'snrd_application_nonce' ); ?>

            <!-- Honeypot anti-spam trap -->
            <div class="snrd-hp-field" style="display:none !important;" aria-hidden="true">
                <label for="snrd_hp_website">Leave this field blank</label>
                <input type="text" name="snrd_hp_website" id="snrd_hp_website" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Field: Station Name -->
            <div class="snrd-input-group">
                <label for="snrd_station_name" class="snrd-input-label">
                    <?php esc_html_e( 'Station Name', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                </label>
                <input 
                    type="text" 
                    name="snrd_station_name" 
                    id="snrd_station_name" 
                    class="snrd-form-input" 
                    required 
                    placeholder="e.g. Kingdom FM 106.1 or Savika Gospel Radio"
                    value="<?php echo isset( $_POST['snrd_station_name'] ) ? esc_attr( $_POST['snrd_station_name'] ) : ''; ?>" 
                />
            </div>

            <!-- Field: Location & Genre row -->
            <div class="snrd-input-row">
                <div class="snrd-input-group col">
                    <label for="snrd_location" class="snrd-input-label">
                        <?php esc_html_e( 'Location / Region', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="snrd_location" 
                        id="snrd_location" 
                        class="snrd-form-input" 
                        required 
                        placeholder="e.g. Mankessim, Ghana or Accra, Ghana"
                        value="<?php echo isset( $_POST['snrd_location'] ) ? esc_attr( $_POST['snrd_location'] ) : ''; ?>" 
                    />
                </div>

                <div class="snrd-input-group col">
                    <label for="snrd_genre" class="snrd-input-label">
                        <?php esc_html_e( 'Primary Genre', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                    </label>
                    <select name="snrd_genre" id="snrd_genre" class="snrd-form-select" required>
                        <option value=""><?php esc_html_e( 'Select Genre', 'savika-news-radio' ); ?></option>
                        <?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : ?>
                            <?php 
                            $selected_genre = isset( $_POST['snrd_genre'] ) ? absint( $_POST['snrd_genre'] ) : 0;
                            foreach ( $genres as $genre ) : ?>
                                <option value="<?php echo esc_attr( $genre->term_id ); ?>" <?php selected( $selected_genre, $genre->term_id ); ?>>
                                    <?php echo esc_html( $genre->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Field: Public Audio Stream URL -->
            <div class="snrd-input-group">
                <label for="snrd_stream_url" class="snrd-input-label">
                    <?php esc_html_e( 'Public Audio Stream URL', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                </label>
                <input 
                    type="url" 
                    name="snrd_stream_url" 
                    id="snrd_stream_url" 
                    class="snrd-form-input" 
                    required 
                    placeholder="https://stream.zeno.fm/xxxxxxxxxx or https://...:8000/stream"
                    value="<?php echo isset( $_POST['snrd_stream_url'] ) ? esc_attr( $_POST['snrd_stream_url'] ) : ''; ?>" 
                />
                <span class="snrd-field-hint">
                    <?php esc_html_e( 'The direct listener link provided by Zeno.fm, Icecast, or Shoutcast. Do not submit private broadcasting passwords.', 'savika-news-radio' ); ?>
                </span>
            </div>

            <!-- Field: Description -->
            <div class="snrd-input-group">
                <label for="snrd_description" class="snrd-input-label">
                    <?php esc_html_e( 'Station Description', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                </label>
                <textarea 
                    name="snrd_description" 
                    id="snrd_description" 
                    class="snrd-form-textarea" 
                    rows="4" 
                    required 
                    placeholder="<?php esc_attr_e( 'Provide a concise overview of your programming, broadcast frequency, format, and schedule.', 'savika-news-radio' ); ?>"
                ><?php echo isset( $_POST['snrd_description'] ) ? esc_textarea( $_POST['snrd_description'] ) : ''; ?></textarea>
            </div>

            <!-- Field: Contact Email -->
            <div class="snrd-input-group">
                <label for="snrd_contact_email" class="snrd-input-label">
                    <?php esc_html_e( 'Applicant Contact Email', 'savika-news-radio' ); ?> <span class="snrd-req">*</span>
                </label>
                <input 
                    type="email" 
                    name="snrd_contact_email" 
                    id="snrd_contact_email" 
                    class="snrd-form-input" 
                    required 
                    placeholder="broadcaster@station.com"
                    value="<?php echo isset( $_POST['snrd_contact_email'] ) ? esc_attr( $_POST['snrd_contact_email'] ) : ''; ?>" 
                />
                <span class="snrd-field-hint">
                    <?php esc_html_e( 'Used exclusively for administrative review correspondence. Your email address remains strictly confidential.', 'savika-news-radio' ); ?>
                </span>
            </div>

            <!-- Field: Station Logo -->
            <div class="snrd-input-group">
                <label for="snrd_station_logo" class="snrd-input-label">
                    <?php esc_html_e( 'Station Logo (Optional)', 'savika-news-radio' ); ?>
                </label>
                <input 
                    type="file" 
                    name="snrd_station_logo" 
                    id="snrd_station_logo" 
                    class="snrd-form-file" 
                    accept="image/jpeg,image/png,image/webp" 
                />
                <span class="snrd-field-hint">
                    <?php esc_html_e( 'Recommended: Square artwork, PNG or JPG format, maximum file size 3MB.', 'savika-news-radio' ); ?>
                </span>
            </div>

            <!-- Field: Zeno.fm Page & Website row -->
            <div class="snrd-input-row">
                <div class="snrd-input-group col">
                    <label for="snrd_zeno_url" class="snrd-input-label">
                        <?php esc_html_e( 'Zeno.fm Profile URL (Optional)', 'savika-news-radio' ); ?>
                    </label>
                    <input 
                        type="url" 
                        name="snrd_zeno_url" 
                        id="snrd_zeno_url" 
                        class="snrd-form-input" 
                        placeholder="https://zeno.fm/radio/..."
                        value="<?php echo isset( $_POST['snrd_zeno_url'] ) ? esc_attr( $_POST['snrd_zeno_url'] ) : ''; ?>" 
                    />
                </div>

                <div class="snrd-input-group col">
                    <label for="snrd_website_url" class="snrd-input-label">
                        <?php esc_html_e( 'Official Website (Optional)', 'savika-news-radio' ); ?>
                    </label>
                    <input 
                        type="url" 
                        name="snrd_website_url" 
                        id="snrd_website_url" 
                        class="snrd-form-input" 
                        placeholder="https://yourstation.com"
                        value="<?php echo isset( $_POST['snrd_website_url'] ) ? esc_attr( $_POST['snrd_website_url'] ) : ''; ?>" 
                    />
                </div>
            </div>

            <!-- Field: Consent -->
            <div class="snrd-input-group snrd-terms-box">
                <label class="snrd-checkbox-label">
                    <input type="checkbox" name="snrd_authorized" value="1" required <?php checked( isset( $_POST['snrd_authorized'] ) ); ?> />
                    <span><?php esc_html_e( 'I confirm that I am authorized to submit this radio station and that the stream is cleared for public listening.', 'savika-news-radio' ); ?> <span class="snrd-req">*</span></span>
                </label>
            </div>

            <div class="snrd-form-submit-row">
                <button type="submit" name="snrd_submit_application" class="snrd-btn-submit">
                    <?php esc_html_e( 'Submit Station Application', 'savika-news-radio' ); ?> &rarr;
                </button>
            </div>

        </form>

    <?php endif; ?>

</div>
