/**
 * FlexExt Company Lookup for GUS - Frontend Checkout Autofill
 * Vanilla JavaScript implementation for WooCommerce Checkout (Classic & Blocks)
 *
 * @version 1.0.3
 * @author FlexExt Dev
 */

(function () {
    'use strict';

    const flexcoloCheckout = {
        config: {
            restUrl: '/wp-json/flexcolo/v1/lookup',
            nonce: '',
            auto_lookup_digits: true,
            is_pl: false,
            i18n: {
                btnDefault: 'Fetch Company Data',
                btnLoading: 'Loading...',
                emptyNip: 'Please enter a valid NIP before fetching data.',
                successTitle: 'Company details fetched successfully!',
                digits_error: 'Polish NIP must consist of exactly 10 digits.',
                invalid_nip: 'Invalid Modulo 11 tax ID checksum.',
                not_found: 'Company not found in registry.',
                network_error: 'Error connecting to NIP verification server.'
            }
        },

        isLoading: false,
        lastLookedUpNip: '',
        debounceTimer: null,

        init() {
            this.syncConfig();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.boot());
            } else {
                this.boot();
            }
        },

        syncConfig() {
            const externalConfig = window.flexcolo_checkout_config || {};
            this.config = Object.assign({}, this.config, externalConfig);
            if (externalConfig.rest_url && !externalConfig.restUrl) {
                this.config.restUrl = externalConfig.rest_url;
            }
            if (externalConfig.i18n) {
                this.config.i18n = Object.assign({}, this.config.i18n, externalConfig.i18n);
            }
        },

        boot() {
            this.syncConfig();
            this.bindEvents();
            this.observeDynamicDom();

            if (window.jQuery) {
                window.jQuery(document.body).on('updated_checkout', () => {
                    this.syncConfig();
                    this.bindEvents();
                });
            }
        },

        getElements() {
            const nipInput = document.querySelector('#billing_vat_nip') ||
                             document.querySelector('input[name="billing_vat_nip"]') ||
                             document.querySelector('[id$="billing_vat_nip"]');

            const lookupBtn = document.querySelector('#flexcolo-lookup-btn') ||
                              document.querySelector('.flexcolo-lookup-btn');

            const statusMsg = document.querySelector('#flexcolo-status-msg') ||
                              document.querySelector('.flexcolo-status-msg');

            const countrySelect = document.querySelector('#billing_country') ||
                                  document.querySelector('select[name="billing_country"]') ||
                                  document.querySelector('[id$="billing_country"]');

            let companyInput = document.querySelector('#billing_company') ||
                               document.querySelector('input[name="billing_company"]') ||
                               document.querySelector('[id$="billing_company"]');

            if (!companyInput && nipInput) {
                const fieldRow = nipInput.closest('.form-row') || nipInput.closest('.wc-block-components-text-input') || nipInput.parentElement;
                if (fieldRow && fieldRow.parentElement) {
                    const isPl = this.config.is_pl || (document.documentElement.lang || '').indexOf('pl') === 0;
                    const compP = document.createElement('p');
                    compP.className = 'form-row form-row-wide';
                    compP.id = 'billing_company_field';
                    compP.setAttribute('data-priority', '20');
                    compP.innerHTML =
                        '<label for="billing_company" class="">' + (isPl ? 'Nazwa firmy' : 'Company name') + '&nbsp;<span class="optional">(optional)</span></label>' +
                        '<span class="woocommerce-input-wrapper">' +
                        '<input type="text" class="input-text" name="billing_company" id="billing_company" placeholder="' + (isPl ? 'Nazwa firmy (pobierana automatycznie po NIP)' : 'Company name (autofilled from NIP)') + '" value="" autocomplete="organization" />' +
                        '</span>';
                    fieldRow.parentElement.insertBefore(compP, fieldRow);
                    companyInput = compP.querySelector('#billing_company');
                }
            }

            const address1Input = document.querySelector('#billing_address_1') ||
                                  document.querySelector('input[name="billing_address_1"]') ||
                                  document.querySelector('[id$="billing_address_1"]');

            const postcodeInput = document.querySelector('#billing_postcode') ||
                                  document.querySelector('input[name="billing_postcode"]') ||
                                  document.querySelector('[id$="billing_postcode"]');

            const cityInput = document.querySelector('#billing_city') ||
                              document.querySelector('input[name="billing_city"]') ||
                              document.querySelector('[id$="billing_city"]');

            return {
                nipInput,
                lookupBtn,
                statusMsg,
                countrySelect,
                companyInput,
                address1Input,
                postcodeInput,
                cityInput
            };
        },

        bindEvents() {
            const els = this.getElements();

            if (els.nipInput && !els.nipInput.dataset.flexcoloBound) {
                els.nipInput.dataset.flexcoloBound = 'true';

                els.nipInput.addEventListener('input', (e) => {
                    this.handleNipInput(e.target.value);
                });

                els.nipInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.executeLookup();
                    }
                });

                els.nipInput.addEventListener('blur', () => {
                    if (!this.config.auto_lookup_digits) {
                        return;
                    }
                    const clean = this.cleanNip(els.nipInput.value);
                    const digits = clean.replace(/\D/g, '');
                    if (digits.length === 10 && clean !== this.lastLookedUpNip) {
                        this.executeLookup();
                    }
                });
            }

            if (els.lookupBtn && !els.lookupBtn.dataset.flexcoloBound) {
                els.lookupBtn.dataset.flexcoloBound = 'true';
                els.lookupBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.executeLookup();
                });
            }
        },

        observeDynamicDom() {
            const observer = new MutationObserver(() => {
                const els = this.getElements();
                if (els.nipInput && !els.nipInput.dataset.flexcoloBound) {
                    this.bindEvents();
                    this.ensureButtonInjected(els.nipInput);
                }
            });

            const target = document.querySelector('.woocommerce') ||
                           document.querySelector('.wc-block-checkout') ||
                           document.body;

            if (target) {
                observer.observe(target, { childList: true, subtree: true });
            }
        },

        ensureButtonInjected(nipInput) {
            if (!nipInput) return;
            const container = nipInput.closest('.form-row') ||
                              nipInput.closest('.wc-block-components-text-input') ||
                              nipInput.parentElement;

            if (container && !container.querySelector('#flexcolo-lookup-btn')) {
                const isPl = this.config.is_pl || (document.documentElement.lang || '').indexOf('pl') === 0;
                const btnLabel = this.config.i18n.btnDefault || (isPl ? 'Pobierz dane po NIP' : 'Fetch Company Data');
                const hintLabel = isPl ? 'Biała Lista MF (PL)' : 'Polish Ministry of Finance (MF) Registry';

                const wrapper = document.createElement('div');
                wrapper.className = 'flexcolo-action-wrapper';

                wrapper.innerHTML =
                    '<div class="flexcolo-action-row">' +
                    '<button type="button" class="button button-secondary flexcolo-lookup-btn" id="flexcolo-lookup-btn">' +
                    '<span class="flexcolo-btn-icon" aria-hidden="true">🔍</span>' +
                    '<span class="flexcolo-btn-text">' + this.escapeHtml(btnLabel) + '</span>' +
                    '<span class="flexcolo-spinner" style="display:none;" aria-hidden="true">⏳</span>' +
                    '</button>' +
                    '<small class="flexcolo-hint">' + this.escapeHtml(hintLabel) + '</small>' +
                    '</div>' +
                    '<div class="flexcolo-status-msg" id="flexcolo-status-msg"></div>';

                container.appendChild(wrapper);
                this.bindEvents();
            }
        },

        handleNipInput(val) {
            const els = this.getElements();
            if (els.statusMsg) {
                els.statusMsg.style.display = 'none';
            }

            if (!this.config.auto_lookup_digits) {
                return;
            }

            const clean = this.cleanNip(val);
            const digits = clean.replace(/\D/g, '');
            const country = this.getSelectedCountry();

            if (country === 'PL' && digits.length === 10 && clean !== this.lastLookedUpNip) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.executeLookup();
                }, 400);
            }
        },

        getSelectedCountry() {
            const els = this.getElements();
            if (els.countrySelect) {
                return (els.countrySelect.value || 'PL').toUpperCase();
            }
            return 'PL';
        },

        cleanNip(nip) {
            let cleaned = (nip || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            if (cleaned.indexOf('PL') === 0) {
                cleaned = cleaned.substring(2);
            }
            return cleaned;
        },

        async executeLookup() {
            if (this.isLoading) {
                return;
            }

            this.syncConfig();
            const els = this.getElements();
            if (!els.nipInput) {
                return;
            }

            const rawNip = els.nipInput.value.trim();
            const clean = this.cleanNip(rawNip);

            if (!clean) {
                this.showStatus('error', this.config.i18n.emptyNip || 'Please enter a valid NIP before fetching data.');
                els.nipInput.focus();
                return;
            }

            const isPl = this.config.is_pl || (document.documentElement.lang || '').indexOf('pl') === 0;
            const digits = clean.replace(/\D/g, '');

            if (digits.length !== 10) {
                this.showStatus('error', this.config.i18n.digits_error || (isPl ? 'Polski NIP musi składać się dokładnie z 10 cyfr.' : 'Polish NIP must consist of exactly 10 digits.'));
                return;
            }

            if (!this.validatePolishNipChecksum(digits)) {
                this.showStatus('error', this.config.i18n.invalid_nip || (isPl ? 'Wprowadzony numer NIP ma nieprawidłową sumę kontrolną.' : 'Invalid Modulo 11 tax ID checksum.'));
                return;
            }

            this.setLoading(true);

            try {
                const response = await fetch(this.config.restUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.config.nonce
                    },
                    body: JSON.stringify({
                        nip: digits
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    const errorMsg = data.message || this.config.i18n.not_found || (isPl ? 'Nie odnaleziono firmy w rejestrze MF.' : 'Company not found in registry.');
                    this.showStatus('error', errorMsg);
                    return;
                }

                this.fillCheckoutFields(data);
                this.lastLookedUpNip = digits;

                const street = data.street || (data.address && data.address.street) || '';
                const postcode = data.postcode || (data.address && data.address.postcode) || '';
                const city = data.city || (data.address && data.address.city) || '';
                const successTitle = this.config.i18n.success || (isPl ? 'Dane firmy pobrane pomyślnie!' : 'Company details fetched successfully!');

                const htmlMsg =
                    '<div style="display:flex; flex-direction:column; gap:2px;">' +
                    '<div style="font-weight:700; display:flex; align-items:center; gap:6px;">' +
                    '<span>✅</span> <span>' + this.escapeHtml(successTitle) + '</span>' +
                    '</div>' +
                    '<div style="font-weight:700; color:#0f172a; margin-top:2px; font-size:13px;">' + this.escapeHtml(data.name || '') + '</div>' +
                    '<div style="color:#334155; font-size:11px;">' +
                    this.escapeHtml(street) + (street && (postcode || city) ? ', ' : '') + this.escapeHtml(postcode) + ' ' + this.escapeHtml(city) +
                    '</div>' +
                    '</div>';

                this.showStatus('success', htmlMsg, true);

            } catch (err) {
                this.showStatus('error', this.config.i18n.network_error || (isPl ? 'Wystąpił błąd podczas łączenia z serwerem weryfikacji NIP.' : 'Error connecting to NIP verification server.'));
            } finally {
                this.setLoading(false);
            }
        },

        fillCheckoutFields(data) {
            const els = this.getElements();
            const street = data.street || (data.address && data.address.street) || '';
            const postcode = data.postcode || (data.address && data.address.postcode) || '';
            const city = data.city || (data.address && data.address.city) || '';

            if (data.name && els.companyInput) {
                this.setFieldValue(els.companyInput, data.name);
            }

            if (street && els.address1Input) {
                this.setFieldValue(els.address1Input, street);
            }

            if (postcode && els.postcodeInput) {
                this.setFieldValue(els.postcodeInput, postcode);
            }

            if (city && els.cityInput) {
                this.setFieldValue(els.cityInput, city);
            }

            this.triggerWooCommerceUpdate();
        },

        setFieldValue(element, value) {
            if (!element) return;

            const prototype = Object.getPrototypeOf(element);
            const descriptor = Object.getOwnPropertyDescriptor(prototype, 'value');

            if (descriptor && descriptor.set) {
                descriptor.set.call(element, value);
            } else {
                element.value = value;
            }

            element.dispatchEvent(new Event('input', { bubbles: true }));
            element.dispatchEvent(new Event('change', { bubbles: true }));

            if (window.jQuery) {
                window.jQuery(element).trigger('input').trigger('change');
            }
        },

        triggerWooCommerceUpdate() {
            if (window.jQuery) {
                window.jQuery(document.body).trigger('update_checkout');
            }
        },

        validatePolishNipChecksum(nip) {
            if (!/^\d{10}$/.test(nip)) {
                return false;
            }

            const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
            let sum = 0;

            for (let i = 0; i < 9; i++) {
                sum += parseInt(nip[i], 10) * weights[i];
            }

            const control = sum % 11;
            if (control === 10) {
                return false;
            }

            return control === parseInt(nip[9], 10);
        },

        setLoading(loading) {
            this.isLoading = loading;
            const els = this.getElements();

            if (els.lookupBtn) {
                els.lookupBtn.disabled = loading;
                const textSpan = els.lookupBtn.querySelector('.flexcolo-btn-text');
                const spinner = els.lookupBtn.querySelector('.flexcolo-spinner');
                const icon = els.lookupBtn.querySelector('.flexcolo-btn-icon');

                if (loading) {
                    if (textSpan) textSpan.textContent = this.config.i18n.btnLoading || 'Loading...';
                    if (spinner) spinner.style.display = 'inline-block';
                    if (icon) icon.style.display = 'none';
                } else {
                    if (textSpan) textSpan.textContent = this.config.i18n.btnDefault || 'Fetch Company Data';
                    if (spinner) spinner.style.display = 'none';
                    if (icon) icon.style.display = 'inline-block';
                }
            }

            if (els.nipInput) {
                els.nipInput.readOnly = loading;
            }
        },

        showStatus(type, message, isHtml = false) {
            const els = this.getElements();
            if (!els.statusMsg) return;

            els.statusMsg.className = 'flexcolo-status-msg flexcolo-' + type;
            els.statusMsg.style.display = 'block';

            if (isHtml) {
                els.statusMsg.innerHTML = message;
            } else {
                const icon = type === 'error' ? '⚠️ ' : (type === 'warning' ? 'ℹ️ ' : '✅ ');
                els.statusMsg.textContent = icon + message;
            }
        },

        escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };

    flexcoloCheckout.init();
    window.flexcoloCheckout = flexcoloCheckout;
})();
