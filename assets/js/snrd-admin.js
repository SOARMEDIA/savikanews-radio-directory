/**
 * Savika News Radio Directory - Admin Script
 */

(function($) {
    'use strict';

    $(document).ready(function() {

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
                    $statusBadge.css('color', '#c53030').text('Please enter a Stream URL first.');
                    return;
                }

                $statusBadge.css('color', '#2b6cb0').text('Loading stream...');
                audioDOM.pause();
                $audioSource.attr('src', url);
                audioDOM.load();

                var playPromise = audioDOM.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        $statusBadge.css('color', '#22543d').text('✔ Stream playing successfully!');
                    }).catch(function(error) {
                        $statusBadge.css('color', '#c53030').text('⚠ Could not play stream. Verify direct listener URL.');
                    });
                }
            });

            audioDOM.addEventListener('playing', function() {
                $statusBadge.css('color', '#22543d').text('✔ Stream playing successfully!');
            });

            audioDOM.addEventListener('error', function() {
                $statusBadge.css('color', '#c53030').text('⚠ Playback error. Check URL and CORS / HTTPS compatibility.');
            });
        }

    });

})(jQuery);
