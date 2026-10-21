<?php
/**
 * Application Form Template
 *
 * @package Savika_News_Radio_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_success = SNRD_Application_Form::is_success();
$errors     = SNRD_Application_Form::get_errors();
?>

<div class="snrd-application-container">

    <?php if ( $is_success ) : ?>
        <div class="snrd-alert snrd-alert-success">
            <div class="snrd-alert-icon">✔</div>
            <div class="snrd-alert-body">
                <h3><?php esc_html_e( 'Application Successfully Submitted!', 'savika-news-radio' ); ?></h3>
                <p>
                    <?php esc_html_e( 'Thank you for applying to join Savika News Radio. Your application has been received and is awaiting review by our team. We will review your station details and test your public listening link before publication.', 'savika-news-radio' ); ?>
                </p>
                <p style="margin-top: 15px;">
                    <a href="<?php echo esc_url( home_url( '/radio/' ) ); ?>" class="snrd-btn-secondary">
                        ← <?php esc_html_e( 'Return to Radio Directory', 'savika-news-radio' ); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php else : ?>

        <div class="snrd-form-intro">
            <h2><?php esc_html_e( 'Apply to Broadcast on Savika News Radio', 'savika-news-radio' ); ?></h2>
            <p>
                <?php esc_html_e( 'Join our growing radio directory for free! Whether you are an FM station streaming online, a community broadcaster, a gospel station, or an independent talk program, submit your public stream details below for administrator review and listing.', 'savika-news-radio' ); ?>
            </p>
        </div>

        <?php if ( ! empty( $errors ) ) : ?>
            <div class="snrd-alert snrd-alert-error">
                <div class="snrd-alert-icon">⚠️</div>
                <div class="snrd-alert-body">
                    <strong><?php esc_html_e( 'Please correct the following errors:', 'savika-news-radio' ); ?></strong>
                    <ul>
                        <?php foreach ( $errors as $error ) : ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="snrd-form">
            <?php wp_nonce_field( 'snrd_submit_radio_application', 'snrd_application_nonce' ); ?>

            <!-- Honeypot anti-spam trap (invisible to human visitors) -->
            <div class="snrd-hp-field" style="display:none !important;" aria-hidden="true">
                <label for="snrd_hp_website">Leave this field blank</label>
                <input type="text" name="snrd_hp_website" id="snrd_hp_website" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Field: Station Name -->
            <div class="snrd-form-group">
                <label for="snrd_station_name" class="snrd-label">
                    <?php esc_html_e( 'Radio Station Name', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                </label>
                <input 
                    type="text" 
                    name="snrd_station_name" 
                    id="snrd_station_name" 
                    class="snrd-input" 
                    required 
                    placeholder="<?php esc_attr_e( 'e.g. Kingdom FM 106.1 or Savika Gospel Radio', 'savika-news-radio' ); ?>"
                    value="<?php echo isset( $_POST['snrd_station_name'] ) ? esc_attr( $_POST['snrd_station_name'] ) : ''; ?>" 
                />
            </div>

            <!-- Field: Location & Genre row -->
            <div class="snrd-form-row">
                <div class="snrd-form-group snrd-col">
                    <label for="snrd_location" class="snrd-label">
                        <?php esc_html_e( 'Location / Region', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="snrd_location" 
                        id="snrd_location" 
                        class="snrd-input" 
                        required 
                        placeholder="<?php esc_attr_e( 'e.g. Mankessim, Ghana or Accra, Ghana', 'savika-news-radio' ); ?>"
                        value="<?php echo isset( $_POST['snrd_location'] ) ? esc_attr( $_POST['snrd_location'] ) : ''; ?>" 
                    />
                </div>

                <div class="snrd-form-group snrd-col">
                    <label for="snrd_genre" class="snrd-label">
                        <?php esc_html_e( 'Primary Genre', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                    </label>
                    <select name="snrd_genre" id="snrd_genre" class="snrd-select" required>
                        <option value=""><?php esc_html_e( '— Select Genre —', 'savika-news-radio' ); ?></option>
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
            <div class="snrd-form-group">
                <label for="snrd_stream_url" class="snrd-label">
                    <?php esc_html_e( 'Public Audio Stream URL', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                </label>
                <input 
                    type="url" 
                    name="snrd_stream_url" 
                    id="snrd_stream_url" 
                    class="snrd-input" 
                    required 
                    placeholder="https://stream.zeno.fm/..."
                    value="<?php echo isset( $_POST['snrd_stream_url'] ) ? esc_attr( $_POST['snrd_stream_url'] ) : ''; ?>" 
                />
                <small class="snrd-help-text">
                    <?php esc_html_e( 'The direct live audio listener link from Zeno.fm or your streaming server (Icecast/Shoutcast). Never share private broadcast/BUTT source passwords.', 'savika-news-radio' ); ?>
                </small>
            </div>

            <!-- Field: Description -->
            <div class="snrd-form-group">
                <label for="snrd_description" class="snrd-label">
                    <?php esc_html_e( 'Station Description / About Us', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                </label>
                <textarea 
                    name="snrd_description" 
                    id="snrd_description" 
                    class="snrd-textarea" 
                    rows="4" 
                    required 
                    placeholder="<?php esc_attr_e( 'Tell listeners about your programs, music format, frequency, schedule, and mission...', 'savika-news-radio' ); ?>"
                ><?php echo isset( $_POST['snrd_description'] ) ? esc_textarea( $_POST['snrd_description'] ) : ''; ?></textarea>
            </div>

            <!-- Field: Contact Email -->
            <div class="snrd-form-group">
                <label for="snrd_contact_email" class="snrd-label">
                    <?php esc_html_e( 'Contact Email (Broadcaster / Admin)', 'savika-news-radio' ); ?> <span class="snrd-required">*</span>
                </label>
                <input 
                    type="email" 
                    name="snrd_contact_email" 
                    id="snrd_contact_email" 
                    class="snrd-input" 
                    required 
                    placeholder="broadcaster@yourstation.com"
                    value="<?php echo isset( $_POST['snrd_contact_email'] ) ? esc_attr( $_POST['snrd_contact_email'] ) : ''; ?>" 
                />
                <small class="snrd-help-text">
                    <?php esc_html_e( 'For administrative correspondence only. Your email address will remain confidential and will NOT be shown publicly on the directory.', 'savika-news-radio' ); ?>
                </small>
            </div>

            <!-- Field: Station Logo -->
            <div class="snrd-form-group">
                <label for="snrd_station_logo" class="snrd-label">
                    <?php esc_html_e( 'Station Logo / Artwork (Optional)', 'savika-news-radio' ); ?>
                </label>
                <input 
                    type="file" 
                    name="snrd_station_logo" 
                    id="snrd_station_logo" 
                    class="snrd-file-input" 
                    accept="image/jpeg,image/png,image/webp" 
                />
                <small class="snrd-help-text">
                    <?php esc_html_e( 'Accepted formats: JPG, PNG, WebP (Square artwork recommended, max size: 3MB).', 'savika-news-radio' ); ?>
                </small>
            </div>

            <!-- Field: Zeno.fm Page & Website row -->
            <div class="snrd-form-row">
                <div class="snrd-form-group snrd-col">
                    <label for="snrd_zeno_url" class="snrd-label">
                        <?php esc_html_e( 'Zeno.fm Profile URL (Optional)', 'savika-news-radio' ); ?>
                    </label>
                    <input 
                        type="url" 
                        name="snrd_zeno_url" 
                        id="snrd_zeno_url" 
                        class="snrd-input" 
                        placeholder="https://zeno.fm/radio/..."
                        value="<?php echo isset( $_POST['snrd_zeno_url'] ) ? esc_attr( $_POST['snrd_zeno_url'] ) : ''; ?>" 
                    />
                </div>

                <div class="snrd-form-group snrd-col">
                    <label for="snrd_website_url" class="snrd-label">
                        <?php esc_html_e( 'Official Website (Optional)', 'savika-news-radio' ); ?>
                    </label>
                    <input 
                        type="url" 
                        name="snrd_website_url" 
                        id="snrd_website_url" 
                        class="snrd-input" 
                        placeholder="https://yourstation.com"
                        value="<?php echo isset( $_POST['snrd_website_url'] ) ? esc_attr( $_POST['snrd_website_url'] ) : ''; ?>" 
                    />
                </div>
            </div>

            <!-- Field: Confirmation checkbox -->
            <div class="snrd-form-group snrd-checkbox-group">
                <label class="snrd-checkbox-label">
                    <input type="checkbox" name="snrd_authorized" value="1" required <?php checked( isset( $_POST['snrd_authorized'] ) ); ?> />
                    <span><?php esc_html_e( 'I confirm that I am authorized to submit this station and that the audio stream is cleared for public listening.', 'savika-news-radio' ); ?> <span class="snrd-required">*</span></span>
                </label>
            </div>

            <div class="snrd-form-actions">
                <button type="submit" name="snrd_submit_application" class="snrd-submit-btn">
                    <?php esc_html_e( 'Submit Station Application', 'savika-news-radio' ); ?>
                </button>
            </div>

        </form>

    <?php endif; ?>

</div>
