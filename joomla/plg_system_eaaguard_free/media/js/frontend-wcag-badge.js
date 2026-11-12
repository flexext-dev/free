/**
 * Real-Time WCAG 2.2 / EAA Frontend Accessibility Badge & Panel Controller
 * Standards: WCAG 2.2 Level AA / AAA, EN 301 549 v3.2.1, EAA 2025/2026.
 */

(function () {
  'use strict';

  if (window.FlxWcagBadgeLoaded) return;
  window.FlxWcagBadgeLoaded = true;

  const cfg = window.FlxWcagConfig || {};
  const declarationUrl = cfg.declarationUrl || '/index.php?option=com_ajax&plugin=eaaguard&task=declaration&format=html';
  const position = cfg.position || 'bottom-right';

  // State keys
  const STORAGE_KEY = 'flx_wcag_preferences';

  function getStoredPrefs() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    } catch (e) {
      return {};
    }
  }

  function savePrefs(prefs) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch (e) {}
  }

  function applyPreferences(prefs) {
    document.body.classList.toggle('flx-wcag-high-contrast', !!prefs.highContrast);
    document.body.classList.toggle('flx-wcag-readable-font', !!prefs.readableFont);
    document.body.classList.toggle('flx-wcag-highlight-links', !!prefs.highlightLinks);
    document.body.classList.toggle('flx-wcag-stop-animations', !!prefs.stopAnimations);
  }

  function createDom() {
    const prefs = getStoredPrefs();
    applyPreferences(prefs);

    // 1. Launcher button
    const launcher = document.createElement('button');
    launcher.type = 'button';
    launcher.className = `flx-wcag-launcher ${position === 'bottom-left' ? 'position-left' : ''}`;
    launcher.id = 'flx-wcag-toggle-btn';
    launcher.setAttribute('aria-haspopup', 'dialog');
    launcher.setAttribute('aria-expanded', 'false');
    launcher.setAttribute('aria-label', 'Deklaracja Dostępności i Narzędzia WCAG 2.2 / EAA');
    launcher.innerHTML = `
      <div class="flx-wcag-icon-wrap" aria-hidden="true">
        <svg viewBox="0 0 24 24">
          <path d="M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/>
        </svg>
      </div>
      <div class="flx-wcag-launcher-text">
        <span class="flx-wcag-badge-title">Dostępność Cyfrowa</span>
        <span class="flx-wcag-badge-subtitle">WCAG 2.2 AA / EAA</span>
      </div>
    `;

    // 2. Backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'flx-wcag-modal-backdrop';
    backdrop.id = 'flx-wcag-backdrop';

    // 3. Panel Dialog
    const panel = document.createElement('div');
    panel.className = `flx-wcag-panel ${position === 'bottom-left' ? 'position-left' : ''}`;
    panel.id = 'flx-wcag-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.setAttribute('aria-labelledby', 'flx-wcag-title');

    panel.innerHTML = `
      <div class="flx-wcag-header">
        <div class="flx-wcag-header-info">
          <div class="flx-wcag-icon-wrap" aria-hidden="true" style="width:28px;height:28px;">
            <svg viewBox="0 0 24 24"><path d="M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/></svg>
          </div>
          <div>
            <h3 id="flx-wcag-title">Dostępność Cyfrowa & EAA</h3>
            <p>Monitorowanie WCAG 2.2 AA w czasie rzeczywistym</p>
          </div>
        </div>
        <button type="button" class="flx-wcag-close-btn" id="flx-wcag-close" aria-label="Zamknij panel dostępności">&times;</button>
      </div>

      <div class="flx-wcag-body">
        <div class="flx-wcag-status-card" role="status">
          <div class="flx-wcag-status-icon" aria-hidden="true">🛡️</div>
          <div>
            <div class="flx-wcag-status-title">Strażnik Dostępności Aktywny</div>
            <div class="flx-wcag-status-desc">Serwis jest chroniony przed publikacją niedostępnych treści (Pre-Publish Gatekeeper) oraz audytowany pod kątem Europejskiego Aktu o Dostępności.</div>
          </div>
        </div>

        <a href="${declarationUrl}" class="flx-wcag-btn-declaration" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
          </svg>
          <span>Oficjalna Deklaracja Dostępności</span>
        </a>

        <div class="flx-wcag-section-title">Ułatwienia Dostępności (WCAG AA)</div>
        <div class="flx-wcag-tools-grid">
          <button type="button" class="flx-wcag-tool-btn ${prefs.highContrast ? 'active' : ''}" id="flx-tool-contrast" aria-pressed="${!!prefs.highContrast}">
            <span>🌓</span>
            <span>Wysoki Kontrast</span>
          </button>
          <button type="button" class="flx-wcag-tool-btn ${prefs.readableFont ? 'active' : ''}" id="flx-tool-font" aria-pressed="${!!prefs.readableFont}">
            <span>🔤</span>
            <span>Czytelna Czcionka</span>
          </button>
          <button type="button" class="flx-wcag-tool-btn ${prefs.highlightLinks ? 'active' : ''}" id="flx-tool-links" aria-pressed="${!!prefs.highlightLinks}">
            <span>🔗</span>
            <span>Podkreśl Linki</span>
          </button>
          <button type="button" class="flx-wcag-tool-btn ${prefs.stopAnimations ? 'active' : ''}" id="flx-tool-anim" aria-pressed="${!!prefs.stopAnimations}">
            <span>⏸️</span>
            <span>Zatrzymaj Ruch</span>
          </button>
        </div>

        <div style="margin-top: 14px;">
          <button type="button" class="flx-wcag-btn-declaration" style="background:#0f172a; color:#ffffff; width:100%; border:none; cursor:pointer;" id="flx-tool-jooa11y" title="Uruchom wbudowany wizualny inspektor Jooa11y (Sa11y)">
            <span>✨</span>
            <span>Uruchom Inspektor Jooa11y (Sa11y)</span>
          </button>
        </div>
      </div>

      <div class="flx-wcag-footer">
        <span>Zgodność z EN 301 549 & EAA</span>
        <button type="button" class="flx-wcag-btn-reset" id="flx-tool-reset">Resetuj ułatwienia</button>
      </div>
    `;

    document.body.appendChild(launcher);
    document.body.appendChild(backdrop);
    document.body.appendChild(panel);

    // Event handlers
    function openPanel() {
      panel.classList.add('active');
      backdrop.classList.add('active');
      launcher.setAttribute('aria-expanded', 'true');
      panel.querySelector('.flx-wcag-close-btn').focus();
    }

    function closePanel() {
      panel.classList.remove('active');
      backdrop.classList.remove('active');
      launcher.setAttribute('aria-expanded', 'false');
      launcher.focus();
    }

    launcher.addEventListener('click', () => {
      if (panel.classList.contains('active')) {
        closePanel();
      } else {
        openPanel();
      }
    });

    backdrop.addEventListener('click', closePanel);
    panel.querySelector('#flx-wcag-close').addEventListener('click', closePanel);

    // Keyboard support: Escape closes dialog
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && panel.classList.contains('active')) {
        closePanel();
      }
    });

    // Tool toggles
    function setupToolToggle(buttonId, prefKey) {
      const btn = panel.querySelector(`#${buttonId}`);
      if (!btn) return;
      btn.addEventListener('click', () => {
        const curPrefs = getStoredPrefs();
        curPrefs[prefKey] = !curPrefs[prefKey];
        savePrefs(curPrefs);
        btn.classList.toggle('active', !!curPrefs[prefKey]);
        btn.setAttribute('aria-pressed', curPrefs[prefKey] ? 'true' : 'false');
        applyPreferences(curPrefs);
      });
    }

    setupToolToggle('flx-tool-contrast', 'highContrast');
    setupToolToggle('flx-tool-font', 'readableFont');
    setupToolToggle('flx-tool-links', 'highlightLinks');
    setupToolToggle('flx-tool-anim', 'stopAnimations');

    // Jooa11y trigger
    const btnJooa11y = panel.querySelector('#flx-tool-jooa11y');
    if (btnJooa11y) {
      btnJooa11y.addEventListener('click', () => {
        try {
          const curUrl = new URL(window.location.href);
          curUrl.searchParams.set('jooa11y', '1');
          window.location.href = curUrl.toString();
        } catch (e) {
          const sep = window.location.href.indexOf('?') !== -1 ? '&' : '?';
          window.location.href = window.location.href + sep + 'jooa11y=1';
        }
      });
    }

    // Reset button
    panel.querySelector('#flx-tool-reset').addEventListener('click', () => {
      savePrefs({});
      applyPreferences({});
      panel.querySelectorAll('.flx-wcag-tool-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.setAttribute('aria-pressed', 'false');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createDom);
  } else {
    createDom();
  }
})();
