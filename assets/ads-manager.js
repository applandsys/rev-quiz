/* Ad loading and placement shared by all pages. Settings: ads-config.js. */
(function (window, document) {
    'use strict';

    var config = window.SiteAdsConfig || {};
    var formats = config.formats || {};
    var slots = config.slots || {};
    var publisherId = config.publisherId || '';
    var canLoad = config.enabled === true && /^ca-pub-\d+$/.test(publisherId);
    var rewardedInMemory = false;
    var rewardPending = false;

    function isEnabled(format) {
        return canLoad && formats[format] === true;
    }

    function hideSlot(element) {
        var container = element.closest('.banner') || element;
        container.style.display = 'none';
    }

    function configureSlot(element, slot) {
        if (!/^\d+$/.test(slot || '')) {
            hideSlot(element);
            return false;
        }
        element.setAttribute('data-ad-client', publisherId);
        element.setAttribute('data-ad-slot', slot);
        return true;
    }

    function requestDisplayAd(element) {
        try {
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        } catch (error) {
            console.error('Could not request display ad:', error);
            if (element.closest('#ads-modal')) {
                element.closest('#ads-modal').classList.add('hidden');
            }
        }
    }

    function setupBannerAds() {
        var banners = document.querySelectorAll('ins.adsbygoogle[data-site-ad="banner"]');
        banners.forEach(function (element) {
            if (!isEnabled('banner')) {
                hideSlot(element);
            } else if (configureSlot(element, slots.banner)) {
                requestDisplayAd(element);
            }
        });
    }

    function setupPopup() {
        var modal = document.getElementById('ads-modal');
        if (!modal) return;

        // The popup starts hidden in HTML, before any external script loads.
        var closeButton = document.getElementById('close_modal');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                modal.classList.add('hidden');
            });
        }

        var element = modal.querySelector('ins.adsbygoogle[data-site-ad="popup"]');
        if (!element || !isEnabled('popup') || !/^\d+$/.test(slots.popup || '')) return;

        var popup = config.popup || {};
        var delay = Math.max(0, Number(popup.delayMs) || 0);
        window.setTimeout(function () {
            if (!configureSlot(element, slots.popup)) return;
            if (popup.hideWhenUnfilled) {
                var observer = new MutationObserver(function () {
                    if (element.getAttribute('data-ad-status') === 'unfilled') {
                        modal.classList.add('hidden');
                        observer.disconnect();
                    }
                });
                observer.observe(element, { attributes: true, attributeFilter: ['data-ad-status'] });
            }

            modal.classList.remove('hidden');
            requestDisplayAd(element);

            // A blocked or unavailable SDK must not leave an empty popup open.
            var timeout = Number(popup.loadTimeoutMs) || 0;
            if (timeout > 0) {
                window.setTimeout(function () {
                    if (element.getAttribute('data-ad-status') !== 'filled') {
                        modal.classList.add('hidden');
                    }
                }, timeout);
            }
        }, delay);
    }

    function hasReward() {
        try {
            return window.sessionStorage.getItem(config.rewarded.sessionKey) === 'true';
        } catch (error) {
            return rewardedInMemory;
        }
    }

    function rememberReward() {
        rewardedInMemory = true;
        try {
            window.sessionStorage.setItem(config.rewarded.sessionKey, 'true');
        } catch (error) {
            // Session storage can be unavailable in private browsing.
        }
    }

    function showRewardedAd(destination) {
        var settings = config.rewarded || {};
        if (!isEnabled('rewarded') || (settings.oncePerTab && hasReward())) {
            window.location.href = destination;
            return false; // Prevent the original <a> from navigating before the ad ends.
        }
        if (rewardPending) return false;
        rewardPending = true;

        try {
            window.adBreak({
                type: 'reward',
                name: settings.placementName || 'game_started',
                beforeReward: function (showAd) { showAd(); },
                adBreakDone: function (placementInfo) {
                    rewardPending = false;
                    if (placementInfo && placementInfo.breakStatus === 'viewed') {
                        if (settings.oncePerTab) rememberReward();
                        window.location.href = destination;
                    }
                }
            });
        } catch (error) {
            rewardPending = false;
            console.error('Could not request rewarded ad:', error);
        }
        return false;
    }

    // Available for a future natural, user-initiated transition; no existing
    // page calls it, so current navigation and ad frequency are unchanged.
    function showInterstitial(onComplete) {
        var complete = typeof onComplete === 'function' ? onComplete : function () {};
        if (!isEnabled('interstitial')) {
            complete({ breakStatus: 'disabled' });
            return;
        }
        var settings = config.interstitial || {};
        try {
            window.adBreak({
                type: settings.placementType || 'next',
                name: settings.placementName || 'between_sections',
                adBreakDone: complete
            });
        } catch (error) {
            console.error('Could not request interstitial ad:', error);
            complete({ breakStatus: 'error' });
        }
    }

    window.SiteAds = {
        config: config,
        showRewardedAd: showRewardedAd,
        showInterstitial: showInterstitial
    };

    if (canLoad) {
        // Google replaces these queue shims when its library is ready.
        window.adsbygoogle = window.adsbygoogle || [];
        window.adBreak = window.adConfig = function (options) {
            window.adsbygoogle.push(options);
        };

        var tag = document.createElement('script');
        tag.async = true;
        tag.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(publisherId);
        tag.crossOrigin = 'anonymous';
        var position = config.anchor && config.anchor.position;
        if (position === 'bottom' || position === 'collapsed-bottom') {
            tag.setAttribute('data-overlays', position);
        }
        document.head.appendChild(tag);

        if (formats.rewarded || formats.interstitial) {
            window.adConfig({ preloadAdBreaks: 'on', sound: 'off' });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setupBannerAds();
            setupPopup();
        });
    } else {
        setupBannerAds();
        setupPopup();
    }
}(window, document));
