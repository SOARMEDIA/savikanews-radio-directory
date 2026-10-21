/**
 * Savika News Radio Directory - Admin Script
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // 1. Stream Testing Player
        var $streamInput   = $('#snrd_stream_url');
        var $audioPlayer   = $('#snrd-admin-audio-preview');
        var $audioSource   = $('#snrd-admin-audio-source');
        var $testBtn       = $('#snrd-reload-stream-btn');
        var $statusBadge   = $('#snrd-stream-test-status');

        if ($testBtn.length && $audioPlayer.length) {
            var audioDOM = $audioPlayer[0];

            $testBtn.on('click', function(e) {
                e.preventDefault();
                var url = $.trim($streamInput.val());

                if (!url) {
                    $statusBadge.css('color', '#c53030').text('⚠ Enter a Stream URL first.');
                    return;
                }

                $statusBadge.css('color', '#2b6cb0').text('Connecting to stream...');
                audioDOM.pause();
                $audioSource.attr('src', url);
                audioDOM.load();

                var playPromise = audioDOM.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        $statusBadge.css('color', '#15803d').text('✔ Live Audio Playing!');
                    }).catch(function(error) {
                        $statusBadge.css('color', '#b91c1c').text('⚠ Playback error. Check URL / format.');
                    });
                }
            });

            audioDOM.addEventListener('playing', function() {
                $statusBadge.css('color', '#15803d').text('✔ Live Audio Playing!');
            });

            audioDOM.addEventListener('error', function() {
                $statusBadge.css('color', '#b91c1c').text('⚠ Playback error. Check direct listener URL.');
            });
        }

        // 2. 1-Click Logo Media Uploader
        var fileFrame;
        var $selectLogoBtn = $('#snrd-select-logo-btn');
        var $removeLogoBtn = $('#snrd-remove-logo-btn');
        var $thumbInput    = $('#_thumbnail_id');
        var $previewBox    = $('#snrd-logo-preview');
        var $emptyBox      = $('#snrd-logo-empty');
        var $logoImg       = $('#snrd-logo-img');

        $selectLogoBtn.on('click', function(e) {
            e.preventDefault();

            if (fileFrame) {
                fileFrame.open();
                return;
            }

            fileFrame = wp.media({
                title: 'Select or Upload Station Logo',
                button: { text: 'Use as Station Logo' },
                multiple: false,
                library: { type: 'image' }
            });

            fileFrame.on('select', function() {
                var attachment = fileFrame.state().get('selection').first().toJSON();
                $thumbInput.val(attachment.id);

                var imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                $logoImg.attr('src', imgUrl);
                $previewBox.show();
                $emptyBox.hide();
                $removeLogoBtn.show();
            });

            fileFrame.open();
        });

        $removeLogoBtn.on('click', function(e) {
            e.preventDefault();
            $thumbInput.val('-1');
            $logoImg.attr('src', '');
            $previewBox.hide();
            $emptyBox.show();
            $removeLogoBtn.hide();
        });

    });

})(jQuery);
