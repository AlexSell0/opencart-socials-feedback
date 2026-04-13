/**
 * Module: Socials Feedback
 * Developer: AlexS
 * Email: alexsell72@gmail.com
 * GitHub: https://github.com/AlexSell0/opencart-socials-feedback
 * Telegram: https://t.me/AlexS735
 *
 * @copyright  (c) 2024, AlexS
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package Extension
 * @version 1.0.0
 */
(function () {
    'use strict';

    const BLOCK_SEL = '#sfLinks';
    const LAYER_SEL = '#sfOverlay';

    function fadeIn(el, cb) {
        el.style.opacity = 0;
        el.style.display = 'block';
        let start = null;
        (function step(ts) {
            if (!start) start = ts;
            el.style.opacity = Math.min((ts - start) / 10, 1);
            (ts - start) < 10 ? requestAnimationFrame(step) : cb && cb();
        })(performance.now());
    }

    function fadeOut(el, cb) {
        let start = null;
        (function step(ts) {
            if (!start) start = ts;
            el.style.opacity = 1 - Math.min((ts - start) / 10, 1);
            if ((ts - start) < 10) {
                requestAnimationFrame(step);
            } else {
                el.style.display = 'none';
                cb && cb();
            }
        })(performance.now());
    }

    function createLayer() {
        const layer = document.createElement('div');
        layer.className = 'social-feedback__vw-layer-dark';
        layer.setAttribute('data-close-trigger', '');
        document.body.prepend(layer);
        return layer;
    }

    function open(block) {
        const layer = createLayer();
        fadeIn(layer);
        block.classList.remove('close');
        block.classList.add('open');
        fadeIn(block);
    }

    function close(block) {
        const layer = document.querySelector(LAYER_SEL);
        fadeOut(block, function () {
            block.classList.remove('open');
            block.classList.add('close');
        });
        if (layer) fadeOut(layer, () => layer.remove());
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.open__callme a');
        const block = document.querySelector(BLOCK_SEL);
        if (!toggle || !block) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            block.classList.contains('close') ? open(block) : close(block);
        });

        document.addEventListener('click', function (e) {
            if (e.target.hasAttribute('data-close-trigger')) {
                close(block);
            }
        });
    });
})();
