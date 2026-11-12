/**
 * Real-Time WCAG 2.2 / EAA Editor Guard & 1-Click Auto-Fix for WordPress (Gutenberg & Classic Editor)
 * Pre-Publish Gatekeeper adhering to European Accessibility Act (EAA) & WCAG 2.2 AA
 *
 * @package     wp-eaaguard
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     Commercial Proprietary
 */

(function () {
    'use strict';

    window.EaaGuard = {
        config: window.EaaGuardConfig || {
            lang: 'en',
            strictMode: true,
            isLicensed: true,
            ajaxUrl: '/wp-admin/admin-ajax.php',
            nonce: ''
        },
        lang: 'en',
        violations: [],
        criticalCount: 0,
        warningCount: 0,
        isBlocked: false,
        isGutenberg: false,
        editorInstance: null,
        currentView: 'report',
        activePreviewTab: 'visual',
        lastBackupContent: null,
        pendingRepairedHtml: null,
        pendingFixes: [],
        auditDebounceTimer: null,

        // Bilingual Dictionary
        dict: {
            en: {
                badgePass: 'WCAG 2.2: Compliant',
                badgeBlocked: 'Publication Blocked',
                badgeErrors: 'Violations Found',
                badgeWarning: 'WCAG Warnings',
                modalTitle: 'WCAG 2.2 / EAA Accessibility Guard',
                modalTitlePreview: 'WCAG 2.2 / EAA Auto-Fix Remediation Preview',
                alertBlockedTitle: '⛔ Post publication is physically blocked!',
                alertBlockedDesc: 'Resolve the critical accessibility errors below or click "⚡ Auto-Fix (Preview)" to generate safe, compliant corrections with live preview.',
                alertWarnTitle: '⚠️ Accessibility recommendations detected:',
                alertWarnDesc: 'Review the recommendations below or click "⚡ Auto-Fix (Preview)" to resolve them automatically.',
                passCongratulations: 'Congratulations! No accessibility violations detected.',
                passDescription: 'Post content fully complies with WCAG 2.2 AA guidelines and the European Accessibility Act (EAA). Publication is allowed.',
                passStatusAllowed: 'Status: Allowed to publish',
                badgeCritical: 'CRITICAL ERROR',
                badgeWarningText: 'WARNING',
                lblProblem: 'Problem:',
                lblHowToFix: '💡 How to fix:',
                summaryFound: 'Found: <strong>{critical} critical errors</strong>, <strong>{warning} warnings</strong>.',
                btnAutoFix: '⚡ Auto-Fix (Preview)',
                btnUndo: '↩️ Undo Fix',
                btnUndoInline: '↩️ Undo Last Auto-Fix',
                btnRecheck: 'Re-Check Content',
                btnClose: 'Close',
                btnDiscard: '❌ Discard Changes (Do Not Save)',
                btnApply: '💾 Apply Fixes to Editor',
                previewBannerTitle: 'Generated automatic repairs: {count} accessibility improvements',
                previewBannerDesc: 'Inspect the live rendered visual layout or compare the HTML source diff before applying changes. Changes will <strong>NOT be saved to the editor</strong> without your explicit confirmation.',
                previewListHeading: 'Itemized breakdown of applied fixes ({count}):',
                previewTabVisual: '👁️ Live Visual Preview',
                previewTabDiff: '📝 Source Code Diff (Before / After)',
                diffBeforeHead: '❌ Source Code Before Fix (With Violations)',
                diffAfterHead: '✅ Source Code After Fix (WCAG 2.2 AA Compliant)',
                previewNotice: 'Clicking <strong>"Discard Changes"</strong> dismisses preview leaving your editor content 100% untouched. Clicking <strong>"Apply Fixes"</strong> updates the editor and unblocks publishing.',
                toastApplied: '🎉 Remediation applied to editor! Content is now compliant with WCAG 2.2 AA.',
                toastDiscarded: 'ℹ️ Changes discarded. Editor content remains completely untouched.',
                toastUndo: '↩️ Auto-fix reverted. Previous editor content has been restored.'
            },
            pl: {
                badgePass: 'WCAG 2.2: Zgodny',
                badgeBlocked: 'Publikacja Zablokowana',
                badgeErrors: 'Wykryto Błędy',
                badgeWarning: 'Ostrzeżenia WCAG',
                modalTitle: 'Strażnik Dostępności WCAG 2.2 / EAA',
                modalTitlePreview: 'Podgląd Automatycznej Naprawy WCAG 2.2 / EAA',
                alertBlockedTitle: '⛔ Publikacja wpisu została fizycznie zablokowana!',
                alertBlockedDesc: 'Usuń poniższe błędy krytyczne lub użyj przycisku „⚡ Automatyczna naprawa (Podgląd)”, aby wygenerować bezpieczne poprawki z podglądem.',
                alertWarnTitle: '⚠️ Wykryto zalecenia dostępności:',
                alertWarnDesc: 'Przejrzyj poniższe zalecenia lub użyj „⚡ Automatyczna naprawa (Podgląd)”, aby naprawić je automatycznie.',
                passCongratulations: 'Gratulacje! Brak wykrytych naruszeń dostępności.',
                passDescription: 'Treść wpisu jest w pełni zgodna z wytycznymi WCAG 2.2 AA oraz Europejskim Aktem o Dostępności (EAA). Publikacja jest dozwolona.',
                passStatusAllowed: 'Status: Zezwolono na publikację',
                badgeCritical: 'BŁĄD KRYTYCZNY',
                badgeWarningText: 'OSTRZEŻENIE',
                lblProblem: 'Problem:',
                lblHowToFix: '💡 Jak naprawić:',
                summaryFound: 'Znaleziono: <strong>{critical} błędów krytycznych</strong>, <strong>{warning} ostrzeżeń</strong>.',
                btnAutoFix: '⚡ Automatyczna naprawa (Podgląd)',
                btnUndo: '↩️ Cofnij naprawę',
                btnUndoInline: '↩️ Cofnij ostatnią naprawę',
                btnRecheck: 'Sprawdź ponownie',
                btnClose: 'Zamknij',
                btnDiscard: '❌ Odrzuć zmiany (Nie zapisuj)',
                btnApply: '💾 Zastosuj Naprawy do Edytora',
                previewBannerTitle: 'Wygenerowano automatyczne naprawy: {count} ulepszeń dostępności',
                previewBannerDesc: 'Poniżej możesz sprawdzić jak treść będzie wyglądać po naprawie oraz porównać kod HTML. Zmiany <strong>NIE zostaną zapisane w edytorze</strong> bez Twojej wyraźnej decyzji.',
                previewListHeading: 'Wykaz wprowadzonych napraw ({count}):',
                previewTabVisual: '👁️ Podgląd Wizualny',
                previewTabDiff: '📝 Porównanie Kodu (Przed / Po)',
                diffBeforeHead: '❌ Kod Przed Naprawą (Z Błędami)',
                diffAfterHead: '✅ Kod Po Naprawie (Zgodny z WCAG 2.2 AA)',
                previewNotice: 'Kliknięcie <strong>„Odrzuć zmiany”</strong> zamknie podgląd i pozostawi treść w edytorze bez zmian. Kliknięcie <strong>„Zastosuj Naprawy”</strong> zaktualizuje edytor i odblokuje publikację.',
                toastApplied: '🎉 Naprawy zostały zastosowane! Treść jest zgodna z WCAG 2.2 AA.',
                toastDiscarded: 'ℹ️ Zmiany zostały odrzucone. Treść wpisu w edytorze pozostała nienaruszona.',
                toastUndo: '↩️ Cofnięto naprawę. Przywrócono poprzednią treść wpisu.'
            }
        },

        t: function (key, fallback, replacements) {
            const l = (this.lang === 'pl') ? 'pl' : 'en';
            let str = (this.dict[l] && this.dict[l][key]) || (this.dict.en && this.dict.en[key]) || fallback || key;
            if (replacements && typeof replacements === 'object') {
                for (const rk in replacements) {
                    str = str.replace(new RegExp('\\{' + rk + '\\}', 'g'), replacements[rk]);
                }
            }
            return str;
        },

        init: function () {
            const self = this;

            const configLang = (window.EaaGuardConfig && window.EaaGuardConfig.lang) || '';
            const htmlLang = (document.documentElement.lang || '').toLowerCase();
            self.lang = (configLang === 'pl' || htmlLang.startsWith('pl')) ? 'pl' : 'en';

            const boot = () => {
                self.isGutenberg = document.body.classList.contains('block-editor-page') ||
                                   !!(window.wp && wp.data && (wp.data.select('core/block-editor') || wp.data.select('core/editor')));
                self.setup();
            };

            if (window.wp && wp.domReady) {
                wp.domReady(boot);
            } else if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }
        },

        setup: function () {
            const self = this;
            self.createFloatingBadge();
            self.createModal();

            if (self.isGutenberg) {
                self.setupGutenberg();
            } else {
                self.setupClassicEditor();
            }

            // Keyboard shortcut protection (Ctrl+S / Cmd+S)
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    if (self.isBlocked) {
                        e.preventDefault();
                        self.showModal();
                        self.showToast(
                            (self.lang === 'pl')
                                ? '⛔ Zapis zablokowany: usuń błędy WCAG 2.2 przed zapisem!'
                                : '⛔ Save blocked: resolve WCAG 2.2 violations before saving!',
                            'error'
                        );
                    }
                }
            });

            setTimeout(() => self.runAudit(), 1000);
            setTimeout(() => self.runAudit(), 2500);
        },

        setupGutenberg: function () {
            const self = this;
            let lastSig = '';

            const getBlocksSignature = () => {
                if (!window.wp || !wp.data || !wp.data.select) return '';
                const blockSel = wp.data.select('core/block-editor');
                if (!blockSel || typeof blockSel.getBlocks !== 'function') return '';
                const blocks = blockSel.getBlocks();
                if (!blocks || !blocks.length) return '';
                let sig = '';
                const walk = (bl) => {
                    for (let i = 0; i < bl.length; i++) {
                        const b = bl[i];
                        sig += b.name + ':' + (b.attributes ? JSON.stringify(b.attributes) : '') + ';';
                        if (b.innerBlocks && b.innerBlocks.length) walk(b.innerBlocks);
                    }
                };
                walk(blocks);
                return sig;
            };

            const handleStateChange = () => {
                const sig = getBlocksSignature();
                if (sig && sig !== lastSig) {
                    lastSig = sig;
                    self.debounceAudit();
                    return;
                }
                const currentContent = self.getCurrentContent();
                if (currentContent && currentContent !== lastSig) {
                    lastSig = currentContent;
                    self.debounceAudit();
                }
            };

            if (window.wp && wp.data && wp.data.subscribe) {
                wp.data.subscribe(handleStateChange);
            }
        },

        setupClassicEditor: function () {
            const self = this;

            if (window.tinymce) {
                tinymce.on('AddEditor', function (e) {
                    self.bindTinyMceEvents(e.editor);
                });
                if (tinymce.editors && tinymce.editors.length > 0) {
                    for (let i = 0; i < tinymce.editors.length; i++) {
                        self.bindTinyMceEvents(tinymce.editors[i]);
                    }
                }
            }

            const ta = document.getElementById('content');
            if (ta) {
                ta.addEventListener('input', () => self.debounceAudit());
                ta.addEventListener('change', () => self.runAudit());
            }

            // Intercept publish button
            const publishBtn = document.getElementById('publish');
            if (publishBtn) {
                publishBtn.addEventListener('click', (e) => {
                    if (self.isBlocked) {
                        e.preventDefault();
                        e.stopPropagation();
                        self.showModal();
                    }
                }, true);
            }
        },

        bindTinyMceEvents: function (editor) {
            const self = this;
            if (!editor) return;
            self.editorInstance = editor;

            editor.on('init', () => {
                self.runAudit();
            });

            editor.on('input change SetContent paste keyup NodeChange', () => {
                self.debounceAudit();
            });
        },

        getCurrentContent: function () {
            const isGb = this.isGutenberg || document.body.classList.contains('block-editor-page') ||
                         !!(window.wp && wp.data && wp.data.select && (wp.data.select('core/editor') || wp.data.select('core/block-editor')));

            if (isGb && window.wp && wp.data && wp.data.select) {
                if (wp.data.select('core/block-editor') && wp.blocks && typeof wp.blocks.serialize === 'function') {
                    const blocks = wp.data.select('core/block-editor').getBlocks();
                    if (blocks && blocks.length > 0) {
                        return wp.blocks.serialize(blocks);
                    }
                }
                const editorSel = wp.data.select('core/editor');
                if (editorSel && typeof editorSel.getEditedPostContent === 'function') {
                    const c = editorSel.getEditedPostContent();
                    if (c) return c;
                }
            }

            if (window.tinymce && tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
                return tinymce.activeEditor.getContent() || '';
            }
            const ta = document.getElementById('content');
            if (ta) {
                return ta.value || '';
            }
            return '';
        },

        setCurrentContent: function (html) {
            this.lastBackupContent = this.getCurrentContent();

            if (this.isGutenberg && window.wp && wp.data && wp.blocks) {
                try {
                    const parsedBlocks = wp.blocks.parse(html);
                    wp.data.dispatch('core/block-editor').resetBlocks(parsedBlocks);
                    return true;
                } catch (e) {
                    console.error('Gutenberg block reset error:', e);
                }
            }

            if (window.tinymce && tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
                tinymce.activeEditor.setContent(html);
                tinymce.activeEditor.save();
                return true;
            }

            const ta = document.getElementById('content');
            if (ta) {
                ta.value = html;
                ta.dispatchEvent(new Event('input', { bubbles: true }));
                ta.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            }

            return false;
        },

        debounceAudit: function () {
            const self = this;
            clearTimeout(self.auditDebounceTimer);
            self.auditDebounceTimer = setTimeout(() => self.runAudit(), 400);
        },

        // Client-side DOM Audit Engine
        runAudit: function () {
            const self = this;
            const content = self.getCurrentContent();
            const violations = [];
            let critical = 0;
            let warning = 0;

            if (!content || content.trim() === '') {
                self.violations = [];
                self.criticalCount = 0;
                self.warningCount = 0;
                self.updateUI();
                return;
            }

            const temp = document.createElement('div');
            temp.innerHTML = content;

            // 1. Check Images (WCAG 1.1.1)
            const imgs = temp.querySelectorAll('img');
            imgs.forEach((img) => {
                if (!img.hasAttribute('alt')) {
                    critical++;
                    violations.push({
                        code: 'WCAG_1_1_1_MISSING_ALT',
                        criterion: 'WCAG 1.1.1 (Non-text Content)',
                        severity: 'critical',
                        message: (self.lang === 'pl') ? 'Obraz nie posiada wymaganego atrybutu alt.' : 'Image is missing the required "alt" attribute.',
                        suggestion: (self.lang === 'pl') ? 'Dodaj atrybut alt="Opis obrazu".' : 'Add descriptive alt="Clear image description" attribute.',
                        snippet: img.outerHTML.substring(0, 100)
                    });
                } else {
                    const alt = img.getAttribute('alt').trim().toLowerCase();
                    const dummy = ['image', 'obraz', 'obrazek', 'zdjęcie', 'photo', 'pic', 'picture', 'dsc_', 'img_'];
                    if (dummy.some(d => alt === d || alt.startsWith(d))) {
                        warning++;
                        violations.push({
                            code: 'WCAG_1_1_1_DUMMY_ALT',
                            criterion: 'WCAG 1.1.1 (Non-text Content)',
                            severity: 'warning',
                            message: (self.lang === 'pl') ? `Opis alternatywny ("${img.getAttribute('alt')}") jest generyczny i bezużyteczny.` : `Alternative text ("${img.getAttribute('alt')}") is a generic placeholder.`,
                            suggestion: (self.lang === 'pl') ? 'Wprowadź merytoryczny opis zawartości grafiki.' : 'Replace with a meaningful description of what is depicted.',
                            snippet: img.outerHTML.substring(0, 100)
                        });
                    }
                }
            });

            // 2. Check Headings (WCAG 1.3.1, 2.4.6)
            const headings = temp.querySelectorAll('h1, h2, h3, h4, h5, h6');
            let lastLvl = 1;
            headings.forEach((h) => {
                const lvl = parseInt(h.tagName.substring(1), 10);
                const txt = h.textContent.trim();

                if (!txt) {
                    critical++;
                    violations.push({
                        code: 'WCAG_1_3_1_EMPTY_HEADING',
                        criterion: 'WCAG 1.3.1 (Info and Relationships)',
                        severity: 'critical',
                        message: (self.lang === 'pl') ? `Pusty nagłówek <${h.tagName.toLowerCase()}> pozbawiony tekstu.` : `Empty heading <${h.tagName.toLowerCase()}> with no visible text.`,
                        suggestion: (self.lang === 'pl') ? 'Wpisz treść nagłówka lub usuń pusty element.' : 'Provide visible heading text or remove the empty tag.',
                        snippet: h.outerHTML
                    });
                }

                if (lastLvl > 0 && lvl > lastLvl + 1) {
                    critical++;
                    violations.push({
                        code: 'WCAG_1_3_1_HEADING_LEVEL_SKIPPED',
                        criterion: 'WCAG 1.3.1 (Info and Relationships)',
                        severity: 'critical',
                        message: (self.lang === 'pl') ? `Pominięto poziom w strukturze: po H${lastLvl} następuje H${lvl}.` : `Skipped heading level: H${lvl} follows H${lastLvl}.`,
                        suggestion: (self.lang === 'pl') ? `Zmień nagłówek H${lvl} na H${lastLvl + 1}.` : `Change H${lvl} to H${lastLvl + 1} to maintain consecutive hierarchy.`,
                        snippet: h.outerHTML.substring(0, 80)
                    });
                }
                lastLvl = lvl;
            });

            // 3. Check Tables (WCAG 1.3.1)
            const tables = temp.querySelectorAll('table');
            tables.forEach((tbl) => {
                const ths = tbl.querySelectorAll('th');
                if (ths.length === 0) {
                    critical++;
                    violations.push({
                        code: 'WCAG_1_3_1_TABLE_NO_HEADERS',
                        criterion: 'WCAG 1.3.1 (Info and Relationships)',
                        severity: 'critical',
                        message: (self.lang === 'pl') ? 'Tabela nie posiada zdefiniowanych komórek nagłówkowych (<th>).' : 'Data table contains no header cells (<th>).',
                        suggestion: (self.lang === 'pl') ? 'Dodaj wiersz nagłówka z komórkami <th scope="col">.' : 'Convert first table row into <th> header cells with scope="col".',
                        snippet: tbl.outerHTML.substring(0, 100) + '...'
                    });
                }
            });

            // 4. Check Links (WCAG 2.4.4, 4.1.2)
            const links = temp.querySelectorAll('a');
            links.forEach((a) => {
                const txt = a.textContent.trim();
                const hasAria = a.hasAttribute('aria-label') && a.getAttribute('aria-label').trim() !== '';
                const hasTitle = a.hasAttribute('title') && a.getAttribute('title').trim() !== '';
                const hasImg = a.querySelector('img') !== null;

                if (!txt && !hasAria && !hasTitle && !hasImg) {
                    critical++;
                    violations.push({
                        code: 'WCAG_2_4_4_EMPTY_LINK',
                        criterion: 'WCAG 2.4.4 (Link Purpose in Context)',
                        severity: 'critical',
                        message: (self.lang === 'pl') ? 'Pusty odnośnik bez tekstu ani etykiety aria-label.' : 'Hyperlink <a> is completely empty with no text or accessible label.',
                        suggestion: (self.lang === 'pl') ? 'Dodaj tekst linku lub usuń martwy znacznik <a>.' : 'Provide visible link text or aria-label="Description", or remove the tag.',
                        snippet: a.outerHTML.substring(0, 100)
                    });
                }

                const generic = ['kliknij tutaj', 'tutaj', 'więcej', 'czytaj więcej', 'zobacz więcej', 'link', 'click here', 'more', 'read more', 'download'];
                if (generic.includes(txt.toLowerCase()) && !hasAria && !hasTitle) {
                    warning++;
                    violations.push({
                        code: 'WCAG_2_4_4_AMBIGUOUS_ANCHOR',
                        criterion: 'WCAG 2.4.4 (Link Purpose in Context)',
                        severity: 'warning',
                        message: (self.lang === 'pl') ? `Niejednoznaczna treść linku ("${txt}").` : `Ambiguous link text ("${txt}") lacks destination context.`,
                        suggestion: (self.lang === 'pl') ? 'Dodaj aria-label="Szczegółowy cel odnośnika".' : 'Add descriptive aria-label="Detailed target destination".',
                        snippet: a.outerHTML.substring(0, 100)
                    });
                }
            });

            // 5. Check Font Size & Contrast (WCAG 1.4.3 AA & 1.4.4)
            const styledEls = temp.querySelectorAll('[style], [class*="has-"], p, h1, h2, h3, h4, h5, h6, span, a, li, blockquote');
            styledEls.forEach((el) => {
                const style = el.getAttribute('style') || '';
                const className = el.getAttribute('class') || '';
                const txt = el.textContent.trim();
                if (!txt) return;

                // Font size check (< 11px)
                const fsMatch = style.match(/font-size\s*:\s*([^;]+)/i);
                if (fsMatch) {
                    const fsStr = fsMatch[1].trim().toLowerCase();
                    const val = parseFloat(fsStr);
                    if ((fsStr.includes('px') && val < 11.0) || (fsStr.includes('pt') && val < 8.5)) {
                        critical++;
                        violations.push({
                            code: 'WCAG_1_4_4_FONT_TOO_SMALL',
                            criterion: 'WCAG 1.4.4 (Resize Text)',
                            severity: 'critical',
                            message: (self.lang === 'pl') ? `Zbyt mały rozmiar czcionki (${fsStr}) na tekście "${txt.substring(0, 30)}".` : `Text font-size is ${fsStr}, below the minimum legible threshold (11px).`,
                            suggestion: (self.lang === 'pl') ? 'Zwiększ wielkość czcionki do minimum 12-16px.' : 'Increase font size to at least 14-16px.',
                            snippet: txt.substring(0, 60)
                        });
                    }
                }

                // Contrast calculation with DOM inheritance and Gutenberg theme classes (base, white, etc.)
                let fgColor = null;
                let bgColor = null;

                if (className.includes('has-white-color') || className.includes('has-base-color') || className.includes('has-background-color')) {
                    fgColor = [255, 255, 255];
                } else {
                    const cMatch = className.match(/\bhas-([a-z0-9-]+)-color\b/i);
                    if (cMatch) fgColor = self.parseColor(cMatch[1]);
                }

                if (className.includes('has-white-background-color') || className.includes('has-base-background-color')) {
                    bgColor = [255, 255, 255];
                } else {
                    const bgMatch = className.match(/\bhas-([a-z0-9-]+)-background-color\b/i);
                    if (bgMatch) bgColor = self.parseColor(bgMatch[1]);
                }

                let curr = el;
                while (curr && curr.getAttribute) {
                    const s = curr.getAttribute('style') || '';
                    const c = curr.getAttribute('class') || '';

                    if (!fgColor) {
                        if (c.includes('has-white-color') || c.includes('has-base-color') || c.includes('has-background-color')) {
                            fgColor = [255, 255, 255];
                        } else {
                            const cm = c.match(/\bhas-([a-z0-9-]+)-color\b/i);
                            if (cm) fgColor = self.parseColor(cm[1]);
                            if (!fgColor) {
                                const colorM = s.match(/(?<!-)\bcolor\s*:\s*([^;]+)/i);
                                if (colorM) fgColor = self.parseColor(colorM[1]);
                            }
                        }
                    }
                    if (!bgColor) {
                        if (c.includes('has-white-background-color') || c.includes('has-base-background-color')) {
                            bgColor = [255, 255, 255];
                        } else {
                            const bm = c.match(/\bhas-([a-z0-9-]+)-background-color\b/i);
                            if (bm) bgColor = self.parseColor(bm[1]);
                            if (!bgColor) {
                                const bgM = s.match(/\bbackground(-color)?\s*:\s*([^;]+)/i);
                                if (bgM) bgColor = self.parseColor(bgM[2]);
                            }
                        }
                    }
                    curr = curr.parentElement;
                }

                if (fgColor) {
                    if (!bgColor) bgColor = [255, 255, 255]; // default white page background
                    const ratio = self.getContrastRatio(fgColor, bgColor);
                    if (ratio < 4.50) {
                        const snippetStr = txt.substring(0, 50);
                        const exists = violations.some(v => v.code === 'WCAG_1_4_3_CONTRAST_LOW' && v.snippet && v.snippet.includes(snippetStr));
                        if (!exists) {
                            critical++;
                            violations.push({
                                code: 'WCAG_1_4_3_CONTRAST_LOW',
                                criterion: 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                severity: 'critical',
                                message: (self.lang === 'pl') 
                                    ? `Niedozwolony niski kontrast tekstu ("${snippetStr}") – obliczony współczynnik: ${ratio.toFixed(2)}:1 (wymagany min. 4.50:1). Biały lub jasny tekst zlewa się z tłem i jest niewidoczny.`
                                    : `Illegal low color contrast on text "${snippetStr}" – ratio: ${ratio.toFixed(2)}:1 (min. 4.50:1 required). Text blends into background.`,
                                suggestion: (self.lang === 'pl') 
                                    ? 'Zwiększ kontrast między tekstem a tłem (np. czarny tekst na jasnym tle) lub usuń wymuszony kolor.'
                                    : 'Increase contrast ratio between text and background to at least 4.50:1.',
                                snippet: snippetStr
                            });
                        }
                    }
                }
            });

            // 5b. Live Gutenberg Editor Canvas Inspection (Iframe & In-Page)
            let canvasDoc = null;
            const canvasIframe = document.querySelector('iframe[name="editor-canvas"]');
            if (canvasIframe && canvasIframe.contentDocument) {
                canvasDoc = canvasIframe.contentDocument;
            } else if (document.querySelector('.editor-styles-wrapper')) {
                canvasDoc = document;
            }

            if (canvasDoc) {
                const canvasEls = canvasDoc.querySelectorAll('.editor-styles-wrapper p, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6, .editor-styles-wrapper span, .editor-styles-wrapper [class*="has-"]');
                canvasEls.forEach((cel) => {
                    const ctxt = cel.textContent.trim();
                    if (!ctxt) return;
                    const cClass = cel.getAttribute('class') || '';
                    
                    let isWhiteFg = cClass.includes('has-base-color') || cClass.includes('has-white-color');
                    if (!isWhiteFg) {
                        try {
                            const cs = window.getComputedStyle(cel);
                            if (cs && (cs.color === 'rgb(255, 255, 255)' || cs.color === '#ffffff' || cs.color === 'white')) {
                                isWhiteFg = true;
                            }
                        } catch (e) {}
                    }
                    
                    if (isWhiteFg) {
                        let hasDarkBg = false;
                        let p = cel;
                        while (p && p.tagName !== 'BODY') {
                            const pc = p.getAttribute('class') || '';
                            if (pc.includes('has-contrast-background-color') || pc.includes('has-black-background-color') || pc.includes('has-dark')) {
                                hasDarkBg = true;
                                break;
                            }
                            try {
                                const pcs = window.getComputedStyle(p);
                                const bg = self.parseColor(pcs.backgroundColor);
                                if (bg && self.getRelativeLuminance(bg) < 0.2) {
                                    hasDarkBg = true;
                                    break;
                                }
                            } catch (e) {}
                            p = p.parentElement;
                        }

                        if (!hasDarkBg) {
                            const snippetStr = ctxt.substring(0, 50);
                            const exists = violations.some(v => v.code === 'WCAG_1_4_3_CONTRAST_LOW' && v.snippet && v.snippet.includes(snippetStr));
                            if (!exists) {
                                critical++;
                                violations.push({
                                    code: 'WCAG_1_4_3_CONTRAST_LOW',
                                    criterion: 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                    severity: 'critical',
                                    message: (self.lang === 'pl') 
                                        ? `Biały tekst na białym tle ("${snippetStr}") – współczynnik kontrastu 1.00:1 (wymagany min. 4.50:1). Treść jest całkowicie niewidoczna dla użytkowników.`
                                        : `White text on white background ("${snippetStr}") – contrast ratio 1.00:1 (min. 4.50:1 required). Text is completely invisible.`,
                                    suggestion: (self.lang === 'pl') 
                                        ? 'Zmień kolor tekstu na ciemny w panelu bocznym bloku (np. Kolor -> Tekst -> Domyślny / Contrast) lub dodaj ciemne tło.'
                                        : 'Change text color to a dark contrast color in the block settings sidebar or add a dark background.',
                                    snippet: snippetStr
                                });
                            }
                        }
                    }
                });
            }

            // 6. Direct Gutenberg Block Attributes Contrast Check
            if (window.wp && wp.data && wp.data.select) {
                const blockSel = wp.data.select('core/block-editor') || wp.data.select('core/editor');
                if (blockSel && typeof blockSel.getBlocks === 'function') {
                    const blocks = blockSel.getBlocks();
                    self.checkGutenbergBlocksContrast(blocks, violations, (isCrit) => {
                        if (isCrit) critical++; else warning++;
                    });
                }
            }

            // 7. Check Gutenberg Block Attributes Contrast in raw serialized comment JSON
            const commentRegex = /<!--\s*wp:[a-z0-9\/-]+\s*(\{.*?\})\s*-->/gis;
            let bm;
            while ((bm = commentRegex.exec(content)) !== null) {
                try {
                    const blockData = JSON.parse(bm[1]);
                    if (blockData) {
                        const tc = blockData.textColor || (blockData.style && blockData.style.color && blockData.style.color.text);
                        const bc = blockData.backgroundColor || (blockData.style && blockData.style.color && blockData.style.color.background);
                        if (tc) {
                            const parsedFg = self.parseColor(tc);
                            const parsedBg = bc ? self.parseColor(bc) : [255, 255, 255];
                            if (parsedFg && parsedBg) {
                                const ratio = self.getContrastRatio(parsedFg, parsedBg);
                                if (ratio < 4.50) {
                                    const snippetStr = bm[0].substring(0, 60);
                                    const exists = violations.some(v => v.code === 'WCAG_1_4_3_CONTRAST_LOW' && v.snippet && v.snippet.includes(snippetStr));
                                    if (!exists) {
                                        critical++;
                                        violations.push({
                                            code: 'WCAG_1_4_3_CONTRAST_LOW',
                                            criterion: 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                            severity: 'critical',
                                            message: (self.lang === 'pl')
                                                ? `Niedozwolony niski kontrast koloru tekstu ("${tc}" na tle ${bc || 'białym'}) – współczynnik: ${ratio.toFixed(2)}:1 (wymagany min. 4.50:1). Tekst zlewa się z tłem.`
                                                : `Illegal low color contrast ("${tc}" on ${bc || 'white'} background) – ratio: ${ratio.toFixed(2)}:1 (min. 4.50:1 required). Text blends into background.`,
                                            suggestion: (self.lang === 'pl')
                                                ? 'Zmień kolor tekstu na kontrastowy w panelu ustawień bloku (po prawej stronie edytora).'
                                                : 'Change text color to a high-contrast color in the block settings sidebar.',
                                            snippet: snippetStr
                                        });
                                    }
                                }
                            }
                        }
                    }
                } catch (e) {}
            }

            self.violations = violations;
            self.criticalCount = critical;
            self.warningCount = warning;
            self.updateUI();
        },

        updateUI: function () {
            const self = this;
            const badge = document.getElementById('eaaguard-floating-badge');
            const icon = document.getElementById('eaaguard-badge-icon');
            const label = document.getElementById('eaaguard-badge-label');
            const count = document.getElementById('eaaguard-badge-count');

            if (!badge) return;

            const isLicensed = self.config.isLicensed;
            const hasCritical = self.criticalCount > 0;
            const hasWarning = self.warningCount > 0;

            if (!isLicensed) {
                badge.className = 'eaaguard-badge-float eaaguard-badge-block';
                icon.textContent = '🔒';
                label.textContent = (self.lang === 'pl') ? 'Brak Licencji EAA' : 'License Required';
                count.style.display = 'none';
                self.lockPublishing(true);
                return;
            }

            if (hasCritical) {
                badge.className = 'eaaguard-badge-float eaaguard-badge-block';
                icon.textContent = '⛔';
                label.textContent = self.t('badgeBlocked', 'Publication Blocked');
                count.textContent = self.criticalCount;
                count.style.display = 'inline-block';
                self.lockPublishing(true);
            } else if (hasWarning) {
                badge.className = 'eaaguard-badge-float eaaguard-badge-warn';
                icon.textContent = '⚠️';
                label.textContent = self.t('badgeWarning', 'WCAG Warnings');
                count.textContent = self.warningCount;
                count.style.display = 'inline-block';
                self.lockPublishing(false);
            } else {
                badge.className = 'eaaguard-badge-float eaaguard-badge-pass';
                icon.textContent = '🟢';
                label.textContent = self.t('badgePass', 'WCAG 2.2: Compliant');
                count.style.display = 'none';
                self.lockPublishing(false);
            }
        },

        lockPublishing: function (lock) {
            const self = this;
            self.isBlocked = lock;

            const isGb = self.isGutenberg || document.body.classList.contains('block-editor-page') || !!(window.wp && wp.data && wp.data.dispatch);
            if (isGb && window.wp && wp.data && wp.data.dispatch) {
                const editorDispatch = wp.data.dispatch('core/editor');
                if (editorDispatch) {
                    if (lock) {
                        if (typeof editorDispatch.lockPostSaving === 'function') {
                            editorDispatch.lockPostSaving('eaa-wcag-violations');
                        }
                        if (wp.data.dispatch('core/notices')) {
                            wp.data.dispatch('core/notices').createErrorNotice(
                                (self.lang === 'pl')
                                    ? '⛔ Strażnik WCAG 2.2 / EAA: Publikacja i zapis zablokowane z powodu błędów dostępności. Kliknij pływający widget, aby naprawić.'
                                    : '⛔ WCAG 2.2 / EAA Guard: Publication blocked due to accessibility violations. Click floating widget to auto-fix.',
                                { id: 'eaaguard-lock-notice', isDismissible: true }
                            );
                        }
                    } else {
                        if (typeof editorDispatch.unlockPostSaving === 'function') {
                            editorDispatch.unlockPostSaving('eaa-wcag-violations');
                        }
                        if (wp.data.dispatch('core/notices')) {
                            wp.data.dispatch('core/notices').removeNotice('eaaguard-lock-notice');
                        }
                    }
                }
            }

            const publishBtn = document.getElementById('publish');
            if (publishBtn) {
                if (lock) {
                    publishBtn.setAttribute('data-eaaguard-locked', '1');
                    publishBtn.style.opacity = '0.5';
                    publishBtn.style.pointerEvents = 'none';
                } else {
                    publishBtn.removeAttribute('data-eaaguard-locked');
                    publishBtn.style.opacity = '1';
                    publishBtn.style.pointerEvents = 'auto';
                }
            }
        },

        checkGutenbergBlocksContrast: function (blocks, violations, incCount) {
            const self = this;
            if (!blocks || !blocks.length) return;

            blocks.forEach((block) => {
                const attrs = block.attributes || {};
                const textColor = attrs.textColor || (attrs.style && attrs.style.color && attrs.style.color.text);
                const bgColor = attrs.backgroundColor || (attrs.style && attrs.style.color && attrs.style.color.background);

                if (textColor) {
                    const parsedFg = self.parseColor(textColor);
                    const parsedBg = bgColor ? self.parseColor(bgColor) : [255, 255, 255];

                    if (parsedFg && parsedBg) {
                        const ratio = self.getContrastRatio(parsedFg, parsedBg);
                        if (ratio < 4.50) {
                            incCount(true);
                            violations.push({
                                code: 'WCAG_1_4_3_CONTRAST_LOW',
                                criterion: 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                severity: 'critical',
                                message: (self.lang === 'pl') 
                                    ? `Niedozwolony niski kontrast w bloku "${block.name}" (tekst: ${textColor}, tło: ${bgColor || 'domyślne białe'}) – współczynnik: ${ratio.toFixed(2)}:1 (wymagany min. 4.50:1). Tekst jest niewidoczny.`
                                    : `Illegal low color contrast in block "${block.name}" (text: ${textColor}, bg: ${bgColor || 'white'}) – ratio: ${ratio.toFixed(2)}:1 (min. 4.50:1 required). Text is unreadable.`,
                                suggestion: (self.lang === 'pl') 
                                    ? 'Zmień kolor tekstu na kontrastowy w panelu ustawień bloku (po prawej stronie edytora).'
                                    : 'Change text color to a high-contrast color in the block settings sidebar.',
                                snippet: (attrs.content ? String(attrs.content).substring(0, 60) : block.name)
                            });
                        }
                    }
                }

                if (block.innerBlocks && block.innerBlocks.length) {
                    self.checkGutenbergBlocksContrast(block.innerBlocks, violations, incCount);
                }
            });
        },

        parseColor: function (str) {
            if (!str) return null;
            str = String(str).toLowerCase().trim().replace(/['";]/g, '');
            if (str === 'transparent' || str === 'inherit' || str === 'initial') return null;

            // Theme preset slug or variable check
            if (str.startsWith('var:preset|color|') || str.startsWith('var(--wp--preset--color--')) {
                str = str.replace(/^var:preset\|color\|/, '').replace(/^var\(--wp--preset--color--/, '').replace(/\)$/, '');
            }

            if (str.startsWith('#')) {
                let hex = str.slice(1);
                if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                if (hex.length >= 6) {
                    return [parseInt(hex.slice(0, 2), 16), parseInt(hex.slice(2, 4), 16), parseInt(hex.slice(4, 6), 16)];
                }
            }
            const rgbM = str.match(/rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/);
            if (rgbM) {
                return [parseInt(rgbM[1], 10), parseInt(rgbM[2], 10), parseInt(rgbM[3], 10)];
            }

            // Check active Gutenberg theme settings
            if (window.wp && wp.data && wp.data.select) {
                try {
                    const bSel = wp.data.select('core/block-editor');
                    if (bSel && typeof bSel.getSettings === 'function') {
                        const s = bSel.getSettings();
                        if (s && s.colors && Array.isArray(s.colors)) {
                            const found = s.colors.find(c => c.slug === str || (c.name && c.name.toLowerCase() === str));
                            if (found && found.color) {
                                return this.parseColor(found.color);
                            }
                        }
                    }
                } catch (e) {}
            }

            const names = {
                white: [255, 255, 255],
                base: [255, 255, 255],         // Twenty Twenty-Five / Twenty Twenty-Four base white
                background: [255, 255, 255],   // Standard background slug
                'accent-5': [251, 250, 243],   // TT5 light cream
                contrast: [17, 17, 17],        // TT5 dark contrast
                black: [0, 0, 0],
                gray: [128, 128, 128],
                grey: [128, 128, 128],
                silver: [192, 192, 192],
                lightgray: [211, 211, 211],
                lightgrey: [211, 211, 211],
                yellow: [255, 255, 0],
                red: [255, 0, 0],
                blue: [0, 0, 255],
                green: [0, 128, 0]
            };
            return names[str] || null;
        },

        getRelativeLuminance: function (rgb) {
            const a = rgb.map(v => {
                v /= 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
        },

        getContrastRatio: function (rgb1, rgb2) {
            const l1 = this.getRelativeLuminance(rgb1);
            const l2 = this.getRelativeLuminance(rgb2);
            const lighter = Math.max(l1, l2);
            const darker = Math.min(l1, l2);
            return (lighter + 0.05) / (darker + 0.05);
        },

        createFloatingBadge: function () {
            if (document.getElementById('eaaguard-floating-badge')) return;

            const badge = document.createElement('div');
            badge.id = 'eaaguard-floating-badge';
            badge.className = 'eaaguard-badge-float eaaguard-badge-pass';
            badge.setAttribute('role', 'status');
            badge.setAttribute('aria-live', 'polite');
            badge.innerHTML = `
                <div class="eaaguard-badge-pill" id="eaaguard-badge-trigger">
                    <span class="eaaguard-badge-icon" id="eaaguard-badge-icon">🟢</span>
                    <span class="eaaguard-badge-label" id="eaaguard-badge-label">${this.t('badgePass', 'WCAG 2.2: Compliant')}</span>
                    <span class="eaaguard-badge-counter" id="eaaguard-badge-count" style="display:none;">0</span>
                </div>
            `;
            document.body.appendChild(badge);

            badge.addEventListener('click', () => {
                window.EaaGuard.showModal();
            });
        },

        createModal: function () {
            if (document.getElementById('eaaguard-modal-backdrop')) return;

            const modal = document.createElement('div');
            modal.id = 'eaaguard-modal-backdrop';
            modal.className = 'eaaguard-modal-backdrop';
            modal.style.display = 'none';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'eaaguard-modal-title');

            modal.innerHTML = `
                <div class="eaaguard-modal-dialog">
                    <div class="eaaguard-modal-header">
                        <div class="eaaguard-modal-title-wrap">
                            <span class="eaaguard-modal-shield">🛡️</span>
                            <h2 id="eaaguard-modal-title" class="eaaguard-modal-title">${this.t('modalTitle', 'WCAG 2.2 / EAA Accessibility Guard')}</h2>
                        </div>
                        <button type="button" class="eaaguard-modal-close" id="eaaguard-modal-close-btn" aria-label="${this.t('btnClose', 'Close')}">&times;</button>
                    </div>
                    <div class="eaaguard-modal-body" id="eaaguard-modal-content"></div>
                    <div class="eaaguard-modal-footer">
                        <span class="eaaguard-modal-status-text" id="eaaguard-modal-status-summary"></span>
                        <div class="eaaguard-modal-actions" id="eaaguard-modal-actions">
                            <button type="button" class="eaaguard-mbtn eaaguard-mbtn-secondary" id="eaaguard-btn-dismiss">${this.t('btnClose', 'Close')}</button>
                            <button type="button" class="eaaguard-mbtn eaaguard-mbtn-autofix" id="eaaguard-btn-autofix" style="display:none;"><span class="eaaguard-btn-icon">⚡</span> ${this.t('btnAutoFix', '⚡ Auto-Fix (Preview)')}</button>
                            <button type="button" class="eaaguard-mbtn eaaguard-mbtn-undo" id="eaaguard-btn-undo" style="display:none;">${this.t('btnUndo', '↩️ Undo Fix')}</button>
                            <button type="button" class="eaaguard-mbtn eaaguard-mbtn-primary" id="eaaguard-btn-recheck">${this.t('btnRecheck', 'Re-Check Content')}</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('eaaguard-modal-close-btn').addEventListener('click', () => window.EaaGuard.hideModal());
            document.getElementById('eaaguard-btn-dismiss').addEventListener('click', () => window.EaaGuard.hideModal());
            document.getElementById('eaaguard-btn-recheck').addEventListener('click', () => {
                window.EaaGuard.runAudit();
                window.EaaGuard.renderModalContent();
            });
            document.getElementById('eaaguard-btn-autofix').addEventListener('click', () => {
                window.EaaGuard.prepareAutoFixPreview();
            });
            document.getElementById('eaaguard-btn-undo').addEventListener('click', () => {
                window.EaaGuard.undoAutoFix();
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) window.EaaGuard.hideModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display !== 'none') {
                    window.EaaGuard.hideModal();
                }
            });
        },

        showModal: function () {
            this.currentView = 'report';
            this.renderModalContent();
            const modal = document.getElementById('eaaguard-modal-backdrop');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('eaaguard-modal-close-btn').focus();
            }
        },

        hideModal: function () {
            const modal = document.getElementById('eaaguard-modal-backdrop');
            if (modal) modal.style.display = 'none';
        },

        renderModalContent: function () {
            const self = this;
            const container = document.getElementById('eaaguard-modal-content');
            const summary = document.getElementById('eaaguard-modal-status-summary');
            const btnAutoFix = document.getElementById('eaaguard-btn-autofix');
            const btnUndo = document.getElementById('eaaguard-btn-undo');

            if (!container) return;

            if (self.currentView === 'preview') {
                self.renderPreviewView();
                return;
            }

            document.getElementById('eaaguard-modal-title').textContent = self.t('modalTitle', 'WCAG 2.2 / EAA Accessibility Guard');

            if (self.lastBackupContent) {
                btnUndo.style.display = 'inline-flex';
            } else {
                btnUndo.style.display = 'none';
            }

            if (!self.config.isLicensed) {
                btnAutoFix.style.display = 'none';
                summary.innerHTML = '<span style="color:#dc2626;font-weight:bold;">Wymagana Licencja Komercyjna FlexExt!</span>';
                container.innerHTML = `
                    <div class="eaaguard-m-alert eaaguard-m-alert-danger">
                        <span class="eaaguard-m-alert-icon">🔒</span>
                        <div>
                            <h4>Funkcje zablokowane</h4>
                            <p>Wprowadź klucz licencyjny w panelu EAA Guard, aby aktywować weryfikację dostępności i automatyczną naprawę.</p>
                        </div>
                    </div>
                `;
                return;
            }

            const total = self.violations.length;
            if (total === 0) {
                btnAutoFix.style.display = 'none';
                summary.innerHTML = '<span style="color:#059669;font-weight:bold;">🟢 Status: Zgodny z WCAG 2.2 AA / EAA</span>';
                container.innerHTML = `
                    <div class="eaaguard-m-alert eaaguard-m-alert-success">
                        <span class="eaaguard-m-alert-icon">🎉</span>
                        <div>
                            <h4>${self.t('passCongratulations', 'Congratulations! No accessibility violations detected.')}</h4>
                            <p>${self.t('passDescription', 'Post content fully complies with WCAG 2.2 AA guidelines and EAA.')}</p>
                        </div>
                    </div>
                `;
                return;
            }

            btnAutoFix.style.display = 'inline-flex';
            summary.innerHTML = self.t('summaryFound', 'Found errors', { critical: self.criticalCount, warning: self.warningCount });

            let html = '';
            if (self.criticalCount > 0) {
                html += `
                    <div class="eaaguard-m-alert eaaguard-m-alert-danger">
                        <span class="eaaguard-m-alert-icon">⛔</span>
                        <div>
                            <h4>${self.t('alertBlockedTitle', 'Post publication is physically blocked!')}</h4>
                            <p>${self.t('alertBlockedDesc', 'Resolve critical accessibility errors or use 1-Click Auto-Fix.')}</p>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="eaaguard-m-alert eaaguard-m-alert-warning">
                        <span class="eaaguard-m-alert-icon">⚠️</span>
                        <div>
                            <h4>${self.t('alertWarnTitle', 'Accessibility recommendations detected:')}</h4>
                            <p>${self.t('alertWarnDesc', 'Review recommendations below or use Auto-Fix.')}</p>
                        </div>
                    </div>
                `;
            }

            html += '<div class="eaaguard-violations-list">';
            self.violations.forEach((v) => {
                const isCrit = v.severity === 'critical';
                const badgeClass = isCrit ? 'eaaguard-vbadge-crit' : 'eaaguard-vbadge-warn';
                const badgeText = isCrit ? self.t('badgeCritical', 'CRITICAL ERROR') : self.t('badgeWarningText', 'WARNING');

                html += `
                    <div class="eaaguard-vcard ${isCrit ? 'eaaguard-vcard-crit' : 'eaaguard-vcard-warn'}">
                        <div class="eaaguard-vcard-header">
                            <span class="eaaguard-vbadge ${badgeClass}">${badgeText}</span>
                            <span class="eaaguard-vcriterion">${v.criterion}</span>
                        </div>
                        <div class="eaaguard-vcard-body">
                            <p class="eaaguard-vmsg"><strong>${self.t('lblProblem', 'Problem:')}</strong> ${v.message}</p>
                            <p class="eaaguard-vsugg"><strong>${self.t('lblHowToFix', 'How to fix:')}</strong> ${v.suggestion}</p>
                            ${v.snippet ? `<pre class="eaaguard-vsnippet"><code>${self.escapeHtml(v.snippet)}</code></pre>` : ''}
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            container.innerHTML = html;
        },

        // 1-Click Auto-Fix Preview Preparation
        prepareAutoFixPreview: function () {
            const self = this;
            const content = self.getCurrentContent();

            // Call AJAX auto-fix
            jQuery.post(self.config.ajaxUrl, {
                action: 'eaaguard_autofix',
                nonce: self.config.nonce,
                content: content
            }, function (res) {
                if (res.success && res.data) {
                    self.pendingRepairedHtml = res.data.repairedHtml;
                    self.pendingFixes = res.data.fixes || [];
                    self.currentView = 'preview';
                    self.renderModalContent();
                } else {
                    self.showToast('Błąd generowania automatycznej naprawy.', 'error');
                }
            }).fail(function () {
                self.showToast('Błąd połączenia z serwerem.', 'error');
            });
        },

        renderPreviewView: function () {
            const self = this;
            const container = document.getElementById('eaaguard-modal-content');
            const summary = document.getElementById('eaaguard-modal-status-summary');
            const actions = document.getElementById('eaaguard-modal-actions');

            document.getElementById('eaaguard-modal-title').textContent = self.t('modalTitlePreview', 'WCAG 2.2 / EAA Auto-Fix Remediation Preview');

            summary.innerHTML = '<span style="color:#2563eb;font-weight:bold;">🔍 ' + self.t('previewSummaryWaiting', 'Preview mode: waiting for confirmation') + '</span>';

            actions.innerHTML = `
                <button type="button" class="eaaguard-mbtn eaaguard-mbtn-discard" id="eaaguard-btn-discard">${self.t('btnDiscard', '❌ Discard Changes (Do Not Save)')}</button>
                <button type="button" class="eaaguard-mbtn eaaguard-mbtn-apply" id="eaaguard-btn-apply">${self.t('btnApply', '💾 Apply Fixes to Editor')}</button>
            `;

            document.getElementById('eaaguard-btn-discard').addEventListener('click', () => {
                self.currentView = 'report';
                self.renderModalContent();
                self.showToast(self.t('toastDiscarded', 'Changes discarded. Editor untouched.'), 'info');
            });

            document.getElementById('eaaguard-btn-apply').addEventListener('click', () => {
                self.applyAutoFix();
            });

            const currentHtml = self.getCurrentContent();
            const repairedHtml = self.pendingRepairedHtml;
            const fixes = self.pendingFixes;

            let html = `
                <div class="eaaguard-m-alert eaaguard-m-alert-info">
                    <span class="eaaguard-m-alert-icon">✨</span>
                    <div>
                        <h4>${self.t('previewBannerTitle', 'Generated automatic repairs', { count: fixes.length })}</h4>
                        <p>${self.t('previewBannerDesc', 'Inspect layout or diff before applying.')}</p>
                    </div>
                </div>

                <div class="eaaguard-preview-tabs">
                    <button type="button" class="eaaguard-ptab-btn ${self.activePreviewTab === 'visual' ? 'active' : ''}" id="eaaguard-tab-visual">${self.t('previewTabVisual', 'Live Visual Preview')}</button>
                    <button type="button" class="eaaguard-ptab-btn ${self.activePreviewTab === 'diff' ? 'active' : ''}" id="eaaguard-tab-diff">${self.t('previewTabDiff', 'Source Code Diff')}</button>
                </div>

                <div id="eaaguard-preview-pane-visual" class="eaaguard-preview-pane ${self.activePreviewTab === 'visual' ? 'active' : ''}">
                    <div class="eaaguard-visual-frame">${repairedHtml}</div>
                </div>

                <div id="eaaguard-preview-pane-diff" class="eaaguard-preview-pane ${self.activePreviewTab === 'diff' ? 'active' : ''}">
                    <div class="eaaguard-diff-container">
                        <div class="eaaguard-diff-col">
                            <h5>${self.t('diffBeforeHead', 'Source Code Before Fix')}</h5>
                            <pre class="eaaguard-diff-pre eaaguard-diff-before"><code>${self.escapeHtml(currentHtml)}</code></pre>
                        </div>
                        <div class="eaaguard-diff-col">
                            <h5>${self.t('diffAfterHead', 'Source Code After Fix')}</h5>
                            <pre class="eaaguard-diff-pre eaaguard-diff-after"><code>${self.escapeHtml(repairedHtml)}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="eaaguard-fixes-list">
                    <h4>${self.t('previewListHeading', 'Itemized breakdown', { count: fixes.length })}</h4>
                    <ul>
                        ${fixes.map(f => `<li><span class="eaaguard-fix-icon">${f.icon || '✔️'}</span> <strong>${f.title}:</strong> ${f.detail}</li>`).join('')}
                    </ul>
                </div>
            `;

            container.innerHTML = html;

            document.getElementById('eaaguard-tab-visual').addEventListener('click', () => {
                self.activePreviewTab = 'visual';
                self.renderPreviewView();
            });
            document.getElementById('eaaguard-tab-diff').addEventListener('click', () => {
                self.activePreviewTab = 'diff';
                self.renderPreviewView();
            });
        },

        applyAutoFix: function () {
            const self = this;
            if (!self.pendingRepairedHtml) return;

            const success = self.setCurrentContent(self.pendingRepairedHtml);
            if (success) {
                self.pendingRepairedHtml = null;
                self.pendingFixes = [];
                self.currentView = 'report';
                self.hideModal();
                self.showToast(self.t('toastApplied', 'Remediation applied to editor!'), 'success');

                setTimeout(() => {
                    self.runAudit();
                }, 400);
            } else {
                self.showToast('Nie udało się wstrzyknąć kodu do edytora.', 'error');
            }
        },

        undoAutoFix: function () {
            const self = this;
            if (!self.lastBackupContent) return;

            self.setCurrentContent(self.lastBackupContent);
            self.lastBackupContent = null;
            self.showToast(self.t('toastUndo', 'Auto-fix reverted.'), 'info');
            self.runAudit();
            self.renderModalContent();
        },

        showToast: function (msg, type = 'info') {
            let toast = document.getElementById('eaaguard-toast-notice');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'eaaguard-toast-notice';
                toast.className = 'eaaguard-toast-notice';
                document.body.appendChild(toast);
            }
            toast.textContent = msg;
            toast.className = 'eaaguard-toast-notice eaaguard-toast-' + type + ' eaaguard-toast-show';

            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                toast.className = 'eaaguard-toast-notice';
            }, 3500);
        },

        escapeHtml: function (str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    };

    window.EaaGuard.init();
})();
