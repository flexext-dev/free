=== FlexExt Web Accessibility & EAA Guard ===
Contributors: flexext, pawelagit
Donate link: https://flexext.dev/
Tags: wcag, eaa, accessibility, toolbar, contrast
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend WCAG 2.2 accessibility toolbar (font resize, high contrast, link underlining) and automated missing image alt attribute checker.

== Description ==

**FlexExt Web Accessibility & EAA Guard** provides your website visitors with essential on-site accessibility controls and helps editors identify missing image alternative text (WCAG 1.1.1 Non-text Content).

It renders a lightweight, customizable floating accessibility trigger button (`♿`) on your website. When opened, visitors can effortlessly scale text size (100%, 120%, 140%), activate high-contrast dark mode, and underline all hyperlinks.

### Key Features

* **Visitor Accessibility Toolbar:** Floating trigger widget (`♿`) positioned at the bottom-left or bottom-right corner of the screen.
* **Instant Text Resizing:** One-click font scaling for visually impaired users (100%, 120%, 140%).
* **High Contrast / Dark Mode:** Enhances text readability against bright backgrounds.
* **Hyperlink Highlighting:** Clearly underlines and highlights all clickable links across the page.
* **Smart LocalStorage Memory:** Remembers visitor accessibility preferences across page reloads without cookies.
* **Missing Image Alt Auditor on Post Save:** Automatically checks posts, pages, and products when saved and lists items with missing image `alt` attributes in the admin dashboard.
* **Bilingual Interface (EN / PL):** Automatically adapts to English and Polish WordPress locales.

### Informacje po polsku / Polish Summary

Wtyczka **FlexExt Web Accessibility & EAA Guard** udostępnia odwiedzającym pływający pasek ułatwień dostępu WCAG 2.2 (zmiana rozmiaru czcionki 100%/120%/140%, wysoki kontrast, podkreślanie odnośników) oraz automatycznie wykrywa brakujące opisy alternatywne (`alt`) obrazków podczas zapisywania wpisów i stron.

### Looking for Real-Time Gutenberg Auditing & EAA 2025 Auto-Remediation?

For organizations requiring full European Accessibility Act (EAA 2025) and WCAG 2.2 AA editor enforcement, **FlexExt Web Accessibility & EAA Guard PRO** is available at [flexext.dev](https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress) with:
* Real-time Gutenberg and Classic Editor live sidebar accessibility inspector
* Mathematical WCAG 1.4.3 AA color contrast analyzer and pre-publish blocker
* One-click Auto-Remediation engine (automatic contrast enhancement to 4.5:1 and ARIA synthesis)
* Binary PDF/UA accessibility document tagger and Statutory EAA 2025 Accessibility Statement generator

== Installation ==

1. Upload the `flexext-wcag-eaa-guard` folder to `/wp-content/plugins/`, or install the `.zip` archive via **Plugins > Add New Plugin > Upload Plugin**.
2. Activate **FlexExt Web Accessibility & EAA Guard** through the **Plugins** menu in WordPress.
3. Configure toolbar position and inspect missing alt attributes under **WCAG Guard** in your WordPress admin menu.

== Frequently Asked Questions ==

= Does this plugin require an API key or registration? =
No. The plugin is 100% open-source under GPLv2 and works out of the box without any API keys or registration.

= Does this plugin alter my saved post content? =
No. The plugin only inspects saved post HTML for missing `alt` attributes and reports them in the admin dashboard so authors can add descriptions.

= Does the frontend accessibility toolbar use tracking cookies? =
No. Visitor preferences (font size, contrast, link underlining) are stored strictly in the browser's local `localStorage` and never leave the user's device.

== Screenshots ==

1. Frontend WCAG 2.2 accessibility toolbar widget opened on a page.
2. Admin configuration panel for the on-page accessibility toolbar.
3. Missing image alt attributes audit report in the WordPress admin dashboard.

== Changelog ==

= 1.0.2 =
* Standardized all global constants, option names, meta keys, transients, and nonces to the `FLEXEAAG_` / `flexeaag_` prefix for WordPress.org Plugin Directory compliance.
* Replaced PHP 8.0 `str_starts_with()` with `strpos()` for full PHP 7.4 compatibility.
* Removed external link from the frontend visitor accessibility widget in accordance with WordPress.org Guideline 10.
* Removed unused asset files and redundant `load_plugin_textdomain()` call.

= 1.0.1 =
* Verified compatibility with WordPress 7.1 and PHP 8.3.
* Added bilingual EN/PL interface support.

= 1.0.0 =
* Initial public release.
