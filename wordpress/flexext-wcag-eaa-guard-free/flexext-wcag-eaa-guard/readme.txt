=== FlexExt WCAG 2.2 & EAA Guard for WordPress ===
Contributors: flexext, pawelagit
Donate link: https://flexext.dev/
Tags: wcag, eaa, accessibility, accessibility guard, gutenberg, contrast, alt text, headings
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Real-time WCAG 2.2 AA & European Accessibility Act (EAA 2025) Pre-Publish Guard for Gutenberg and Classic Editor.

== Description ==

**FlexExt WCAG 2.2 & EAA Guard for WordPress** is an automated pre-publish accessibility gatekeeper designed to ensure your website content complies with the **European Accessibility Act (EAA 2025 / EN 301 549)** and **WCAG 2.2 AA standards**.

As authors and editors compose posts in the Gutenberg Block Editor or Classic Editor, the plugin actively analyzes the DOM in real-time, highlighting color contrast failures, missing image alt texts, heading sequence violations, and ambiguous link labels before content goes live.

### Key Features (100% Free):
* **Real-Time Gutenberg & Classic Editor Gatekeeper:** In-editor accessibility panel provides immediate visual feedback on accessibility issues as you type.
* **Color Contrast Checker (WCAG 1.4.3 AA):** Mathematical luminance calculation verifying 4.5:1 text-to-background contrast ratio (3:1 for large text).
* **Image Alt-Text Enforcer (WCAG 1.1.1):** Flags missing, empty, or filename-based alternative texts on embedded images.
* **Heading Hierarchy Sequence Validator (WCAG 1.3.1):** Validates structural document outline (preventing jumps from H1 directly to H3).
* **1-Click Auto-Remediation:** Instant automatic fixes for common structural flaws with a single click.
* **Optional Hard Publishing Gate:** Prevent content authors from publishing until critical accessibility errors are resolved.
* **Admin Accessibility Tester:** Built-in sandbox allowing administrators to test and auto-fix arbitrary HTML snippets.

### Free vs PRO Edition Comparison:

| Feature | Free (WordPress.org) | PRO Edition (flexext.dev) |
| :--- | :---: | :---: |
| Real-time Gutenberg & Classic editor audit | ✅ Yes | ✅ Yes |
| Color contrast analysis (WCAG 1.4.3 AA) | ✅ Yes | ✅ Yes |
| Image missing alt-text validator | ✅ Yes | ✅ Yes |
| Heading sequence hierarchy checker | ✅ Yes | ✅ Yes |
| 1-Click Auto-Remediation engine | ✅ Yes | ✅ Yes |
| Pre-publish blocking option | ✅ Yes | ✅ Yes |
| Deep Binary PDF Document Auditing (PDF/UA) | ❌ No | ✅ Yes (Pro) |
| Statutory Accessibility Statement Generator (EAA) | ❌ No | ✅ Yes (Pro) |
| Whole-Site Automated URL Crawler & Scanner | ❌ No | ✅ Yes (Pro) |
| Frontend Accessible Contrast Toolbar Widget | ❌ No | ✅ Yes (Pro) |
| Priority 1-on-1 Commercial Support & SLA | ❌ No | ✅ Yes (Pro) |

*Learn more about the PRO edition at [flexext.dev](https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla).*

== Installation ==

1. Download the `flexext-wcag-eaa-guard.zip` package.
2. In your WordPress admin dashboard, navigate to **Plugins > Add New Plugin > Upload Plugin**.
3. Select `flexext-wcag-eaa-guard.zip` and click **Install Now**.
4. Click **Activate Plugin**.
5. Configure publishing options under **WCAG & EAA Guard** in your WordPress admin menu.

== Frequently Asked Questions ==

= Does this plugin require any registration or license key? =
No. The Free edition is 100% open-source under GPLv2 and works out of the box with zero license keys or trial limitations.

= Does this plugin alter my existing content? =
No. The plugin only inspects content in the editor and reports violations to help authors fix them before publishing.

== Screenshots ==

1. Gutenberg block editor showing the real-time WCAG 2.2 accessibility guard sidebar.
2. Color contrast calculation failure with suggested compliant color palette.
3. Admin dashboard settings and instant HTML accessibility test sandbox.

== Changelog ==

= 1.0.0 =
* Initial public release in WordPress.org Plugin Directory.
* Real-time Gutenberg Block Editor and Classic Editor accessibility inspector.
* WCAG 2.2 AA contrast, alt-text, and heading sequence analyzers.
