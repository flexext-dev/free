=== FlexExt Company Lookup for GUS ===
Contributors: flexext, pawelagit
Donate link: https://flexext.dev/
Tags: nip, gus, b2b, checkout, poland
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automated Polish NIP lookup, Modulo 11 checksum validation, and company address autofill for checkout forms. HPOS ready.

== Description ==

**FlexExt Company Lookup for GUS** is a lightweight open-source plugin that streamlines B2B checkout for stores operating in Poland.

When a customer enters a 10-digit Polish Tax Identification Number (NIP) and clicks the **"Pobierz dane po NIP" (Fetch Company Data)** button (or completes typing 10 digits), the plugin validates the NIP checksum locally using the official weighted Modulo 11 algorithm, queries the public Polish Ministry of Finance registry API (`wykazpodatnikowvat.mf.gov.pl`), and automatically populates the **Company Name**, **Street Address**, **Postal Code (XX-XXX)**, and **City** fields in the checkout form.

### Key Features

* **Polish NIP Modulo 11 Validation:** Instant client-side and server-side mathematical checksum verification (`[6, 5, 7, 2, 3, 4, 5, 6, 7]`) to prevent invalid queries.
* **One-Click & Auto-Lookup Company Autofill:** Automatically populates Company Name, Street Address, Postal Code, and City in the billing form.
* **Smart Polish Address Parser:** Intelligently splits combined single-line official registry address strings into separate street, postal code (`XX-XXX`), and city fields.
* **HPOS & Checkout Blocks Ready:** Full native compatibility with High-Performance Order Storage (`custom_order_tables`) as well as classic and block-based checkout templates.
* **Order Metadata & Email Integration:** Saves the customer's NIP, registry verification status, and official request identifier in the order metadata and displays the NIP in admin order details, thank-you pages, and order emails.
* **24-Hour Transients Caching:** Caches lookup responses using the WordPress Transients API (configurable duration) to minimize external HTTP requests.
* **Built-in Diagnostics Tool:** Test live connectivity with the Polish Ministry of Finance API directly from the admin panel.
* **Pure Vanilla JavaScript:** Lightweight frontend script with zero jQuery requirement.

### Informacje po polsku / Polish Summary

Wtyczka **FlexExt Company Lookup for GUS** automatyzuje pobieranie danych polskich firm po numerze NIP w formularzu zamówienia. Waliduje sumę kontrolną 10-cyfrowego numeru NIP (algorytm wagowy Modulo 11), pobiera dane rejestrowe z oficjalnego publicznego API Wykazu Podatników VAT Ministerstwa Finansów (`wykazpodatnikowvat.mf.gov.pl`) i automatycznie uzupełnia pola: **Nazwa firmy**, **Ulica**, **Kod pocztowy** oraz **Miejscowość**. Obsługuje pamięć podręczną Transients API oraz tabele zamówień HPOS.

### Looking for EU VIES & Advanced Tax Compliance?

For stores selling internationally across the European Union or requiring extended accounting automation, a separate commercial edition (**FlexExt Auto-GUS & VIES PRO**) is available at [flexext.dev](https://flexext.dev/en-gb/product/auto-gus-mf-vies-checkout-filler-woocommerce) with:
* European Commission VIES cross-border VAT ID lookup (DE, CZ, FR, IT, ES, NL, etc.)
* Automated 0% VAT Reverse Charge application for verified EU B2B buyers
* Bank account verification against the Polish White List registry
* Batch bulk NIP & VAT verification in the admin orders list

== External services ==

This plugin connects to one external service: the public VAT taxpayer registry API of the Polish Ministry of Finance (the "White List", Wykaz podatników VAT), host `wykazpodatnikowvat.mf.gov.pl`. It is needed to look up the company name, registered address and VAT status of a Polish company by its NIP (tax identification number) and to fill in the checkout form automatically.

* **What is sent and when:** only the 10-digit NIP typed by the customer and the current date (`YYYY-MM-DD`), as an HTTPS GET request to `https://wykazpodatnikowvat.mf.gov.pl/api/search/nip/{nip}?date={YYYY-MM-DD}`. The request is made only when a customer starts a company lookup in the checkout form (button click or after the 10th digit is typed) or when an administrator runs a test query on the plugin's Diagnostics tab. Nothing is sent when the plugin is activated or when pages are loaded. No API key and no other customer data are transmitted. The server's IP address is visible to the service, as with any HTTP request.
* **Returned data:** public registry data (company name, registered address, VAT status, request identifier). It is cached in WordPress transients for the configured time and saved in the order metadata after an order is placed.
* **Provider:** Ministry of Finance of the Republic of Poland / National Revenue Administration (KAS) - [terms of use](https://www.gov.pl/web/kas/api-wykazu-podatnikow-vat) (official API description with usage conditions and limits), [privacy policy](https://www.gov.pl/web/gov/polityka-prywatnosci) and the [personal data processing notice of the Head of KAS](https://www.gov.pl/web/kas/klauzula-przetwarzania-danych-osobowych-udostepnionych-droga-elektroniczna).
* **API documentation:** [https://wykazpodatnikowvat.mf.gov.pl/api](https://wykazpodatnikowvat.mf.gov.pl/api)

== Installation ==

1. Upload the `flexext-gus-company-lookup` folder to `/wp-content/plugins/`, or install the `.zip` archive via **Plugins > Add New Plugin > Upload Plugin**.
2. Activate **FlexExt Company Lookup for GUS** through the **Plugins** menu in WordPress.
3. The NIP field and "Pobierz dane po NIP" (Fetch Company Data) button will become active in your checkout billing form.
4. Configure field options, button labels, and cache duration under **WooCommerce > Company Lookup GUS** (or **Settings > Company Lookup GUS**).

== Frequently Asked Questions ==

= Does this plugin require an API key or registration? =
No. The plugin works out of the box using the free, public REST API provided by the Polish Ministry of Finance (`wykazpodatnikowvat.mf.gov.pl`), which does not require an API key.

= Is High-Performance Order Storage (HPOS) supported? =
Yes. The plugin natively declares compatibility with `custom_order_tables` (HPOS) and Checkout Blocks.

= What happens if a customer enters an invalid NIP? =
The plugin validates the 10-digit NIP using the official Modulo 11 checksum algorithm (`[6, 5, 7, 2, 3, 4, 5, 6, 7]`) both in the browser and on the server. If the checksum is invalid, an error message is shown immediately without sending unnecessary requests to the external API.

= Where is the customer's NIP saved? =
When an order is placed, the NIP and lookup metadata (`_billing_vat_nip`, `_flexcolo_vat_nip_status`, `_flexcolo_vat_nip_request_id`, `_flexcolo_vat_nip_verified_at`) are stored in the order metadata and displayed in the admin order view, order confirmation page, and order emails.

== Screenshots ==

1. Checkout billing form showing the NIP input field and company data lookup button.
2. Successful NIP lookup with auto-populated Company Name, Street Address, Postal Code, and City.
3. Admin order view displaying the saved NIP and registry verification details.
4. Plugin settings and API diagnostics page in the WordPress admin dashboard.

== Changelog ==

= 1.0.4 =
* Switched to the official, current Ministry of Finance API host `wykazpodatnikowvat.mf.gov.pl` (the former API address is being retired by the provider on 1 December 2026).
* Rewrote the `== External services ==` section: provider, exact endpoint, what data is sent and when, and verified working links to the terms of use and privacy policy.
* Added suggested privacy policy text for site owners (Settings > Privacy).

= 1.0.3 =
* Standardized all PHP class, function, constant, option, transient, REST route, and `wp_localize_script` JavaScript object prefixes to `flexcolo_` / `FLEXCOLO_` in accordance with WordPress.org Plugin Directory guidelines.

= 1.0.2 =
* Renamed plugin to "FlexExt Company Lookup for GUS" (`flexext-gus-company-lookup`) in accordance with WordPress.org Plugin Directory naming guidelines.
* Fixed PHP 7.4 compatibility by replacing PHP 8 `str_starts_with()` calls with `strpos()`.
* Moved all inline CSS and JavaScript into properly enqueued asset files (`checkout-nip.css`, `admin-settings.css`, `admin-settings.js`).
* Removed unused PRO-only VIES endpoint code to ensure 100% of included code is unlocked and functional.
* Added complete `== External services ==` documentation with official Ministry of Finance terms and privacy policy links.
* Standardized English header description and readme documentation.

= 1.0.1 =
* Verified WordPress 7.1 and WooCommerce 9.4 compatibility.
* Added explicit textdomain loader and translation templates.
* Performance optimizations for checkout field rendering.

= 1.0.0 =
* Initial public release.
