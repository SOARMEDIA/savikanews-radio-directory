/**
 * Savika News Radio Directory - Public Frontend Script
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // ==========================================
        // 1. SINGLE ACTIVE AUDIO PLAYER ENFORCEMENT
        // ==========================================
        // Uses native HTML5 audio for 100% reliable Icecast/Shoutcast/Zeno streaming
        var $allAudio = $('audio.snrd-live-audio, audio.snrd-master-single-player');

        $allAudio.on('play', function() {
            var currentAudio = this;
            $allAudio.each(function() {
                if (this !== currentAudio && !this.paused) {
                    this.pause();
                }
            });
        });

        // ==========================================
        // 2. LIVE SEARCH & GENRE FILTER ENGINE
        // ==========================================
        var $searchInput  = $('#snrd-station-search');
        var $clearBtn     = $('#snrd-search-clear');
        var $filterTabs   = $('.snrd-filter-tab');
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
                    var freq = $card.attr('data-frequency') || '';
                    var genres = ($card.attr('data-genres') || '').split(' ');

                    var matchesSearch = (query === '') || 
                        (title.indexOf(query) !== -1) || 
                        (location.indexOf(query) !== -1) || 
                        (freq.indexOf(query) !== -1);

                    var matchesGenre = (activeGenre === 'all') || ($.inArray(activeGenre, genres) !== -1);

                    if (matchesSearch && matchesGenre) {
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

            $searchInput.on('input', function() {
                var val = $(this).val();
                if (val.length > 0) {
                    $clearBtn.show();
                } else {
                    $clearBtn.hide();
                }
                filterStations();
            });

            $clearBtn.on('click', function() {
                $searchInput.val('').focus();
                $clearBtn.hide();
                filterStations();
            });

            $filterTabs.on('click', function() {
                $filterTabs.removeClass('active');
                $(this).addClass('active');
                activeGenre = $(this).attr('data-genre');
                filterStations();
            });
        }

    });

})(jQuery);
