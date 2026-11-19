# European Accessibility Act (EAA) & WCAG 2.2 AA: Catching Accessibility Bugs Before Editors Hit Publish

> **Summary:** Why post-publish accessibility overlays fail legal audits — and how real-time DOM & contrast guardrails inside CMS editors prevent WCAG 2.2 violations at the source.
>
> 🔗 **Original Article:** [https://flexext.dev/en-gb/blog/european-accessibility-act-eaa-wcag-22-compliance-joomla-wordpress](https://flexext.dev/en-gb/blog/european-accessibility-act-eaa-wcag-22-compliance-joomla-wordpress) | 🌐 **Official Store:** [https://flexext.dev](https://flexext.dev) | 🛒 **Joomla Store:** [https://flexext.store](https://flexext.store) | 🔍 **Free AI Scanner:** [https://aichecker.flexext.dev](https://aichecker.flexext.dev)

---

Even when an agency delivers a 100/100 Lighthouse Accessibility storefront on launch day, **85% of WCAG 2.2 AA regressions are introduced within the next 90 days by everyday content editing**:

1. A marketer uploads a promotional image with an empty or filename `alt="IMG_4092.jpg"`.
2. Someone pastes `<span style="color: #666666">` inside a card that turns `#1e293b` in Dark Mode — dropping contrast to an unreadable `1.8:1`.
3. Product tabs use `<h4>` inside an `<h2>` section, breaking screen-reader heading navigation.
4. Custom links say *"Click here"* without descriptive context.

Floating JavaScript "overlay toolbar widgets" cannot guess the semantic meaning of a product diagram or repair broken checkout form labels. Under the **European Accessibility Act (EAA / EN 301 549)**, compliance has to live in the actual HTML and CSS.

Here are the three highest-impact engineering patterns we enforce across every template and plugin at [FlexExt.dev](https://flexext.dev/).

---

## 1. Never Set `color` Without a Paired `background` Token

The #1 source of unreadable UI in modern CMS admin panels and storefronts is mixing hardcoded Bootstrap utility classes (`bg-white`, `text-dark`, `text-muted`) with OS or theme Dark Mode (`[data-bs-theme="dark"]` or `@media (prefers-color-scheme: dark)`).

Instead, define **paired surface + foreground CSS custom properties** verified against WCAG 2.2 AA (`≥ 4.5:1` for normal text, `≥ 3.0:1` for large text and UI boundaries):

```css
:root {
  --surface-card: #ffffff;
  --text-primary: #0f172a;   /* 17.85:1 on #ffffff */
  --text-secondary: #475569; /*  7.58:1 on #ffffff */
  --btn-primary-bg: #0369a1; /*  5.93:1 with #ffffff */
  --btn-primary-fg: #ffffff;
}

[data-bs-theme="dark"] {
  --surface-card: #0f172a;
  --text-primary: #f8fafc;   /* 16.92:1 on #0f172a */
  --text-secondary: #cbd5e1; /* 11.45:1 on #0f172a */
  --btn-primary-bg: #0284c7;
  --btn-primary-fg: #ffffff;
}
```

And if merchants can pick a custom badge or banner hex color in the database, compute foreground text dynamically from relative WCAG luminance:

```php
function readableTextColor(string $hexBg): string {
    $hex = ltrim($hexBg, '#');
    [$r, $g, $b] = [
        hexdec(substr($hex, 0, 2)) / 255,
        hexdec(substr($hex, 2, 2)) / 255,
        hexdec(substr($hex, 4, 2)) / 255,
    ];
    $lin = array_map(fn($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4, [$r, $g, $b]);
    $lum = 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
    return $lum > 0.179 ? '#0f172a' : '#ffffff';
}
```

---

## 2. Guard Content Inside the CMS Editor Before Publish

Catching WCAG violations *while* an author is editing an article or product takes 10 seconds to fix; catching them in an external audit 6 months later costs thousands.

Inside the editor (Gutenberg / TinyMCE / Joomla Content & Product forms), a lightweight real-time DOM observer can immediately flag:
- `<img>` elements missing `alt` attributes (unless explicitly decorative `role="presentation"`),
- skipped heading levels (`<h1>` -> `<h4>`),
- empty links/buttons without `aria-label` or visible text,
- low-contrast inline `style` combinations,
- tables missing `<th>` headers.

---

## Free Tools & Native EAA Guard Plugins

We released **free community editions** and **Pro suites** of our **WCAG 2.2 & EAA Accessibility Guard** with real-time Gutenberg/TinyMCE/CKEditor checking, PDF/UA auditing, and automated `/accessibility-statement` generation:

- **Full EAA & WCAG 2.2 Engineering Guide on FlexExt.dev:** [https://flexext.dev/en-gb/blog/european-accessibility-act-eaa-wcag-22-compliance-joomla-wordpress](https://flexext.dev/en-gb/blog/european-accessibility-act-eaa-wcag-22-compliance-joomla-wordpress)
- **WordPress Real-Time WCAG 2.2 & EAA Editor Guard Pro:** [https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress](https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress)
- **Joomla 4/5/6 Real-Time WCAG 2.2 & EAA Editor Guard Pro:** [https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla](https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla)
- **Joomla Web Accessibility & EAA Guard (Free Edition):** [https://flexext.dev/en-gb/product/web-accessibility-eaa-guard-free-joomla](https://flexext.dev/en-gb/product/web-accessibility-eaa-guard-free-joomla)
- **Craft CMS 4 & 5 EAA & WCAG Accessibility Guard Pro:** [https://flexext.dev/en-gb/product/craft-cms-eaa-wcag-accessibility-guard-pro](https://flexext.dev/en-gb/product/craft-cms-eaa-wcag-accessibility-guard-pro)
- **Free Live Technical & Readiness Audit:** [https://aichecker.flexext.dev/](https://aichecker.flexext.dev/)
