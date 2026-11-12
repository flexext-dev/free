/**
 * Frontend Accessibility Badge & Assistive Tools Widget (WordPress)
 * Full Keyboard Navigation, WCAG 2.2 AA / AAA Compliance
 *
 * @package     wp-eaaguard
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     Commercial Proprietary
 */

(function () {
    'use strict';

    const config = window.EaaBadgeConfig || {
        lang: 'pl',
        position: 'bottom-right',
        declaration: '/deklaracja-dostepnosci/',
        i18n: {}
    };

    const state = {
        highContrast: localStorage.getItem('eaaguard_high_contrast') === '1',
        readableFont: localStorage.getItem('eaaguard_readable_font') === '1',
        highlightLinks: localStorage.getItem('eaaguard_highlight_links') === '1',
        stopMotion: localStorage.getItem('eaaguard_stop_motion') === '1',
        textSize: parseInt(localStorage.getItem('eaaguard_text_size') || '0', 10),
        isOpen: false
    };

    // Apply stored preferences on boot
    function applyPreferences() {
        document.documentElement.classList.toggle('eaaguard-high-contrast', state.highContrast);
        document.documentElement.classList.toggle('eaaguard-readable-font', state.readableFont);
        document.documentElement.classList.toggle('eaaguard-highlight-links', state.highlightLinks);
        document.documentElement.classList.toggle('eaaguard-stop-motion', state.stopMotion);

        document.documentElement.classList.remove('eaaguard-text-lg', 'eaaguard-text-xl');
        if (state.textSize === 1) document.documentElement.classList.add('eaaguard-text-lg');
        if (state.textSize === 2) document.documentElement.classList.add('eaaguard-text-xl');
    }

    applyPreferences();

    function adjustBadgePosition() {
        const container = document.getElementById('eaaguard-widget-container');
        if (!container) return;
        const demoBadge = document.getElementById('fext-demo-floating-badge');
        if (demoBadge && (container.classList.contains('eaaguard-pos-left') || window.getComputedStyle(container).left !== 'auto')) {
            const rect = demoBadge.getBoundingClientRect();
            if (rect && rect.height > 0) {
                const targetBottom = Math.max(175, Math.round(window.innerHeight - rect.top + 16));
                container.style.bottom = targetBottom + 'px';
                container.style.zIndex = '1000005';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const trigger = document.getElementById('eaaguard-badge-trigger');
        if (!trigger) return;

        createModal();
        adjustBadgePosition();

        trigger.addEventListener('click', () => {
            toggleModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && state.isOpen) {
                closeModal();
            }
        });
    });

    window.addEventListener('load', adjustBadgePosition);
    window.addEventListener('resize', adjustBadgePosition);
    setTimeout(adjustBadgePosition, 400);
    setTimeout(adjustBadgePosition, 1200);

    function createModal() {
        if (document.getElementById('eaaguard-frontend-modal')) return;

        const modal = document.createElement('div');
        modal.id = 'eaaguard-frontend-modal';
        modal.className = 'eaaguard-fmodal-backdrop';
        modal.style.display = 'none';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'eaaguard-fmodal-title');

        const i18n = config.i18n;

        modal.innerHTML = `
            <div class="eaaguard-fmodal-dialog">
                <div class="eaaguard-fmodal-header">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:22px;">♿</span>
                        <h2 id="eaaguard-fmodal-title" style="margin:0;font-size:16px;font-weight:700;">${i18n.modalTitle || 'Panel Dostępności'}</h2>
                    </div>
                    <button type="button" class="eaaguard-fmodal-close" id="eaaguard-fmodal-close-btn" aria-label="${i18n.btnClose || 'Zamknij'}">&times;</button>
                </div>
                <div class="eaaguard-fmodal-body">
                    <div class="eaaguard-fmodal-status">
                        ${i18n.statusCompliant || '🟢 Zgodny z WCAG 2.2 AA / EAA'}
                    </div>

                    <div class="eaaguard-tools-list">
                        <button type="button" class="eaaguard-tool-btn ${state.highContrast ? 'active' : ''}" id="eaaguard-tool-contrast">
                            <span class="eaaguard-tool-icon">🔲</span>
                            <span>${i18n.toolContrast || 'Wysoki Kontrast'}</span>
                            <span class="eaaguard-toggle-indicator">${state.highContrast ? 'WŁ' : 'WYŁ'}</span>
                        </button>

                        <button type="button" class="eaaguard-tool-btn ${state.readableFont ? 'active' : ''}" id="eaaguard-tool-font">
                            <span class="eaaguard-tool-icon">🔤</span>
                            <span>${i18n.toolFont || 'Czytelna Czcionka'}</span>
                            <span class="eaaguard-toggle-indicator">${state.readableFont ? 'WŁ' : 'WYŁ'}</span>
                        </button>

                        <button type="button" class="eaaguard-tool-btn ${state.highlightLinks ? 'active' : ''}" id="eaaguard-tool-links">
                            <span class="eaaguard-tool-icon">🔗</span>
                            <span>${i18n.toolLinks || 'Wyróżnij Linki'}</span>
                            <span class="eaaguard-toggle-indicator">${state.highlightLinks ? 'WŁ' : 'WYŁ'}</span>
                        </button>

                        <button type="button" class="eaaguard-tool-btn ${state.stopMotion ? 'active' : ''}" id="eaaguard-tool-motion">
                            <span class="eaaguard-tool-icon">⏸️</span>
                            <span>${i18n.toolMotion || 'Zatrzymaj Animacje'}</span>
                            <span class="eaaguard-toggle-indicator">${state.stopMotion ? 'WŁ' : 'WYŁ'}</span>
                        </button>

                        <div class="eaaguard-size-control">
                            <span class="eaaguard-tool-icon">🔍</span>
                            <span>${i18n.toolTextSize || 'Rozmiar Tekstu:'}</span>
                            <div class="eaaguard-size-buttons">
                                <button type="button" class="eaaguard-size-btn ${state.textSize === 0 ? 'active' : ''}" data-size="0">100%</button>
                                <button type="button" class="eaaguard-size-btn ${state.textSize === 1 ? 'active' : ''}" data-size="1">120%</button>
                                <button type="button" class="eaaguard-size-btn ${state.textSize === 2 ? 'active' : ''}" data-size="2">140%</button>
                            </div>
                        </div>

                        <button type="button" class="eaaguard-tool-btn eaaguard-tool-inspector" id="eaaguard-tool-inspector" style="background:#2563eb!important;color:#ffffff!important;font-weight:700;border:1px solid #1d4ed8!important;margin-top:8px;">
                            <span class="eaaguard-tool-icon">🔍</span>
                            <span style="color:#ffffff!important;">${i18n.toolInspector || 'Wizualna Diagnostyka WCAG (Inspektor)'}</span>
                            <span class="eaaguard-toggle-indicator" id="eaaguard-inspector-ind" style="background:#ffffff;color:#2563eb;font-weight:800;">START</span>
                        </button>
                    </div>

                    <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                        <a href="${config.declaration}" class="eaaguard-fmodal-link-btn" target="_blank">
                            ${i18n.btnDeclaration || '📜 Zobacz Deklarację Dostępności'}
                        </a>
                        <button type="button" class="eaaguard-fmodal-reset-btn" id="eaaguard-tool-reset">
                            ${i18n.btnReset || 'Resetuj Ustawienia'}
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        document.getElementById('eaaguard-fmodal-close-btn').addEventListener('click', closeModal);

        // Visual Inspector button
        document.getElementById('eaaguard-tool-inspector').addEventListener('click', function () {
            runVisualDiagnostics();
        });

        // Tool buttons
        document.getElementById('eaaguard-tool-contrast').addEventListener('click', function () {
            state.highContrast = !state.highContrast;
            localStorage.setItem('eaaguard_high_contrast', state.highContrast ? '1' : '0');
            applyPreferences();
            updateToolBtn(this, state.highContrast);
        });

        document.getElementById('eaaguard-tool-font').addEventListener('click', function () {
            state.readableFont = !state.readableFont;
            localStorage.setItem('eaaguard_readable_font', state.readableFont ? '1' : '0');
            applyPreferences();
            updateToolBtn(this, state.readableFont);
        });

        document.getElementById('eaaguard-tool-links').addEventListener('click', function () {
            state.highlightLinks = !state.highlightLinks;
            localStorage.setItem('eaaguard_highlight_links', state.highlightLinks ? '1' : '0');
            applyPreferences();
            updateToolBtn(this, state.highlightLinks);
        });

        document.getElementById('eaaguard-tool-motion').addEventListener('click', function () {
            state.stopMotion = !state.stopMotion;
            localStorage.setItem('eaaguard_stop_motion', state.stopMotion ? '1' : '0');
            applyPreferences();
            updateToolBtn(this, state.stopMotion);
        });

        // Size buttons
        modal.querySelectorAll('.eaaguard-size-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const sz = parseInt(this.getAttribute('data-size'), 10);
                state.textSize = sz;
                localStorage.setItem('eaaguard_text_size', sz.toString());
                applyPreferences();

                modal.querySelectorAll('.eaaguard-size-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Reset
        document.getElementById('eaaguard-tool-reset').addEventListener('click', () => {
            state.highContrast = false;
            state.readableFont = false;
            state.highlightLinks = false;
            state.stopMotion = false;
            state.textSize = 0;

            localStorage.removeItem('eaaguard_high_contrast');
            localStorage.removeItem('eaaguard_readable_font');
            localStorage.removeItem('eaaguard_highlight_links');
            localStorage.removeItem('eaaguard_stop_motion');
            localStorage.removeItem('eaaguard_text_size');

            applyPreferences();

            modal.querySelectorAll('.eaaguard-tool-btn').forEach(b => {
                b.classList.remove('active');
                const ind = b.querySelector('.eaaguard-toggle-indicator');
                if (ind && b.id !== 'eaaguard-tool-inspector') ind.textContent = 'WYŁ';
            });
            modal.querySelectorAll('.eaaguard-size-btn').forEach(b => b.classList.remove('active'));
            modal.querySelector('.eaaguard-size-btn[data-size="0"]').classList.add('active');
        });
    }

    // In-page Visual DOM Diagnostics Engine
    let inspectedElements = [];
    let currentIssueIndex = -1;

    function runVisualDiagnostics() {
        closeModal();
        stopVisualDiagnostics();

        inspectedElements = [];
        let critical = 0;
        let warnings = 0;
        const isPl = (config.lang === 'pl');

        // 1. Audit Images (WCAG 1.1.1)
        const imgs = document.querySelectorAll('img:not(#eaaguard-widget-container img):not(.eaaguard-fmodal-dialog img)');
        imgs.forEach((img) => {
            const alt = img.getAttribute('alt');
            const role = img.getAttribute('role');
            if (role === 'presentation' || role === 'none') return;
            if (alt === null || alt.trim() === '') {
                critical++;
                annotateElement(img, 'critical', 'WCAG 1.1.1', isPl ? 'Brak opisu alternatywnego (alt)' : 'Missing image alt text');
            }
        });

        // 2. Audit Headings (WCAG 1.3.1)
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        let lastLevel = 0;
        headings.forEach((h) => {
            const level = parseInt(h.tagName.substring(1), 10);
            if (lastLevel > 0 && level > lastLevel + 1) {
                critical++;
                annotateElement(h, 'critical', 'WCAG 1.3.1', isPl ? `Pominięto poziom nagłówka: H${level} zaraz po H${lastLevel}` : `Skipped heading level: H${level} after H${lastLevel}`);
            }
            lastLevel = level;
        });

        // 3. Audit Links (WCAG 2.4.4)
        const links = document.querySelectorAll('a:not(#eaaguard-widget-container a):not(.eaaguard-fmodal-dialog a)');
        const genericTexts = ['kliknij tutaj', 'tutaj', 'więcej', 'czytaj więcej', 'zobacz więcej', 'link', 'click here', 'more', 'read more', 'download'];
        links.forEach((a) => {
            const txt = (a.textContent || '').trim().toLowerCase();
            const aria = a.getAttribute('aria-label') || '';
            const title = a.getAttribute('title') || '';
            const hasImg = a.querySelector('img') !== null;

            if (!txt && !aria && !title && !hasImg) {
                critical++;
                annotateElement(a, 'critical', 'WCAG 2.4.4', isPl ? 'Pusty link bez tekstu' : 'Empty hyperlink without accessible text');
            } else if (genericTexts.includes(txt) && !aria && !title) {
                warnings++;
                annotateElement(a, 'warning', 'WCAG 2.4.4', isPl ? `Niejednoznaczna treść odnośnika ("${txt}")` : `Ambiguous link text ("${txt}")`);
            }
        });

        // 4. Audit Form Inputs (WCAG 1.3.1, 4.1.2)
        const inputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not(#eaaguard-widget-container input), select, textarea');
        inputs.forEach((input) => {
            const id = input.id;
            const ariaLabel = input.getAttribute('aria-label');
            const ariaLabelledBy = input.getAttribute('aria-labelledby');
            let hasLabel = false;

            if (ariaLabel || ariaLabelledBy) {
                hasLabel = true;
            } else if (id) {
                const label = document.querySelector(`label[for="${id}"]`);
                if (label && label.textContent.trim()) hasLabel = true;
            }
            if (!hasLabel && input.closest('label')) {
                hasLabel = true;
            }

            if (!hasLabel) {
                critical++;
                annotateElement(input, 'critical', 'WCAG 1.3.1', isPl ? 'Pole formularza nie posiada powiązanej etykiety <label>' : 'Form control missing label');
            }
        });

        // 5. Audit White on White / Low Contrast Elements (WCAG 1.4.3)
        const textElements = document.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6, li, td, th');
        textElements.forEach((el) => {
            if (el.closest('#eaaguard-widget-container') || el.closest('#eaaguard-diag-bar') || el.closest('.eaaguard-diag-pin')) return;
            const style = (el.getAttribute('style') || '').toLowerCase();
            const cl = (el.getAttribute('class') || '').toLowerCase();
            if (cl.includes('has-white-color') || style.includes('#ffffff') || style.includes('white')) {
                if (style.includes('color') && (style.includes('background') || cl.includes('has-white-background-color'))) {
                    if ((style.includes('color: #ffffff') || style.includes('color:#ffffff') || cl.includes('has-white-color')) &&
                        (style.includes('background-color: #ffffff') || style.includes('background:#ffffff') || cl.includes('has-white-background-color'))) {
                        critical++;
                        annotateElement(el, 'critical', 'WCAG 1.4.3', isPl ? 'Biały tekst na białym tle (kontrast 1.00:1)' : 'White text on white background (1.00:1 contrast)');
                    }
                }
            }
        });

        renderDiagnosticBar(critical, warnings);

        if (inspectedElements.length > 0) {
            currentIssueIndex = 0;
            inspectedElements[0].el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function getDiagOverlay() {
        let overlay = document.getElementById('eaaguard-diag-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'eaaguard-diag-overlay';
            overlay.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:99999;overflow:visible;';
            document.body.appendChild(overlay);
        }
        return overlay;
    }

    function updatePinPositions() {
        inspectedElements.forEach(({ el, pin }) => {
            if (!el || !pin || !el.getBoundingClientRect) return;
            const rect = el.getBoundingClientRect();
            const top = rect.top + window.scrollY;
            const left = rect.left + window.scrollX;
            pin.style.top = Math.max(0, top - 24) + 'px';
            pin.style.left = Math.max(0, left) + 'px';
        });
    }

    function annotateElement(el, severity, criterion, message) {
        const isCrit = (severity === 'critical');
        el.classList.add(isCrit ? 'eaaguard-diag-violation' : 'eaaguard-diag-warning');

        const pin = document.createElement('div');
        pin.className = 'eaaguard-diag-pin' + (isCrit ? '' : ' eaaguard-diag-pin-warn');
        pin.innerHTML = `⚠️ <strong>${criterion}:</strong> ${message}`;
        pin.title = `${criterion}: ${message}`;

        pin.addEventListener('click', (e) => {
            e.stopPropagation();
            alert(`[${criterion}] ${message}`);
        });

        // Position pin in floating overlay so page DOM layout (flex, grid, inline) is completely undisturbed
        const rect = el.getBoundingClientRect();
        const top = rect.top + window.scrollY;
        const left = rect.left + window.scrollX;
        pin.style.position = 'absolute';
        pin.style.top = Math.max(0, top - 24) + 'px';
        pin.style.left = Math.max(0, left) + 'px';
        pin.style.pointerEvents = 'auto';

        getDiagOverlay().appendChild(pin);

        inspectedElements.push({ el, pin, message });
    }

    function renderDiagnosticBar(critCount, warnCount) {
        let bar = document.getElementById('eaaguard-diag-bar');
        if (bar) bar.remove();

        const i18n = config.i18n;

        bar = document.createElement('div');
        bar.id = 'eaaguard-diag-bar';
        bar.innerHTML = `
            <div class="eaaguard-diag-bar-left">
                <span class="eaaguard-diag-bar-title">🔍 ${i18n.barTitle || 'Inspektor WCAG 2.2'}</span>
                <span class="eaaguard-diag-bar-badge" style="background:#dc2626;color:#ffffff;">
                    🔴 ${critCount} ${i18n.barErrors || 'Błędów'}
                </span>
                <span class="eaaguard-diag-bar-badge" style="background:#d97706;color:#ffffff;">
                    🟡 ${warnCount} ${i18n.barWarnings || 'Ostrzeżeń'}
                </span>
            </div>
            <div class="eaaguard-diag-bar-actions">
                ${inspectedElements.length > 0 ? `<button type="button" class="eaaguard-diag-btn eaaguard-diag-btn-primary" id="eaaguard-diag-next-btn">⏭️ ${i18n.barNext || 'Następny problem'}</button>` : ''}
                <button type="button" class="eaaguard-diag-btn eaaguard-diag-btn-close" id="eaaguard-diag-close-btn">❌ ${i18n.barClose || 'Zamknij'}</button>
            </div>
        `;

        document.body.appendChild(bar);

        const nextBtn = document.getElementById('eaaguard-diag-next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (inspectedElements.length === 0) return;
                currentIssueIndex = (currentIssueIndex + 1) % inspectedElements.length;
                inspectedElements[currentIssueIndex].el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        document.getElementById('eaaguard-diag-close-btn').addEventListener('click', () => {
            stopVisualDiagnostics();
        });

        window.removeEventListener('resize', updatePinPositions);
        window.removeEventListener('scroll', updatePinPositions);
        window.addEventListener('resize', updatePinPositions);
        window.addEventListener('scroll', updatePinPositions);
    }

    function stopVisualDiagnostics() {
        inspectedElements.forEach(({ el }) => {
            el.classList.remove('eaaguard-diag-violation', 'eaaguard-diag-warning');
        });
        const overlay = document.getElementById('eaaguard-diag-overlay');
        if (overlay) overlay.remove();

        window.removeEventListener('resize', updatePinPositions);
        window.removeEventListener('scroll', updatePinPositions);

        inspectedElements = [];
        currentIssueIndex = -1;

        const bar = document.getElementById('eaaguard-diag-bar');
        if (bar) bar.remove();
    }

    function updateToolBtn(btn, isActive) {
        btn.classList.toggle('active', isActive);
        const ind = btn.querySelector('.eaaguard-toggle-indicator');
        if (ind) ind.textContent = isActive ? 'WŁ' : 'WYŁ';
    }

    function toggleModal() {
        if (state.isOpen) closeModal();
        else openModal();
    }

    function openModal() {
        const modal = document.getElementById('eaaguard-frontend-modal');
        const trigger = document.getElementById('eaaguard-badge-trigger');
        if (!modal) return;

        modal.style.display = 'flex';
        state.isOpen = true;
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
        document.getElementById('eaaguard-fmodal-close-btn').focus();
    }

    function closeModal() {
        const modal = document.getElementById('eaaguard-frontend-modal');
        const trigger = document.getElementById('eaaguard-badge-trigger');
        if (!modal) return;

        modal.style.display = 'none';
        state.isOpen = false;
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
        }
    }

    // Auto-launch inspector if ?eaaguard_inspector=1 is in URL
    if (window.location.search && window.location.search.includes('eaaguard_inspector=1')) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => setTimeout(runVisualDiagnostics, 600));
        } else {
            setTimeout(runVisualDiagnostics, 600);
        }
    }
})();
