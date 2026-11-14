# FlexExt EAA & WCAG Guard Lite for Craft CMS 4 & 5

Free open-source **European Accessibility Act (EAA / EN 301 549) & WCAG 2.2 AA** starter audit plugin for **Craft CMS 4.5+ and Craft CMS 5.x**.

## Included Free Features (MIT License)

- **Control Panel Entry HTML Scanner:** Audits up to 25 published Craft Entries on demand for missing `<img>` `alt` attributes, empty anchor links (`<a>`), and skipped `H1–H6` heading levels.
- **Public `/accessibility-statement` Endpoint:** Automatically publishes a clean, WCAG AA Light/Dark accessible compliance statement page with your organization's accessibility contact email.
- **Zero Storefront Overhead:** Adds zero JavaScript bloat to your public pages.

## Installation

```bash
composer require flexext/flexext-eaa-guard-lite
php craft plugin/install flexext-eaa-guard-lite
```

## Need Binary PDF/UA Asset Auditing, 1-Click Auto-Fix & Save Gatekeeper?

Upgrade to **[FlexExt EAA & WCAG 2.2 Guard Pro](https://flexext.dev/en-gb/product/craft-cms-eaa-wcag-accessibility-guard-pro)** for:
- **Binary PDF/UA & OCR Stream Auditor:** Scans `%PDF-1.x` assets across Craft Volumes for `/StructTreeRoot` tags, `/Lang`, `/Title`, and flags scanned image-only PDFs missing an OCR text layer
- **Strict Publish Gatekeeper (`EVENT_BEFORE_SAVE`):** Optionally blocks editors from publishing entries with critical WCAG 2.2 AA violations
- **1-Click HTML Auto-Fix Engine:** Automatically repairs heading hierarchy jumps, `target="_blank"` `rel` attributes, and decorative image attributes
- **Storefront WCAG Reader Toolbar:** Accessible typography, spacing, and contrast controls
- Live Craft 5 Sandbox Demo: [https://demo.flexext.dev/craft/](https://demo.flexext.dev/craft/)
