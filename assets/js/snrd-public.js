/**
 * Modern Ghana Radio Public Script
 * Interactive Player, Tabs, View Switcher & Real-Time Search Controller
 *
 * @package Savika_News_Radio_Directory
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        var audioDOM        = document.getElementById('mg-audio-element');
        var singleAudioDOM  = document.getElementById('snrd-station-audio');
        var $activeTitle    = $('#mg-active-title');
        var $activeLoc      = $('#mg-active-location');
        var $activePhone    = $('#mg-active-phone');
        var $activeWeb      = $('#mg-active-website');
        var $statusLabel    = $('#mg-status-label');
        var $startBtn       = $('#mg-start-btn');
        var $pauseBtn       = $('#mg-pause-btn');

        /**
         * 1. Station Selection & Live Audio Stream Trigger
         */
        $(document).on('click', '.mg-station-trigger', function(e) {
            e.preventDefault();

            var $trigger = $(this);
            var title    = $trigger.attr('data-title') || 'Radio Station';
            var stream   = $trigger.attr('data-stream') || '';
            var location = $trigger.attr('data-location') || 'Ghana';
            var phone    = $trigger.attr('data-phone') || '';
            var website  = $trigger.attr('data-website') || '';
            var id       = $trigger.attr('data-id') || '';

            // Update Master Console Display
            if ($activeTitle.length) {
                $activeTitle.text(title.toUpperCase());
                $activeLoc.text(location);
                $activePhone.text(phone);
                $activeWeb.text(website);
            }

            // Update Active Highlights Across Both Grid and Table
            $('.snrd-profile-card-item').removeClass('selected is-playing');
            $('.mg-station-row').removeClass('selected is-playing');
            $('.content-left ul li').removeClass('selected is-playing');
            $('.mg-featured-scroll-list li').removeClass('selected is-playing');
            $('.fm-country ul li a, .fm-settings ul li a').removeClass('selected is-playing');

            if (id) {
                $('.snrd-profile-card-item[data-id="' + id + '"]').addClass('selected is-playing');
                $('.mg-station-row[data-id="' + id + '"]').addClass('selected is-playing');
                $('.mg-station-trigger[data-id="' + id + '"]').addClass('selected is-playing');
            }

            // Stream Audio in Master Console
            if (audioDOM && stream) {
                $statusLabel.text('Connecting...');
                audioDOM.pause();
                audioDOM.src = stream;
                audioDOM.load();

                var playPromise = audioDOM.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        $statusLabel.text('Live Broadcast');
                        $startBtn.hide();
                        $pauseBtn.show();
                    }).catch(function(err) {
                        $statusLabel.text('Live Broadcast (Tap to play)');
                        $startBtn.show();
                        $pauseBtn.hide();
                    });
                }
            } else if ($statusLabel.length) {
                $statusLabel.text('Stream Offline');
                $startBtn.show();
                $pauseBtn.hide();
            }
        });

        // Audio element native event listeners for Master Player
        if (audioDOM) {
            audioDOM.addEventListener('playing', function() {
                $statusLabel.text('Live Broadcast');
                $startBtn.hide();
                $pauseBtn.show();
            });

            audioDOM.addEventListener('pause', function() {
                $statusLabel.text('Broadcast Paused');
                $startBtn.show();
                $pauseBtn.hide();
            });

            audioDOM.addEventListener('error', function() {
                $statusLabel.text('Stream Connection Error');
                $startBtn.show();
                $pauseBtn.hide();
            });
        }

        // Single station page audio listener
        if (singleAudioDOM) {
            var $singleStatus = $('#snrd-single-status');
            singleAudioDOM.addEventListener('playing', function() {
                if ($singleStatus.length) {
                    $singleStatus.text('Broadcasting Live Now');
                }
            });
            singleAudioDOM.addEventListener('pause', function() {
                if ($singleStatus.length) {
                    $singleStatus.text('Live Stream Paused');
                }
            });
            singleAudioDOM.addEventListener('error', function() {
                if ($singleStatus.length) {
                    $singleStatus.text('Stream Temporarily Unavailable');
                }
            });
        }

        // Play / Pause Buttons in Master Console
        $startBtn.on('click', function(e) {
            e.preventDefault();
            if (audioDOM && audioDOM.src) {
                audioDOM.play();
            }
        });

        $pauseBtn.on('click', function(e) {
            e.preventDefault();
            if (audioDOM) {
                audioDOM.pause();
            }
        });

        /**
         * 2. View Switcher: Station Profiles Grid vs Directory Table
         */
        $('.mg-view-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var view = $btn.attr('data-view');

            $('.mg-view-btn').removeClass('active');
            $btn.addClass('active');

            if ('table' === view) {
                $('#snrd-view-grid').hide();
                $('#snrd-view-table').show();
            } else {
                $('#snrd-view-table').hide();
                $('#snrd-view-grid').show();
            }
        });

        /**
         * 3. Scope Chips Filter for Profiles Grid
         */
        $('.mg-scope-chip').on('click', function(e) {
            e.preventDefault();
            var $chip = $(this);
            var filter = $chip.attr('data-filter');

            $('.mg-scope-chip').removeClass('active');
            $chip.addClass('active');

            var $cards = $('.snrd-profile-card-item');

            if ('all' === filter) {
                $cards.show();
            } else {
                $cards.each(function() {
                    var cardScope = $(this).attr('data-scope');
                    if (cardScope === filter) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        /**
         * 4. Scope Tabs inside Table View
         */
        $('.mg-tab-link').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var tab = $this.attr('data-tab');

            $('.nav-tabs li').removeClass('active');
            $('.mg-tab-link').removeClass('active');
            $this.addClass('active');
            $this.closest('li').addClass('active');

            if ('outside' === tab) {
                $('#tab1primary').hide();
                $('#tab2primary').show();
            } else {
                $('#tab2primary').hide();
                $('#tab1primary').show();
            }
        });

        /**
         * 5. Real-Time Directory Search (Filters Cards & Tables)
         */
        var $findInput     = $('#findradio');
        var $profileCards  = $('.snrd-profile-card-item');
        var $stationRows   = $('.mg-station-row');
        var $featuredItems = $('.mg-featured-scroll-list li');
        var $emptyAlert    = $('#mg-search-empty-state');

        $findInput.on('input', function() {
            var q = $.trim($(this).val()).toLowerCase();
            var visibleCount = 0;

            if (q === '') {
                $profileCards.show();
                $stationRows.show();
                $featuredItems.show();
                $emptyAlert.hide();
                return;
            }

            // Filter Profile Cards
            $profileCards.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(q) !== -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Filter Table rows
            $stationRows.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Filter featured scroll lists
            $featuredItems.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0) {
                $emptyAlert.show();
            } else {
                $emptyAlert.hide();
            }
        });

    });

})(jQuery);
