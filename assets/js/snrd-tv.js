/**
 * Savika Live TV Player & 2cTV Channel Switcher Engine
 * Dual-Engine HLS.js + Embed Live Tuner, Theatre Mode & Fullscreen Controller
 *
 * @package Savika_News_Radio_Directory
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        var videoDOM      = document.getElementById('snrd-tv-video-element');
        var iframeDOM     = document.getElementById('snrd-tv-iframe-element');
        var $loader       = $('#snrd-tv-loader');
        var $errorOverlay = $('#snrd-tv-error');
        var $retryBtn     = $('#snrd-tv-retry-btn');
        var $theatreScreen = $('#snrd-theatre-screen');
        var $viewport     = $('#snrd-video-viewport');
        var $channelCards = $('.snrd-channel-card');
        var $categoryChips = $('.snrd-tv-chip');

        var $activeLogo   = $('#snrd-active-tv-logo');
        var $activeTitle  = $('#snrd-active-tv-title');
        var $activeDial   = $('#snrd-active-tv-dial');
        var $activeNet    = $('#snrd-active-tv-network');
        var $activeCat    = $('#snrd-active-tv-category');
        var $activeDesc   = $('#snrd-active-tv-desc');

        var hlsInstance   = null;
        var activeChannelData = null;

        /**
         * Tune to a specific channel
         */
        function tuneChannel(chData) {
            if (!chData || !chData.stream) return;

            activeChannelData = chData;
            $errorOverlay.hide();
            $loader.show();

            // 1. Update Header Information
            if ($activeTitle.length) $activeTitle.text(chData.name);
            if ($activeDial.length)  $activeDial.text(chData.dial);
            if ($activeNet.length)   $activeNet.text(chData.network);
            if ($activeCat.length)   $activeCat.text(chData.category);
            if ($activeDesc.length)  $activeDesc.text(chData.desc);
            if ($activeLogo.length && chData.logo) {
                $activeLogo.attr('src', chData.logo).attr('alt', chData.name);
            }

            // 2. Switch Dual Video Engine
            if (chData.type === 'hls') {
                // Hide embed iframe, show native HTML5 video
                if (iframeDOM) {
                    iframeDOM.src = '';
                    iframeDOM.style.display = 'none';
                }

                if (videoDOM) {
                    videoDOM.style.display = 'block';

                    if (hlsInstance) {
                        hlsInstance.destroy();
                        hlsInstance = null;
                    }

                    if (window.Hls && window.Hls.isSupported()) {
                        hlsInstance = new window.Hls({
                            enableWorker: true,
                            lowLatencyMode: true,
                            maxBufferLength: 10
                        });
                        hlsInstance.loadSource(chData.stream);
                        hlsInstance.attachMedia(videoDOM);
                        hlsInstance.on(window.Hls.Events.MANIFEST_PARSED, function() {
                            $loader.hide();
                            $errorOverlay.hide();
                            videoDOM.play().catch(function() {});
                        });
                        hlsInstance.on(window.Hls.Events.ERROR, function(event, data) {
                            if (data.fatal) {
                                $loader.hide();
                                $errorOverlay.fadeIn(200);
                            }
                        });
                    } else if (videoDOM.canPlayType('application/vnd.apple.mpegurl')) {
                        // iOS Safari native HLS
                        videoDOM.src = chData.stream;
                        videoDOM.addEventListener('loadedmetadata', function() {
                            $loader.hide();
                            $errorOverlay.hide();
                            videoDOM.play().catch(function() {});
                        }, { once: true });
                        videoDOM.addEventListener('error', function() {
                            $loader.hide();
                            $errorOverlay.fadeIn(200);
                        }, { once: true });
                    } else {
                        videoDOM.src = chData.stream;
                        $loader.hide();
                    }
                }
            } else {
                // Embed mode (YouTube / Cloud Live Player)
                if (hlsInstance) {
                    hlsInstance.destroy();
                    hlsInstance = null;
                }
                if (videoDOM) {
                    videoDOM.pause();
                    videoDOM.src = '';
                    videoDOM.style.display = 'none';
                }

                if (iframeDOM) {
                    iframeDOM.style.display = 'block';
                    // Ensure autoplay parameter
                    var embedUrl = chData.stream;
                    if (embedUrl.indexOf('autoplay=') === -1) {
                        embedUrl += (embedUrl.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1&enablejsapi=1';
                    }
                    iframeDOM.src = embedUrl;
                    setTimeout(function() {
                        $loader.hide();
                        $errorOverlay.hide();
                    }, 800);
                }
            }

            // 3. Update Channel Matrix Active State
            $channelCards.removeClass('is-active is-playing');
            $channelCards.find('.snrd-card-status-text').text('LIVE');
            $channelCards.find('.snrd-tune-text').text('Watch Live');

            var $currentCard = $('.snrd-channel-card[data-id="' + chData.id + '"]');
            if ($currentCard.length) {
                $currentCard.addClass('is-active is-playing');
                $currentCard.find('.snrd-card-status-text').text('PLAYING NOW');
                $currentCard.find('.snrd-tune-text').text('Now Playing');
            }

            // 4. Scroll smoothly to player screen on mobile screens
            if ($(window).width() < 768) {
                $('html, body').animate({
                    scrollTop: $theatreScreen.offset().top - 20
                }, 300);
            }
        }

        // Channel Card Click Handler
        $(document).on('click', '.snrd-channel-card', function(e) {
            e.preventDefault();
            var $card = $(this);
            var chData = {
                id:       $card.attr('data-id'),
                name:     $card.attr('data-name'),
                dial:     $card.attr('data-dial'),
                network:  $card.attr('data-network'),
                category: $card.attr('data-category'),
                type:     $card.attr('data-type'),
                stream:   $card.attr('data-stream'),
                logo:     $card.attr('data-logo'),
                desc:     $card.attr('data-desc')
            };

            tuneChannel(chData);
        });

        // Retry Button Click Handler
        if ($retryBtn.length) {
            $retryBtn.on('click', function(e) {
                e.preventDefault();
                $errorOverlay.hide();
                if (activeChannelData) {
                    tuneChannel(activeChannelData);
                }
            });
        }

        // Progressive Filtering & Search Controller for 530+ Channels
        var currentCategory = 'all';
        var currentSearch = '';
        var visibleLimit = 48;
        var pageSize = 48;

        function applyFilters() {
            var matchingCards = [];
            var sQuery = currentSearch.toLowerCase().trim();

            $channelCards.each(function() {
                var $c = $(this);
                var cat = ($c.attr('data-category') || '').toLowerCase();
                var name = ($c.attr('data-name') || '').toLowerCase();
                var dial = ($c.attr('data-dial') || '').toLowerCase();
                var net = ($c.attr('data-network') || '').toLowerCase();

                var matchCat = (currentCategory === 'all') || (cat.indexOf(currentCategory.toLowerCase()) !== -1);
                var matchSearch = !sQuery || (name.indexOf(sQuery) !== -1 || dial.indexOf(sQuery) !== -1 || net.indexOf(sQuery) !== -1 || cat.indexOf(sQuery) !== -1);

                if (matchCat && matchSearch) {
                    matchingCards.push($c);
                } else {
                    $c.hide();
                }
            });

            var isFiltered = (currentCategory !== 'all' || sQuery.length > 0);
            var totalMatches = matchingCards.length;

            if (isFiltered) {
                for (var i = 0; i < totalMatches; i++) {
                    matchingCards[i].show();
                }
                $('#snrd-tv-load-more-wrap').hide();
                $('#snrd-visible-count').text(totalMatches);
            } else {
                for (var j = 0; j < totalMatches; j++) {
                    if (j < visibleLimit) {
                        matchingCards[j].show();
                    } else {
                        matchingCards[j].hide();
                    }
                }
                var shown = Math.min(visibleLimit, totalMatches);
                var remaining = totalMatches - shown;
                $('#snrd-visible-count').text(shown);

                if (remaining > 0) {
                    $('#snrd-load-remaining').text('(' + remaining + ' Remaining)');
                    $('#snrd-tv-load-more-wrap').show();
                } else {
                    $('#snrd-tv-load-more-wrap').hide();
                }
            }
        }

        // Category Filter Chips
        $categoryChips.on('click', function(e) {
            e.preventDefault();
            var $chip = $(this);
            currentCategory = $chip.attr('data-filter');

            $categoryChips.removeClass('active');
            $chip.addClass('active');

            applyFilters();
        });

        // Search Input with Debounce
        var searchTimer = null;
        $('#snrd-tv-search-input').on('input', function() {
            var val = $(this).val();
            if (val.length > 0) {
                $('#snrd-tv-search-clear').show();
            } else {
                $('#snrd-tv-search-clear').hide();
            }
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentSearch = val;
                applyFilters();
            }, 180);
        });

        // Clear Search
        $('#snrd-tv-search-clear').on('click', function() {
            $('#snrd-tv-search-input').val('').focus();
            $(this).hide();
            currentSearch = '';
            applyFilters();
        });

        // Load More Channels
        $('#snrd-tv-load-more-btn').on('click', function(e) {
            e.preventDefault();
            visibleLimit += pageSize;
            applyFilters();
        });

        // Initialize progressive cards
        applyFilters();

        // Theatre Mode Toggle
        $('#snrd-tv-theatre-toggle').on('click', function(e) {
            e.preventDefault();
            $theatreScreen.toggleClass('theatre-mode');
            var isTheatre = $theatreScreen.hasClass('theatre-mode');
            $(this).find('span').text(isTheatre ? 'Default Mode' : 'Theatre');
        });

        // Fullscreen Toggle
        $('#snrd-tv-fullscreen-btn').on('click', function(e) {
            e.preventDefault();
            var elem = document.getElementById('snrd-video-viewport');
            if (!elem) return;

            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        });

    });

})(jQuery);

