/**
 * Modern Ghana Radio Interactive Player & Directory Controller
 *
 * @package Savika_News_Radio_Directory
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        var audioDOM = document.getElementById('mg-audio-element');
        var $activeTitle = $('#mg-active-title');
        var $activeLoc   = $('#mg-active-location');
        var $activePhone = $('#mg-active-phone');
        var $activeWeb   = $('#mg-active-website');
        var $statusText  = $('#mg-deck-status-text');

        // 1. Station Click-To-Play in Master Console
        $(document).on('click', '.mg-station-trigger', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var title    = $btn.attr('data-title') || 'Radio Station';
            var stream   = $btn.attr('data-stream') || '';
            var location = $btn.attr('data-location') || 'Ghana';
            var phone    = $btn.attr('data-phone') || '';
            var website  = $btn.attr('data-website') || '';
            var id       = $btn.attr('data-id') || '';

            // Update Master Console Display
            $activeTitle.text(title.toUpperCase());
            $activeLoc.text(location);
            $activePhone.text(phone);
            $activeWeb.text(website);

            // Update Highlight in Directory Table
            $('.mg-directory-row').removeClass('is-playing');
            $('.mg-sidebar-list li a').removeClass('is-playing');
            $('.mg-scroll-list li a').removeClass('is-playing');
            $('.mg-quick-pill').removeClass('is-active');

            if (id) {
                $('.mg-directory-row[data-id="' + id + '"]').addClass('is-playing');
                $('.mg-station-trigger[data-id="' + id + '"]').addClass('is-playing is-active');
            }

            // Stream audio
            if (audioDOM && stream) {
                $statusText.text('Connecting...');
                audioDOM.pause();
                audioDOM.src = stream;
                audioDOM.load();

                var playPromise = audioDOM.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        $statusText.text('Live Broadcast');
                    }).catch(function(err) {
                        $statusText.text('Buffering or Stream Unavailable');
                    });
                }
            } else if ($statusText.length) {
                $statusText.text('No live stream available');
            }
        });

        if (audioDOM) {
            audioDOM.addEventListener('playing', function() {
                $statusText.text('Live Broadcast');
            });
            audioDOM.addEventListener('pause', function() {
                $statusText.text('Broadcast Paused');
            });
            audioDOM.addEventListener('error', function() {
                $statusText.text('Stream Connection Error');
            });
        }

        // 2. Tabs: Stations Inside Ghana vs Outside Ghana
        $('.mg-tab-btn').on('click', function(e) {
            e.preventDefault();
            $('.mg-tab-btn').removeClass('active');
            $(this).addClass('active');

            var tab = $(this).attr('data-tab');
            if ('outside' === tab) {
                $('#mg-panel-inside').hide();
                $('#mg-panel-outside').show();
            } else {
                $('#mg-panel-outside').hide();
                $('#mg-panel-inside').show();
            }
        });

        // 3. Real-Time Directory Search
        var $searchInput = $('#mg-search-input');
        var $allRows     = $('.mg-directory-row');
        var $emptyMsg    = $('#mg-search-empty');

        $searchInput.on('input', function() {
            var q = $.trim($(this).val()).toLowerCase();
            var matches = 0;

            if (q === '') {
                $allRows.show();
                $emptyMsg.hide();
                return;
            }

            $allRows.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(q) !== -1) {
                    $(this).show();
                    matches++;
                } else {
                    $(this).hide();
                }
            });

            if (matches === 0) {
                $emptyMsg.show();
            } else {
                $emptyMsg.hide();
            }
        });

    });

})(jQuery);
