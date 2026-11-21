# Shipping AI-Ready (/llms.txt + MCP) & EAA/WCAG 2.2 Compliant Sites on Craft CMS 4 & 5

> How to add HTTP Accept: text/markdown negotiation, Model Context Protocol discovery cards, and automated European Accessibility Act (EN 301 549) guardrails to Craft CMS 4 & 5.

Agencies building content-rich sites on **Craft CMS 4 and Craft CMS 5** love Craft for a clear reason: clean Twig templates, custom Entry types, Matrix/CKEditor flexibility, and zero opinionated frontend markup forced onto your design system.

In 2026, however, two new technical specifications have landed on almost every agency launch checklist:

1. **AI Search & Agent Readiness (GEO):** Ensuring `OAI-SearchBot`, `ClaudeBot`, `PerplexityBot`, and autonomous MCP agents receive clean Markdown, `/llms.txt` Section indexes, and `Schema.org` graphs rather than 350 KB of layout chrome.
2. **European Accessibility Act (EAA / EN 301 549) & WCAG 2.2 AA:** Preventing content editors from accidentally publishing inaccessible HTML or untagged scanned PDF attachments.

Here is how to implement both cleanly inside Craft's Yii2 event lifecycle without bloating your Twig templates.

---

## 1. Serving `/llms.txt` and `Accept: text/markdown` in Craft CMS

Instead of scattering custom routes across every project's `config/routes.php`, a Craft plugin can listen to `WebApplication::EVENT_BEFORE_REQUEST` and `Response::EVENT_AFTER_PREPARE`:

- **`/llms.txt` & `/llms-full.txt`:** Query active Craft Sections and published `Entry::find()->status('live')` elements per site handle, outputting a deterministic Markdown sitemap with canonical URLs and summaries.
- **HTTP Content Negotiation (`Accept: text/markdown`):** When an AI retrieval bot sends `Accept: text/markdown`, convert `<main>` / `<article>` semantic HTML into token-efficient Markdown and attach `Vary: Accept` plus `Link: </llms.txt>; rel="llms-txt"` headers so CDN caches (Cloudflare, Fastly) never mix HTML and Markdown responses.
- **MCP & Google A2A Discovery Cards:** Expose `/.well-known/mcp/server-card.json` and `/.well-known/agent.json` so AI agents can query published Entries through a read-only JSON endpoint rather than scraping HTML pagination.

You can benchmark how your live Craft, Joomla, WordPress, or OpenCart domain responds to these 25 protocol checks in 3 seconds using the free **[FlexExt AI Website Checker](https://aichecker.flexext.dev/)**.

---

## 2. Automated EAA / WCAG 2.2 Guardrails & PDF/UA Auditing

Passing a WCAG 2.2 AA audit on launch day is only half the battle — two weeks later, an editor uploads a scanned PDF brochure without `/StructTreeRoot` tags or skips from `<h1>` straight to `<h4>` inside a CKEditor field.

By hooking into Craft's element save lifecycle and Asset volumes, automated guardrails can inspect:
- Missing `<img>` `alt` attributes, empty links/buttons, missing table headers, and skipped `H1–H6` heading levels.
- Binary PDF assets inside Craft Volumes: checking `%PDF-1.x` headers for `/StructTreeRoot` (tagged PDF/UA), `/Lang` metadata, `/Title`, and flagging image-only scans that lack an OCR text layer.
- A live `/accessibility-statement` compliance page kept automatically in sync with your latest audit score.

---

## Useful Engineering Links & Craft CMS Plugins

- **Craft CMS AI SEO & Agent Readiness Pro:** [https://flexext.dev/en-gb/product/craft-cms-ai-seo-agent-readiness-pro](https://flexext.dev/en-gb/product/craft-cms-ai-seo-agent-readiness-pro)
- **Craft CMS EAA & WCAG Accessibility Guard Pro:** [https://flexext.dev/en-gb/product/craft-cms-eaa-wcag-accessibility-guard-pro](https://flexext.dev/en-gb/product/craft-cms-eaa-wcag-accessibility-guard-pro)
- **All Craft CMS Extensions & Live Craft 5 Demo:** [https://flexext.dev/en-gb/catalog/craft-extensions](https://flexext.dev/en-gb/catalog/craft-extensions)
- **Free 25-Check AI SEO & Agent Readiness Scanner:** [https://aichecker.flexext.dev/](https://aichecker.flexext.dev/)
- **Zero-Bloat Native E-Commerce Suite:** [https://flexext.store/](https://flexext.store/)
