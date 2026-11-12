/**
 * FlexExt Company Lookup for GUS - Admin Diagnostics & Settings Script
 *
 * @version 1.0.3
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var cfg = window.flexcolo_admin_config || {};

        // Cache clear confirmation
        var clearCacheBtn = document.getElementById('flexcolo-clear-cache-btn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', function (e) {
                var msg = cfg.confirmClearCache || 'Are you sure you want to clear the NIP transients cache?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        }

        // Diagnostics API test
        var btn = document.getElementById('flexcolo-diag-btn');
        var nipInput = document.getElementById('flexcolo-diag-nip');
        var resDiv = document.getElementById('flexcolo-diag-result');
        var output = document.getElementById('flexcolo-diag-output');

        if (!btn || !nipInput || !resDiv || !output) {
            return;
        }

        btn.addEventListener('click', function () {
            var nip = nipInput.value.trim();
            if (!nip) {
                window.alert(cfg.emptyNipAlert || 'Please enter a 10-digit Polish NIP number!');
                nipInput.focus();
                return;
            }

            btn.disabled = true;
            btn.textContent = cfg.testingLabel || 'Testing...';
            resDiv.style.display = 'block';
            output.textContent = cfg.queryingLabel || 'Querying Ministry of Finance registry server...';

            fetch(cfg.restUrl || '/wp-json/flexcolo/v1/lookup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': cfg.nonce || ''
                },
                body: JSON.stringify({
                    nip: nip
                })
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    output.textContent = JSON.stringify(data, null, 2);
                })
                .catch(function (err) {
                    output.textContent = 'Error: ' + (err && err.message ? err.message : 'Request failed');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.textContent = cfg.btnDefaultLabel || 'Run Diagnostic Query';
                });
        });
    });
})();
