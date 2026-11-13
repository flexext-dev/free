/**
 * FlexExt WCAG Free Front-end Accessibility Toolbar Script
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('flx-wcag-toggle-btn');
        const panel = document.getElementById('flx-wcag-panel');
        const closeBtn = document.getElementById('flx-wcag-close-btn');

        if (!toggleBtn || !panel) return;

        // Load saved state from localStorage
        const state = JSON.parse(localStorage.getItem('flx_wcag_state') || '{}');
        applyState(state);

        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = panel.style.display === 'block';
            panel.style.display = isOpen ? 'none' : 'block';
            toggleBtn.setAttribute('aria-expanded', !isOpen);
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                panel.style.display = 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
            });
        }

        document.addEventListener('click', function(e) {
            if (!panel.contains(e.target) && e.target !== toggleBtn) {
                panel.style.display = 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });

        // Action buttons
        const actionBtns = panel.querySelectorAll('.flx-wcag-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.dataset.action;
                handleAction(action);
            });
        });

        function handleAction(action) {
            const current = JSON.parse(localStorage.getItem('flx_wcag_state') || '{}');

            if (action === 'font-normal') {
                current.fontSize = 'normal';
            } else if (action === 'font-large') {
                current.fontSize = 'large';
            } else if (action === 'font-xlarge') {
                current.fontSize = 'xlarge';
            } else if (action === 'contrast') {
                current.highContrast = !current.highContrast;
            } else if (action === 'links') {
                current.highlightLinks = !current.highlightLinks;
            } else if (action === 'reset') {
                localStorage.removeItem('flx_wcag_state');
                applyState({});
                return;
            }

            localStorage.setItem('flx_wcag_state', JSON.stringify(current));
            applyState(current);
        }

        function applyState(st) {
            document.body.classList.remove('flx-wcag-font-large', 'flx-wcag-font-xlarge');
            if (st.fontSize === 'large') {
                document.body.classList.add('flx-wcag-font-large');
            } else if (st.fontSize === 'xlarge') {
                document.body.classList.add('flx-wcag-font-xlarge');
            }

            if (st.highContrast) {
                document.body.classList.add('flx-wcag-high-contrast');
            } else {
                document.body.classList.remove('flx-wcag-high-contrast');
            }

            if (st.highlightLinks) {
                document.body.classList.add('flx-wcag-highlight-links');
            } else {
                document.body.classList.remove('flx-wcag-highlight-links');
            }
        }
    });
})();
