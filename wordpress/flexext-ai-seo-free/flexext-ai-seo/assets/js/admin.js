/**
 * FlexExt AI SEO & Agent Readiness Pro - Admin Scripts
 */
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.flx-tab-btn');
    const tabPanes = document.querySelectorAll('.flx-tab-pane');

    function switchTab(targetId) {
        tabButtons.forEach(btn => {
            if (btn.getAttribute('data-tab') === targetId) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        tabPanes.forEach(pane => {
            if (pane.id === targetId) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        // Update URL hash without jumping
        if (history.replaceState) {
            history.replaceState(null, null, '#' + targetId);
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-tab');
            if (targetId) {
                switchTab(targetId);
            }
        });
    });

    // Check if initial hash is present
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const exists = document.getElementById(hash);
        if (exists && exists.classList.contains('flx-tab-pane')) {
            switchTab(hash);
        }
    }

    // Copy to clipboard handler
    document.querySelectorAll('.flx-btn-copy').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const text = this.getAttribute('data-copy');
            if (!text) return;

            navigator.clipboard.writeText(text).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '✓ Skopiowano';
                this.style.background = '#16a34a';
                this.style.color = '#ffffff';
                setTimeout(() => {
                    this.innerHTML = orig;
                    this.style.background = '';
                    this.style.color = '';
                }, 2000);
            }).catch(() => {
                // Fallback prompt
                prompt('Skopiuj wartość ręcznie:', text);
            });
        });
    });

    // Live DNS Test Handler
    const testDnsBtn = document.getElementById('flx-btn-test-dns');
    const resultsContainer = document.getElementById('flx-dns-test-results');

    if (testDnsBtn && resultsContainer && typeof flxAiSeoData !== 'undefined') {
        testDnsBtn.addEventListener('click', function () {
            testDnsBtn.disabled = true;
            testDnsBtn.innerHTML = '⏳ Odpytywanie Google DoH...';
            resultsContainer.style.display = 'block';
            resultsContainer.innerHTML = '<div style="padding:14px; text-align:center; color:var(--flx-text-muted);">Wysyłanie zapytań DNS-over-HTTPS do globalnych serwerów Google (8.8.8.8)...</div>';

            const formData = new FormData();
            formData.append('action', 'flx_ai_seo_test_dns');
            formData.append('nonce', flxAiSeoData.nonce);

            fetch(flxAiSeoData.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                testDnsBtn.disabled = false;
                testDnsBtn.innerHTML = '🔄 Sprawdź ponownie DNS &rarr;';

                if (!data.success || !data.data) {
                    resultsContainer.innerHTML = '<div class="flx-alert flx-alert-danger">Błąd podczas pobierania danych DNS: ' + (data.data?.message || 'Nieznany błąd') + '</div>';
                    return;
                }

                const res = data.data;
                let html = '<div style="margin-bottom:12px; font-weight:700; color:var(--flx-text-main);">Wyniki testu DNS-over-HTTPS (Google DoH):</div>';

                for (const key in res) {
                    const item = res[key];
                    const isPass = item.status === 'pass';
                    const badgeClass = isPass ? 'pass' : 'fail';
                    const statusText = isPass ? '✓ PASS (AKTYWNY)' : '✗ FAIL (BRAK)';
                    const answersHtml = (item.answers && item.answers.length > 0)
                        ? '<div style="margin-top:4px; font-family:monospace; font-size:11px; color:var(--flx-primary); word-break:break-all;">' + item.answers.join('<br>') + '</div>'
                        : '';

                    html += '<div class="flx-dns-res-item ' + badgeClass + '">' +
                                '<div>' +
                                    '<strong>' + item.label + '</strong> &mdash; <code>' + item.name + '</code> (' + item.type + ')' +
                                    '<div style="font-size:12px; color:var(--flx-text-muted); margin-top:2px;">' + item.message + '</div>' +
                                    answersHtml +
                                '</div>' +
                                '<span class="flx-dns-res-badge ' + badgeClass + '">' + statusText + '</span>' +
                            '</div>';
                }

                resultsContainer.innerHTML = html;
            })
            .catch(err => {
                testDnsBtn.disabled = false;
                testDnsBtn.innerHTML = '🔄 Sprawdź ponownie DNS &rarr;';
                resultsContainer.innerHTML = '<div class="flx-alert flx-alert-danger">Błąd połączenia: ' + err.message + '</div>';
            });
        });
    }

    // Live Agent Readiness Audit Handler
    const runAuditBtn = document.getElementById('flx-btn-run-audit');
    const auditContainer = document.getElementById('flx-live-audit-results');

    if (runAuditBtn && auditContainer && typeof flxAiSeoData !== 'undefined') {
        runAuditBtn.addEventListener('click', function () {
            runAuditBtn.disabled = true;
            runAuditBtn.innerHTML = '⏳ Wykonywanie 20 testów live...';
            auditContainer.style.display = 'block';
            auditContainer.innerHTML = '<div style="padding:24px; text-align:center; color:var(--flx-text-muted); background:var(--flx-card-bg); border:1px solid var(--flx-card-border); border-radius:8px;">' +
                '<div style="font-size:24px; margin-bottom:10px;">🧪</div>' +
                '<strong>Testowanie punktów końcowych w pętli zwrotnej (loopback HTTP)...</strong><br>' +
                '<span style="font-size:12px;">Wysyłanie bezpośrednich żądań GET/HEAD do wszystkich 20 protokołów AI...</span>' +
                '</div>';

            const formData = new FormData();
            formData.append('action', 'flx_ai_seo_run_full_audit');
            formData.append('nonce', flxAiSeoData.nonce);

            fetch(flxAiSeoData.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                runAuditBtn.disabled = false;
                runAuditBtn.innerHTML = '🔄 Uruchom ponownie test live &rarr;';

                if (!data.success || !data.data) {
                    auditContainer.innerHTML = '<div class="flx-alert flx-alert-danger">Błąd podczas wykonywania audytu: ' + (data.data?.message || 'Nieznany błąd') + '</div>';
                    return;
                }

                const res = data.data;
                const score = res.score || 0;
                const passed = res.passed || 0;
                const total = res.total || 0;
                const checks = res.checks || [];

                let html = '<div class="flx-card" style="border:2px solid var(--flx-primary);">' +
                    '<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:16px;">' +
                        '<div>' +
                            '<h3 style="margin:0 0 4px 0;">🎯 Wynik Diagnostyki Live: ' + score + '% (' + passed + '/' + total + ' zaliczonych)</h3>' +
                            '<p style="margin:0; font-size:13px; color:var(--flx-text-muted);">Bezpośrednia weryfikacja kodów odpowiedzi HTTP, zawartości protokołów oraz latencji w milisekundach.</p>' +
                        '</div>' +
                        '<div><span class="flx-badge ' + (score >= 90 ? 'flx-badge-success' : 'flx-badge-warning') + '" style="font-size:14px; padding:6px 14px;">' + res.level + '</span></div>' +
                    '</div>' +
                    '<div class="flx-audit-grid">';

                checks.forEach(c => {
                    const isPass = c.status === 'pass';
                    const badgeClass = isPass ? 'pass' : 'fail';
                    const statusText = isPass ? '✓ PASS (' + (c.http_code || 200) + ')' : '✗ FAIL (' + (c.http_code || 'ERR') + ')';
                    const latencyText = c.latency_ms ? (c.latency_ms + ' ms') : '';

                    html += '<div class="flx-audit-card ' + badgeClass + '">' +
                        '<div class="flx-audit-card-main">' +
                            '<div style="display:flex; align-items:center; gap:8px;">' +
                                '<strong>' + c.name + '</strong>' +
                                (latencyText ? '<span class="flx-latency-badge">' + latencyText + '</span>' : '') +
                            '</div>' +
                            '<div style="font-size:12px; color:var(--flx-text-muted); margin-top:3px;">' + c.details + '</div>' +
                            '<div style="margin-top:4px;"><a href="' + c.url + '" target="_blank" class="flx-code-link">' + c.url + '</a></div>' +
                        '</div>' +
                        '<span class="flx-dns-res-badge ' + badgeClass + '">' + statusText + '</span>' +
                    '</div>';
                });

                html += '</div></div>';
                auditContainer.innerHTML = html;
            })
            .catch(err => {
                runAuditBtn.disabled = false;
                runAuditBtn.innerHTML = '🔄 Uruchom ponownie test live &rarr;';
                auditContainer.innerHTML = '<div class="flx-alert flx-alert-danger">Błąd połączenia z serwerem: ' + err.message + '</div>';
            });
        });
    }

    // Bot Firewall Policy Toggle Handler
    document.querySelectorAll('.flx-bot-policy-select').forEach(sel => {
        sel.addEventListener('change', function () {
            const botKey = this.getAttribute('data-bot');
            const policy = this.value;
            const toast = document.getElementById('flx-bot-policy-toast');

            if (!botKey || typeof flxAiSeoData === 'undefined') return;

            this.disabled = true;

            const formData = new FormData();
            formData.append('action', 'flx_ai_seo_save_bot_policy');
            formData.append('bot_key', botKey);
            formData.append('policy', policy);
            formData.append('nonce', flxAiSeoData.nonce);

            fetch(flxAiSeoData.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success && toast) {
                    toast.style.display = 'inline-block';
                    toast.innerHTML = '✓ Zapisano regułę: ' + botKey + ' &rarr; ' + policy.toUpperCase();
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 3500);
                }
            })
            .catch(() => {
                this.disabled = false;
            });
        });
    });
});
