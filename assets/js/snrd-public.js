/**
 * Savika News Radio Directory - Modern Audio Engine & UI Controller
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // ==========================================
        // 1. DIRECTORY MASTER AUDIO & STICKY DOCK
        // ==========================================
        var masterAudio = document.getElementById('snrd-master-audio');
        var $stickyBar  = $('#snrd-sticky-player');
        var $stickyLogo = $('#snrd-sticky-logo');
        var $stickyTitle = $('#snrd-sticky-title');
        var $stickyPlayBtn = $('#snrd-sticky-play-btn');
        var $stickyVol  = $('#snrd-sticky-volume');
        var $stickyPageBtn = $('#snrd-sticky-details-link');
        var $stickyClose = $('#snrd-sticky-close-btn');

        var currentActiveCard = null;

        function setCardPlayingState($card, isPlaying) {
            if (!$card || !$card.length) return;

            var $playIcon  = $card.find('.snrd-icon-play');
            var $pauseIcon = $card.find('.snrd-icon-pause');
            var $eqBars    = $card.find('.snrd-equalizer-bars');

            if (isPlaying) {
                $card.addClass('is-active-playing');
                $playIcon.hide();
                $pauseIcon.show();
                $eqBars.show();
            } else {
                $card.removeClass('is-active-playing');
                $playIcon.show();
                $pauseIcon.hide();
                $eqBars.hide();
            }
        }

        function setStickyPlayIcon(isPlaying) {
            if (isPlaying) {
                $('.snrd-sticky-icon-play').hide();
                $('.snrd-sticky-icon-pause').show();
            } else {
                $('.snrd-sticky-icon-play').show();
                $('.snrd-sticky-icon-pause').hide();
            }
        }

        // Click on station card play overlay
        $(document).on('click', '.snrd-play-overlay-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $card = $(this).closest('.snrd-card');
            var streamUrl = $card.attr('data-stream-url');
            var stationName = $card.attr('data-station-name');
            var stationLogo = $card.attr('data-station-logo') || '';
            var permalink = $card.attr('data-station-permalink');

            if (!streamUrl || !masterAudio) return;

            // If clicking same station that is already playing -> Toggle Pause
            if (currentActiveCard && currentActiveCard[0] === $card[0]) {
                if (!masterAudio.paused) {
                    masterAudio.pause();
                    setCardPlayingState($card, false);
                    setStickyPlayIcon(false);
                } else {
                    masterAudio.play().then(function() {
                        setCardPlayingState($card, true);
                        setStickyPlayIcon(true);
                    }).catch(function() {});
                }
                return;
            }

            // If switching stations -> Reset previous
            if (currentActiveCard) {
                setCardPlayingState(currentActiveCard, false);
            }

            currentActiveCard = $card;
            masterAudio.src = streamUrl;
            masterAudio.load();

            // Populate sticky player bar
            $stickyTitle.text(stationName);
            if (stationLogo) {
                $stickyLogo.attr('src', stationLogo).show();
            } else {
                $stickyLogo.hide();
            }
            $stickyPageBtn.attr('href', permalink);
            $stickyBar.fadeIn(200);

            var playPromise = masterAudio.play();
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    setCardPlayingState($card, true);
                    setStickyPlayIcon(true);
                }).catch(function(error) {
                    setCardPlayingState($card, false);
                    setStickyPlayIcon(false);
                });
            }
        });

        // Sticky Bar Play/Pause toggle
        $stickyPlayBtn.on('click', function(e) {
            e.preventDefault();
            if (!masterAudio) return;

            if (masterAudio.paused) {
                masterAudio.play().then(function() {
                    setStickyPlayIcon(true);
                    if (currentActiveCard) setCardPlayingState(currentActiveCard, true);
                });
            } else {
                masterAudio.pause();
                setStickyPlayIcon(false);
                if (currentActiveCard) setCardPlayingState(currentActiveCard, false);
            }
        });

        // Sticky volume
        $stickyVol.on('input', function() {
            if (masterAudio) {
                masterAudio.volume = parseFloat($(this).val());
            }
        });

        // Sticky close
        $stickyClose.on('click', function() {
            if (masterAudio) {
                masterAudio.pause();
            }
            if (currentActiveCard) {
                setCardPlayingState(currentActiveCard, false);
            }
            $stickyBar.fadeOut(200);
        });

        // Audio events on Master Audio
        if (masterAudio) {
            masterAudio.addEventListener('ended', function() {
                setStickyPlayIcon(false);
                if (currentActiveCard) setCardPlayingState(currentActiveCard, false);
            });
            masterAudio.addEventListener('error', function() {
                setStickyPlayIcon(false);
                if (currentActiveCard) setCardPlayingState(currentActiveCard, false);
            });
        }


        // ==========================================
        // 2. SINGLE STATION PAGE MASTER PLAYER
        // ==========================================
        var singleAudio = document.getElementById('snrd-single-audio-engine');
        var $singlePlayBtn = $('#snrd-single-play-btn');
        var $singleVol = $('#snrd-single-vol');
        var $singleStatus = $('#snrd-stream-status-text');
        var $singleWaveform = $('#snrd-waveform');

        if ($singlePlayBtn.length && singleAudio) {

            $singlePlayBtn.on('click', function(e) {
                e.preventDefault();

                if (singleAudio.paused) {
                    $singleStatus.text('Connecting to Live Stream...');
                    var p = singleAudio.play();
                    if (p !== undefined) {
                        p.then(function() {
                            $singlePlayBtn.find('.snrd-play-icon').hide();
                            $singlePlayBtn.find('.snrd-pause-icon').show();
                            $singleStatus.text('LIVE BROADCAST ON AIR');
                            $singleWaveform.addClass('is-active').show();
                        }).catch(function(err) {
                            $singleStatus.text('Stream unavailable. Try Zeno.fm player below.');
                            $singleWaveform.removeClass('is-active').hide();
                        });
                    }
                } else {
                    singleAudio.pause();
                    $singlePlayBtn.find('.snrd-play-icon').show();
                    $singlePlayBtn.find('.snrd-pause-icon').hide();
                    $singleStatus.text('Broadcast Paused (Click to Resume)');
                    $singleWaveform.removeClass('is-active').hide();
                }
            });

            $singleVol.on('input', function() {
                singleAudio.volume = parseFloat($(this).val());
            });

            singleAudio.addEventListener('playing', function() {
                $singleStatus.text('LIVE BROADCAST ON AIR');
                $singleWaveform.addClass('is-active').show();
            });

            singleAudio.addEventListener('error', function() {
                $singleStatus.text('Stream connection interrupted.');
                $singleWaveform.removeClass('is-active').hide();
            });
        }


        // ==========================================
        // 3. SEARCH & GENRE FILTER ENGINE
        // ==========================================
        var $searchInput  = $('#snrd-station-search');
        var $clearBtn     = $('#snrd-search-clear');
        var $tabPills     = $('.snrd-tab-pill');
        var $cards        = $('.snrd-card');
        var $countDisplay = $('#snrd-count-display');
        var $noResults    = $('#snrd-no-search-results');

        if ($cards.length > 0) {
            var activeGenre = 'all';

            function filterStations() {
                var query = $.trim($searchInput.val()).toLowerCase();
                var count = 0;

                $cards.each(function() {
                    var $c = $(this);
                    var title = $c.attr('data-station-title') || '';
                    var location = $c.attr('data-location') || '';
                    var freq = $c.attr('data-frequency') || '';
                    var genres = ($c.attr('data-genres') || '').split(' ');

                    var matchesSearch = (query === '') || 
                        (title.indexOf(query) !== -1) || 
                        (location.indexOf(query) !== -1) || 
                        (freq.indexOf(query) !== -1);

                    var matchesGenre = (activeGenre === 'all') || ($.inArray(activeGenre, genres) !== -1);

                    if (matchesSearch && matchesGenre) {
                        $c.show();
                        count++;
                    } else {
                        $c.hide();
                    }
                });

                $countDisplay.text(count);

                if (count === 0) {
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

            $tabPills.on('click', function() {
                $tabPills.removeClass('active');
                $(this).addClass('active');
                activeGenre = $(this).attr('data-genre');
                filterStations();
            });
        }

    });

})(jQuery);
