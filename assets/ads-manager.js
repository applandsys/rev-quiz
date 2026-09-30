/* Popup ad blocks and the Google H5 formats (rewarded, interstitial). Settings: REW QUIZZ > Ad blocks. */
(function (window, document) {
    'use strict';

    var config = window.SiteAdsConfig || {};
    var formats = config.formats || {};
    var publisherId = config.publisherId || '';
    var canLoad = config.enabled === true && /^ca-pub-\d+$/.test(publisherId);
    var rewardedInMemory = false;
    var rewardPending = false;

    function isEnabled(format) {
        return canLoad && formats[format] === true;
    }

    // Popup blocks keep their code in a <template> so ad scripts run only once the popup is visible.
    function runCode(template, target) {
        target.appendChild(template.content.cloneNode(true));
        target.querySelectorAll('script').forEach(function (old) {
            var script = document.createElement('script');
            Array.prototype.forEach.call(old.attributes, function (attr) {
                script.setAttribute(attr.name, attr.value);
            });
            script.text = old.text;
            old.parentNode.replaceChild(script, old);
        });
    }

    function setupPopups() {
        document.querySelectorAll('[data-rq-popup]').forEach(function (modal) {
            var body = modal.querySelector('.rq-modal-body');
            var template = modal.querySelector('template');
            var close = modal.querySelector('.rq-modal-close');
            if (close) {
                close.addEventListener('click', function () {
                    modal.classList.add('hidden');
                });
            }
            if (!body || !template) return;

            var delay = Math.max(0, Number(modal.getAttribute('data-delay')) || 0);
            var hideUnfilled = modal.getAttribute('data-hide-unfilled') === '1';
            window.setTimeout(function () {
                modal.classList.remove('hidden');
                runCode(template, body);

                var unit = body.querySelector('ins.adsbygoogle');
                if (!unit || !hideUnfilled) return;
                var observer = new MutationObserver(function () {
                    if (unit.getAttribute('data-ad-status') === 'unfilled') {
                        modal.classList.add('hidden');
                        observer.disconnect();
                    }
                });
                observer.observe(unit, { attributes: true, attributeFilter: ['data-ad-status'] });
                // A blocked or unavailable ad library must not leave an empty popup open.
                window.setTimeout(function () {
                    if (unit.getAttribute('data-ad-status') !== 'filled') {
                        modal.classList.add('hidden');
                    }
                }, 8000);
            }, delay);
        });
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
        // The AdSense library itself is loaded in <head> (REW QUIZZ > Ad blocks > General).
        // Google replaces these queue shims when its library is ready.
        window.adsbygoogle = window.adsbygoogle || [];
        window.adBreak = window.adConfig = function (options) {
            window.adsbygoogle.push(options);
        };
        if (formats.rewarded || formats.interstitial) {
            window.adConfig({ preloadAdBreaks: 'on', sound: 'off' });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupPopups);
    } else {
        setupPopups();
    }
}(window, document));
