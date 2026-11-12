/**
 * Admin Dashboard JavaScript for EAA & WCAG 2.2 Guard
 * Bilingual English / Polish support & high contrast rendering
 *
 * @package     wp-eaaguard
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     Commercial Proprietary
 */

jQuery(document).ready(function ($) {
    'use strict';

    const config = window.EaaAdminConfig || {
        ajaxUrl: ajaxurl,
        nonce: '',
        lang: 'en',
        i18n: {}
    };

    const isPl = (config.lang === 'pl');

    const i18n = Object.assign({
        scanning: isPl ? 'Trwa skanowanie mediów...' : 'Scanning media library...',
        scanDone: isPl ? 'Skanowanie zakończone pomyślnie!' : 'Scan completed successfully!',
        saving: isPl ? 'Zapisywanie...' : 'Saving settings...',
        saved: isPl ? 'Ustawienia zostały pomyślnie zapisane!' : 'Settings successfully saved!',
        creating: isPl ? 'Tworzenie podstrony...' : 'Creating page...',
        pageCreated: isPl ? 'Podstrona deklaracji została utworzona / zaktualizowana!' : 'Accessibility statement page created / updated!',
        error: isPl ? 'Wystąpił błąd podczas komunikacji z serwerem.' : 'An error occurred while contacting server.',
        confirmScan: isPl ? 'Czy chcesz rozpocząć masowy skan plików PDF w bibliotece mediów?' : 'Do you want to run a batch scan of PDF files in the Media Library?',
        compliant: isPl ? '🟢 Zgodny' : '🟢 Compliant',
        warning: isPl ? '🟡 Ostrzeżenie' : '🟡 Warning',
        violation: isPl ? '🔴 Naruszenie' : '🔴 Violation',
        noIssues: isPl ? 'Brak uwag' : 'No issues',
        issueDetails: isPl ? 'Szczegóły problemów:' : 'Issue details:',
        btnRunPdfScan: isPl ? '🔄 Rozpocznij Masowy Skan Mediów' : '🔄 Run Batch Media Scan',
        btnScanPosts: isPl ? '🔄 Skanuj Opublikowane Wpisy i Strony' : '🔄 Scan Published Posts & Pages',
        btnAuditUrl: isPl ? '🔍 Zbadaj Stronę' : '🔍 Audit Page',
        auditingPosts: isPl ? '⏳ Trwa audyt wpisów...' : '⏳ Auditing posts & pages...',
        auditingUrl: isPl ? '⏳ Trwa audyt URL...' : '⏳ Auditing URL...',
        critErrors: isPl ? 'Błędy Krytyczne:' : 'Critical Errors:',
        warnings: isPl ? 'Ostrzeżenia:' : 'Warnings:',
        notices: isPl ? 'Wskazówki:' : 'Notices:',
        issuesDetected: isPl ? 'Wykryte Problemy Dostępności:' : 'Detected Accessibility Issues:',
        noIssuesDetected: isPl ? '🎉 Brak wykrytych naruszeń WCAG na badanej podstronie!' : '🎉 No WCAG violations detected on inspected page!',
        headingTree: isPl ? 'Drzewo Hierarchii Nagłówków:' : 'Heading Hierarchy Outline Tree:',
        passedWcag: isPl ? '🟢 Zgodna z WCAG 2.2' : '🟢 WCAG 2.2 AA Compliant',
        failedWcag: isPl ? '🔴 Naruszenia Dostępności' : '🔴 Accessibility Violations',
        openPage: isPl ? 'Otwórz podstronę &rarr;' : 'Open page &rarr;',
        licVerifying: isPl ? 'Weryfikacja licencji...' : 'Verifying license...',
        licSaved: isPl ? '✅ Licencja zapisana! Przeładowywanie panelu...' : '✅ License saved! Reloading dashboard...'
    }, config.i18n || {});

    // 1. Unified Tab Navigation
    function switchTab(target, updateHistory) {
        if (!target) return;
        let clean = String(target).replace(/^#/, '');
        if (!clean.startsWith('tab-')) {
            clean = 'tab-' + clean;
        }

        const btn = $('.eaaguard-tab-btn[data-tab="' + clean + '"]');
        const pane = $('#' + clean);

        if (pane.length) {
            $('.eaaguard-tab-btn').removeClass('active').attr('aria-selected', 'false');
            if (btn.length) {
                btn.addClass('active').attr('aria-selected', 'true');
            }

            $('.eaaguard-tab-pane').removeClass('active');
            pane.addClass('active');

            const shortName = clean.replace('tab-', '');
            if (updateHistory !== false && history.replaceState) {
                history.replaceState(null, '', '#' + shortName);
            }

            // Sync WordPress left admin menu active highlight
            const slugMap = {
                'tab-overview': 'wp-eaaguard',
                'tab-pdf': 'wp-eaaguard-pdf',
                'tab-pages': 'wp-eaaguard-pages',
                'tab-declaration': 'wp-eaaguard-declaration',
                'tab-widget': 'wp-eaaguard-widget',
                'tab-license': 'wp-eaaguard-license'
            };
            const activeSlug = slugMap[clean];
            if (activeSlug) {
                $('#adminmenu li.current').removeClass('current');
                $('#adminmenu a[href*="page=' + activeSlug + '"]').closest('li').addClass('current');
            }
        }
    }

    // Tab button click
    $('.eaaguard-tab-btn').on('click', function (e) {
        e.preventDefault();
        switchTab($(this).data('tab'));
    });

    // Intercept WordPress sidebar menu clicks for EAA Guard
    $('#adminmenu a[href*="wp-eaaguard"]').on('click', function (e) {
        const href = $(this).attr('href') || '';
        let targetTab = 'tab-overview';
        if (href.includes('wp-eaaguard-pdf') || href.includes('tab=pdf') || href.includes('#pdf')) {
            targetTab = 'tab-pdf';
        } else if (href.includes('wp-eaaguard-pages') || href.includes('tab=pages') || href.includes('#pages')) {
            targetTab = 'tab-pages';
        } else if (href.includes('wp-eaaguard-declaration') || href.includes('tab=declaration') || href.includes('#declaration')) {
            targetTab = 'tab-declaration';
        } else if (href.includes('wp-eaaguard-widget') || href.includes('tab=widget') || href.includes('#widget')) {
            targetTab = 'tab-widget';
        } else if (href.includes('wp-eaaguard-license') || href.includes('tab=license') || href.includes('#license')) {
            targetTab = 'tab-license';
        }

        // If currently on any EAA Guard page, switch tab smoothly without reloading
        if (window.location.search && window.location.search.includes('page=wp-eaaguard')) {
            e.preventDefault();
            switchTab(targetTab);
        }
    });

    // Hashchange listener & initial load
    window.addEventListener('hashchange', function () {
        if (window.location.hash) {
            switchTab(window.location.hash.substring(1), false);
        }
    });

    if (window.location.hash) {
        switchTab(window.location.hash.substring(1), false);
    }

    // Reset Demo Content & Sample PDFs Button
    $('#btn-reset-demo-content').on('click', function (e) {
        e.preventDefault();
        const btn = $(this);
        const origText = btn.html();
        btn.prop('disabled', true).text(isPl ? '⏳ Przywracanie demo...' : '⏳ Resetting demo content...');

        $.post(config.ajaxUrl, {
            action: 'eaaguard_reset_demo_content',
            nonce: config.nonce
        }, function (res) {
            btn.prop('disabled', false).html(origText);
            if (res.success && res.data) {
                alert(res.data.message || (isPl ? 'Zresetowano demo!' : 'Demo reset complete!'));
                window.location.reload();
            } else {
                alert(res.data ? res.data.message : 'Error resetting demo content');
            }
        }).fail(function () {
            btn.prop('disabled', false).html(origText);
            alert(i18n.error);
        });
    });

    // 2. Batch PDF Scanner
    $('#btn-run-pdf-scan, #btn-quick-scan-pdf').on('click', function () {
        const btn = $(this);
        const isQuick = (btn.attr('id') === 'btn-quick-scan-pdf');
        btn.prop('disabled', true).text(i18n.scanning);

        const progressWrap = $('#eaaguard-pdf-progress-wrap');
        const progressBar = $('#eaaguard-pdf-progress-bar');
        const progressTxt = $('#eaaguard-pdf-progress-text');
        const progressPct = $('#eaaguard-pdf-progress-pct');

        progressWrap.slideDown();
        progressBar.css('width', '25%');
        progressPct.text('25%');
        progressTxt.text(isPl ? 'Analiza binarna plików PDF...' : 'Binary stream inspection of PDF files...');

        $.post(config.ajaxUrl, {
            action: 'eaaguard_batch_scan_pdf',
            nonce: config.nonce
        }, function (res) {
            progressBar.css('width', '100%');
            progressPct.text('100%');
            progressTxt.text(i18n.scanDone);

            if (res.success && res.data) {
                const stats = res.data.stats;
                const results = res.data.results || [];

                // Update KPI Cards
                $('#kpi-total-pdfs').text(stats.total);
                $('#kpi-compliant-pdfs').text(stats.compliant);
                $('#kpi-warning-pdfs').text(stats.warnings);
                $('#kpi-violation-pdfs').text(stats.violations);
                $('#kpi-compliance-pct').text(stats.compliance_pct + '%');

                // Render Table Rows
                const tbody = $('#eaaguard-pdf-tbody');
                if (results.length > 0) {
                    let html = '';
                    results.forEach(function (r, idx) {
                        const issues = r.issues || [];
                        const issueTip = issues.join('\n• ');
                        let statusPill = '';

                        if (r.status === 'compliant') {
                            statusPill = `<span class="eaaguard-pill eaaguard-pill-compliant">${i18n.compliant}</span>`;
                        } else if (r.status === 'warning') {
                            statusPill = `<span class="eaaguard-pill eaaguard-pill-warning" title="${escapeHtml(issueTip)}">${i18n.warning}</span>`;
                        } else {
                            statusPill = `<span class="eaaguard-pill eaaguard-pill-violation" title="${escapeHtml(issueTip)}">${i18n.violation}</span>`;
                        }

                        html += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td><strong>${escapeHtml(r.file_name)}</strong><br><small style="color:var(--flx-text-muted);">${escapeHtml(r.file_path)}</small></td>
                                <td>${formatBytes(r.file_size)}</td>
                                <td>${r.is_tagged ? '<span class="eaaguard-badge-ok">✅ Tagged</span>' : `<span class="eaaguard-badge-warn">⚠️ ${isPl ? 'Brak tagów' : 'Untagged'}</span>`}</td>
                                <td>${r.has_ocr ? `<span class="eaaguard-badge-ok">✅ OCR ${isPl ? 'Tekst' : 'Text'}</span>` : `<span class="eaaguard-badge-err">❌ ${isPl ? 'Skan bitmapowy' : 'Bitmap Scan'}</span>`}</td>
                                <td>${escapeHtml(r.language || '—')}</td>
                                <td>${statusPill}</td>
                                <td><small>${escapeHtml(r.checked_at)}</small></td>
                                <td>
                                    ${issues.length ? `<button type="button" class="button button-small" onclick="alert('${escapeJs(i18n.issueDetails)}\\n\\n${escapeJs(issues.join('\\n'))}')">ℹ️ ${isPl ? 'Błędy' : 'Issues'}</button>` : `<span style="color:#059669;font-size:12px;">${i18n.noIssues}</span>`}
                                </td>
                            </tr>
                        `;
                    });
                    tbody.html(html);
                }

                setTimeout(function () {
                    progressWrap.slideUp();
                    btn.prop('disabled', false).text(isQuick ? (isPl ? '🚀 Skanuj Bibliotekę Mediów (PDF)' : '🚀 Scan Media Library (PDFs)') : i18n.btnRunPdfScan);

                    // If quick scan was clicked from Overview, switch to PDF tab so user immediately sees files
                    if (isQuick) {
                        switchTab('tab-pdf');
                    }
                }, 800);
            } else {
                alert((isPl ? 'Błąd skanowania mediów: ' : 'Media scan error: ') + (res.data ? res.data.message : 'Unknown error'));
                btn.prop('disabled', false).text(i18n.btnRunPdfScan);
            }
        }).fail(function () {
            alert(i18n.error);
            btn.prop('disabled', false).text(i18n.btnRunPdfScan);
        });
    });

    // 3. Posts & Pages Scanner
    $('#btn-scan-posts, #btn-quick-scan-content').on('click', function () {
        const btn = $(this);
        const isQuick = (btn.attr('id') === 'btn-quick-scan-content');
        btn.prop('disabled', true).text(i18n.auditingPosts);

        $.post(config.ajaxUrl, {
            action: 'eaaguard_scan_posts',
            nonce: config.nonce
        }, function (res) {
            btn.prop('disabled', false).text(isQuick ? (isPl ? '🌐 Audytuj Wpisy i Strony' : '🌐 Audit Posts & Pages') : i18n.btnScanPosts);
            if (res.success && res.data && res.data.results) {
                const results = res.data.results;
                const tbody = $('#eaaguard-posts-tbody');

                if (results.length > 0) {
                    let html = '';
                    results.forEach(function (r) {
                        const passBadge = r.passed
                            ? `<span class="eaaguard-pill eaaguard-pill-compliant">${i18n.compliant}</span>`
                            : `<span class="eaaguard-pill eaaguard-pill-violation">${i18n.violation} (${r.violations_count})</span>`;

                        html += `
                            <tr>
                                <td>${r.item_id}</td>
                                <td><strong>${escapeHtml(r.title)}</strong></td>
                                <td><span class="eaaguard-type-tag">${escapeHtml(r.context)}</span></td>
                                <td>${passBadge}</td>
                                <td>${r.violations_count}</td>
                                <td><small>${escapeHtml(r.checked_at)}</small></td>
                                <td>
                                    <a href="${escapeHtml(r.edit_url)}" class="button button-small">
                                        ✏️ ${isPl ? 'Edytuj' : 'Edit'}
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.html(html);
                }

                if (isQuick) {
                    switchTab('tab-pages');
                }
            } else {
                alert((isPl ? 'Błąd audytu wpisów: ' : 'Posts audit error: ') + (res.data ? res.data.message : 'Unknown error'));
            }
        }).fail(function () {
            btn.prop('disabled', false).text(i18n.btnScanPosts);
            alert(i18n.error);
        });
    });

    // 4. URL Live Auditor
    $('#btn-audit-url-submit').on('click', function () {
        const btn = $(this);
        const url = $('#eaaguard-audit-url-input').val().trim();
        const resultBox = $('#eaaguard-url-result');

        if (!url) {
            alert(isPl ? 'Wprowadź prawidłowy adres URL do zbadania.' : 'Please enter a valid URL to audit.');
            return;
        }

        btn.prop('disabled', true).text(i18n.auditingUrl);
        resultBox.html('<div style="padding:15px;color:var(--flx-text-muted);">' + i18n.auditingUrl + '</div>').slideDown();

        $.post(config.ajaxUrl, {
            action: 'eaaguard_audit_url',
            url: url,
            nonce: config.nonce
        }, function (res) {
            btn.prop('disabled', false).text(i18n.btnAuditUrl);

            if (res.success && res.data) {
                const d = res.data;
                const scoreColor = d.score >= 90 ? '#059669' : (d.score >= 70 ? '#d97706' : '#dc2626');

                let html = `
                    <div style="background:var(--flx-bg-card);border:1px solid var(--flx-border);border-radius:8px;padding:20px;color:var(--flx-text-main);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--flx-border);padding-bottom:12px;">
                            <div>
                                <h4 style="margin:0;color:var(--flx-text-main);font-size:16px;">${escapeHtml(d.title || url)}</h4>
                                <small style="color:var(--flx-text-muted);">${escapeHtml(d.url)}</small>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:28px;font-weight:800;color:${scoreColor};">${d.score} / 100</div>
                                <span class="eaaguard-pill ${d.passed ? 'eaaguard-pill-compliant' : 'eaaguard-pill-violation'}">
                                    ${d.passed ? i18n.passedWcag : i18n.failedWcag}
                                </span>
                            </div>
                        </div>

                        <div style="display:flex;gap:16px;margin-bottom:16px;font-size:13px;">
                            <span><strong>${i18n.critErrors}</strong> <span style="color:#dc2626;font-weight:bold;">${d.counts.critical}</span></span>
                            <span><strong>${i18n.warnings}</strong> <span style="color:#d97706;font-weight:bold;">${d.counts.warning}</span></span>
                            <span><strong>${i18n.notices}</strong> <span style="color:#2563eb;font-weight:bold;">${d.counts.notice}</span></span>
                        </div>
                `;

                if (d.violations && d.violations.length > 0) {
                    html += `<h5 style="margin:16px 0 8px 0;color:var(--flx-text-main);">${i18n.issuesDetected}</h5><ul style="margin:0 0 16px 20px;font-size:13px;">`;
                    d.violations.forEach(function (v) {
                        const isCrit = v.severity === 'critical';
                        html += `
                            <li style="margin-bottom:8px;">
                                <span style="color:${isCrit ? '#dc2626' : '#d97706'};font-weight:bold;">[${escapeHtml(v.criterion)}]</span>
                                <span style="color:var(--flx-text-main);">${escapeHtml(v.message)}</span>
                                <div style="color:#0284c7;font-size:12px;">💡 ${escapeHtml(v.suggestion)}</div>
                            </li>
                        `;
                    });
                    html += '</ul>';
                } else {
                    html += `<p style="color:#059669;font-weight:bold;">${i18n.noIssuesDetected}</p>`;
                }

                if (d.outline && d.outline.length > 0) {
                    html += `<h5 style="margin:16px 0 8px 0;color:var(--flx-text-main);">${i18n.headingTree}</h5><div style="background:var(--flx-bg-subtle);border:1px solid var(--flx-border);padding:12px;border-radius:6px;font-size:12px;font-family:monospace;color:var(--flx-text-main);">`;
                    d.outline.forEach(function (o) {
                        const indent = '&nbsp;'.repeat((o.level - 1) * 4);
                        html += `<div>${indent}<strong>&lt;${o.tag}&gt;</strong> ${escapeHtml(o.text)}</div>`;
                    });
                    html += '</div>';
                }

                html += '</div>';
                resultBox.html(html);
            } else {
                resultBox.html(`<div style="padding:15px;background:#fee2e2;border:1px solid #ef4444;color:#991b1b;border-radius:6px;">Error: ${escapeHtml(res.data ? res.data.error : 'Unknown error')}</div>`);
            }
        }).fail(function () {
            btn.prop('disabled', false).text(i18n.btnAuditUrl);
            resultBox.html(`<div style="padding:15px;background:#fee2e2;border:1px solid #ef4444;color:#991b1b;border-radius:6px;">${i18n.error}</div>`);
        });
    });

    // 5. Declaration Settings Form
    $('#eaaguard-declaration-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const statusEl = $('#eaaguard-decl-status');
        statusEl.text(i18n.saving).css('color', '#2563eb');

        const data = form.serializeArray();
        data.push({ name: 'action', value: 'eaaguard_save_settings' });
        data.push({ name: 'nonce', value: config.nonce });

        $.post(config.ajaxUrl, data, function (res) {
            if (res.success) {
                statusEl.text('✅ ' + i18n.saved).css('color', '#059669');
                setTimeout(() => location.reload(), 800);
            } else {
                statusEl.text('❌ ' + (res.data ? res.data.message : 'Error')).css('color', '#dc2626');
            }
        });
    });

    // 6. Create Declaration Page Button
    $('#btn-create-decl-page').on('click', function () {
        const btn = $(this);
        const statusEl = $('#eaaguard-decl-status');
        btn.prop('disabled', true);
        statusEl.text(i18n.creating).css('color', '#2563eb');

        $.post(config.ajaxUrl, {
            action: 'eaaguard_create_declaration_page',
            nonce: config.nonce
        }, function (res) {
            btn.prop('disabled', false);
            if (res.success && res.data) {
                statusEl.html(`✅ ${escapeHtml(res.data.message)} <a href="${res.data.url}" target="_blank" style="margin-left:8px;color:#2563eb;text-decoration:underline;">${i18n.openPage}</a>`).css('color', '#059669');
            } else {
                statusEl.text('❌ ' + (res.data ? res.data.message : 'Error')).css('color', '#dc2626');
            }
        });
    });

    // 7. Widget Form
    $('#eaaguard-widget-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const statusEl = $('#eaaguard-widget-status');
        statusEl.text(i18n.saving).css('color', '#2563eb');

        const data = form.serializeArray();
        data.push({ name: 'action', value: 'eaaguard_save_settings' });
        data.push({ name: 'has_widget_form', value: '1' });
        data.push({ name: 'nonce', value: config.nonce });

        $.post(config.ajaxUrl, data, function (res) {
            if (res.success) {
                statusEl.text('✅ ' + i18n.saved).css('color', '#059669');
                setTimeout(() => statusEl.text(''), 2500);
            } else {
                statusEl.text('❌ ' + (res.data ? res.data.message : 'Error')).css('color', '#dc2626');
            }
        });
    });

    // 8. License Form
    $('#eaaguard-license-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const statusEl = $('#eaaguard-license-status');
        statusEl.text(i18n.licVerifying).css('color', '#2563eb');

        const data = form.serializeArray();
        data.push({ name: 'action', value: 'eaaguard_save_settings' });
        data.push({ name: 'nonce', value: config.nonce });

        $.post(config.ajaxUrl, data, function (res) {
            if (res.success) {
                statusEl.text(i18n.licSaved).css('color', '#059669');
                setTimeout(() => location.reload(), 1000);
            } else {
                statusEl.text('❌ ' + (res.data ? res.data.message : 'Error')).css('color', '#dc2626');
            }
        });
    });

    // Helper functions
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJs(str) {
        return String(str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    function formatBytes(bytes) {
        bytes = parseInt(bytes, 10);
        if (isNaN(bytes) || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
});
