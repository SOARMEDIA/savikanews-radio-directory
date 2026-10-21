/**
 * Savika News Radio Directory - Public Frontend Script
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // 1. Single Active Audio Player Logic
        // Ensures only one radio station plays at any given time
        var allAudioElements = $('audio.snrd-audio-player');

        allAudioElements.on('play', function() {
            var currentAudio = this;
            allAudioElements.each(function() {
                if (this !== currentAudio && !this.paused) {
                    this.pause();
                }
            });
        });

        // Handle Audio Errors
        allAudioElements.on('error', function() {
            var $player = $(this);
            var $parent = $player.closest('.snrd-player-box, .snrd-profile-player-card');
            
            if ($parent.find('.snrd-stream-error-notice').length === 0) {
                var errorNotice = $('<div class="snrd-stream-error-notice" style="color: #c53030; font-size: 12px; margin-top: 6px;">' +
                    (snrdConfig.strings.streamError || 'Stream temporarily unavailable or restricted by browser.') +
                '</div>');
                $parent.append(errorNotice);
            }
        });

        // 2. Directory Search & Genre Filtering
        var $searchInput  = $('#snrd-station-search');
        var $clearBtn     = $('#snrd-search-clear');
        var $genrePills   = $('.snrd-genre-pill');
        var $stationCards = $('.snrd-station-card');
        var $noResults    = $('#snrd-no-search-results');

        if ($stationCards.length > 0) {

            var activeGenre = 'all';

            function filterStations() {
                var query = $.trim($searchInput.val()).toLowerCase();
                var visibleCount = 0;

                $stationCards.each(function() {
                    var $card = $(this);
                    var title = $card.attr('data-station-title') || '';
                    var location = $card.attr('data-location') || '';
                    var genres = ($card.attr('data-genres') || '').split(' ');

                    var matchesQuery = (query === '') || (title.indexOf(query) !== -1) || (location.indexOf(query) !== -1);
                    var matchesGenre = (activeGenre === 'all') || ($.inArray(activeGenre, genres) !== -1);

                    if (matchesQuery && matchesGenre) {
                        $card.show();
                        visibleCount++;
                    } else {
                        $card.hide();
                    }
                });

                if (visibleCount === 0) {
                    $noResults.show();
                } else {
                    $noResults.hide();
                }
            }

            // Search input event
            $searchInput.on('input', function() {
                var val = $(this).val();
                if (val.length > 0) {
                    $clearBtn.show();
                } else {
                    $clearBtn.hide();
                }
                filterStations();
            });

            // Clear search button
            $clearBtn.on('click', function() {
                $searchInput.val('').focus();
                $clearBtn.hide();
                filterStations();
            });

            // Genre pill click
            $genrePills.on('click', function() {
                $genrePills.removeClass('active');
                $(this).addClass('active');
                activeGenre = $(this).attr('data-genre');
                filterStations();
            });
        }

    });

})(jQuery);
