/**
 * FlexExt Donations for Przelewy24 (Lite) - Widget Frontend Script
 */

document.addEventListener('DOMContentLoaded', function () {
    const widgets = document.querySelectorAll('.fextl-widget');
    if (!widgets.length) return;

    const config = window.fextlConfig || {};
    const api = config.restUrl || '';
    const nonce = config.nonce || '';
    const errorGeneric = config.errorGeneric || 'Nie udało się rozpocząć płatności.';
    const errorConnection = config.errorConnection || 'Błąd połączenia. Spróbuj ponownie.';

    widgets.forEach(function (root) {
        const buttons = root.querySelectorAll('.fextl-amount-btn');
        const message = root.querySelector('.fextl-message');

        function setBusy(busy) {
            buttons.forEach(function (btn) {
                btn.disabled = busy;
            });
        }

        function pay(amount) {
            if (!amount) return;
            setBusy(true);
            if (message) {
                message.textContent = '';
                message.style.color = '#b00020';
            }

            fetch(api, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function (result) {
                setBusy(false);
                if (result.status === 200 && result.data && result.data.url) {
                    window.location.href = result.data.url;
                    return;
                }
                const err = (result.data && result.data.error) ? result.data.error : errorGeneric;
                if (message) {
                    message.textContent = err;
                }
            })
            .catch(function () {
                setBusy(false);
                if (message) {
                    message.textContent = errorConnection;
                }
            });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const amount = btn.getAttribute('data-amount');
                pay(amount);
            });
        });
    });
});
