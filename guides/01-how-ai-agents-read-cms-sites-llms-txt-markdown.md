# How AI Search & Shopping Agents Actually Crawl CMS Sites (/llms.txt, Accept: text/markdown & MCP)

> **Summary:** What 30 days of production server logs from GPTBot, ClaudeBot, and Meta-ExternalAgent taught us — and how to serve clean Markdown and Schema.org graphs on WordPress, Joomla, OpenCart, and Craft CMS.
>
> 🔗 **Original Article:** [https://flexext.dev/en-gb/blog/generative-engine-optimization-geo-llms-txt-rfc-8288-joomla-wordpress-opencart](https://flexext.dev/en-gb/blog/generative-engine-optimization-geo-llms-txt-rfc-8288-joomla-wordpress-opencart) | 🌐 **Official Store:** [https://flexext.dev](https://flexext.dev) | 🛒 **Joomla Store:** [https://flexext.store](https://flexext.store) | 🔍 **Free AI Scanner:** [https://aichecker.flexext.dev](https://aichecker.flexext.dev)

---

Over the past 30 days on our own extension store infrastructure, we analyzed every verified bot request hitting production. The numbers surprised even us:

- **Googlebot / Google Crawler:** `378` verified requests
- **OpenAI (`GPTBot` + `OAI-SearchBot`):** `377` requests (`+ 7` direct human click-throughs from `ChatGPT-User`)
- **Meta AI External Agent:** `187` requests
- **Bingbot:** `87` requests
- **Anthropic `ClaudeBot`:** `26` requests

AI answer engines and shopping assistants are no longer an edge experiment — they are already half of your technical crawl budget. Yet when an LLM agent fetches a typical WordPress, Joomla, OpenCart, or Craft CMS page, it receives **300–600 KB of mega-menus, SVGs, CSS classes, and modal markup** before reaching the first paragraph of product specifications.

Here is the 4-layer engineering checklist we use across [FlexExt.dev](https://flexext.dev/) and [FlexExt Store](https://flexext.store/) to make PHP CMS and e-commerce sites 100% readable to AI agents without hurting human UX or full-page caches.

---

## 1. Stop Accidentally Blocking Search Citation Bots in `robots.txt`

Many site owners copied blanket `robots.txt` blocks in 2024 that block *both* model-training scrapers and real-time search citation crawlers (`OAI-SearchBot`, `ChatGPT-User`, `PerplexityBot`). If `OAI-SearchBot` is disallowed, ChatGPT Search cannot cite or link to your product pages.

A clean 2026 `robots.txt` policy explicitly allows search/citation agents and points crawlers to both your XML sitemap and `/llms.txt`:

```txt
User-agent: OAI-SearchBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: ClaudeBot
Allow: /

Sitemap: https://example.com/sitemap.xml
# AI Discovery Index: https://example.com/llms.txt
```

---

## 2. Publish `/llms.txt` and `/llms-full.txt`

Following the `llms.txt` specification, exposing a concise Markdown map at `https://yourdomain.com/llms.txt` gives AI agents a curated, hallucination-free index of your documentation, pricing tiers, canonical URLs, and APIs in under 2,000 tokens.

Make sure your web server serves it with explicit UTF-8 Markdown headers:

```apache
<Files "llms.txt">
    ForceType "text/markdown; charset=utf-8"
    Header set Content-Type "text/markdown; charset=utf-8"
</Files>
```

---

## 3. Support `Accept: text/markdown` Content Negotiation (Without Breaking Page Cache!)

Modern AI agents and RAG pipelines increasingly send `Accept: text/markdown` when fetching URLs. Returning a clean Markdown representation of your product or article (title, SKU, live price, stock availability, specs, and FAQs) cuts token noise by **80–90%**.

Two critical rules when implementing this in PHP (WordPress, Joomla, OpenCart, Craft CMS):
1. **Always emit `Vary: Accept`** so Cloudflare, Varnish, or native full-page caches never serve Markdown to a human Chrome browser (or cached HTML to an agent).
2. Bypass HTML minifiers/caches when `str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'text/markdown')` is true.

```php
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (str_contains($accept, 'text/markdown') || ($_GET['format'] ?? '') === 'markdown') {
    header('Content-Type: text/markdown; charset=utf-8');
    header('Vary: Accept');
    echo $markdownRenderer->renderProduct($product);
    exit;
}
```

---

## 4. Ship Complete `Schema.org` Graphs & Agent Discovery Cards

When shopping assistants compare software or physical products, they cross-check visible copy against structured JSON-LD:
- `Product` + `Offer` (`price`, `priceCurrency`, `availability`, `url`, `sku`)
- `FAQPage` (10–15 real buyer questions and answers)
- `/.well-known/mcp/server-card.json` and `/.well-known/agent-card.json` for Model Context Protocol (MCP) and Google A2A discovery.

---

## Test Your Own Site in 3 Seconds (Free 25-Check Live Scanner)

To avoid manually running `curl` checks on every client store, we built a **free, zero-signup technical scanner** that runs 25 live HTTP probes across 6 AI & GEO Readiness pillars:

👉 **Live Scanner:** [https://aichecker.flexext.dev/](https://aichecker.flexext.dev/)

It automatically fingerprints 45+ CMS and e-commerce stacks (WordPress, WooCommerce, Joomla 4/5/6, OpenCart 3/4, Craft CMS, Shopware, PrestaShop, Shopify) and generates a downloadable starter kit (`llms.txt`, `robots.txt`, `server-card.json`).

If you want drop-in native plugins that automate `/llms.txt`, `Accept: text/markdown`, and JSON-LD dynamically from your catalog:
- **Full GEO & AI Agent Readiness Guide:** [https://flexext.dev/en-gb/blog/generative-engine-optimization-geo-llms-txt-rfc-8288-joomla-wordpress-opencart](https://flexext.dev/en-gb/blog/generative-engine-optimization-geo-llms-txt-rfc-8288-joomla-wordpress-opencart)
- **Joomla 4/5/6 AI SEO & Agent Readiness Pro:** [https://flexext.dev/en-gb/product/flexext-ai-seo-agent-readiness-joomla](https://flexext.dev/en-gb/product/flexext-ai-seo-agent-readiness-joomla)
- **WordPress AI SEO & Agent Readiness Pro:** [https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro](https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro)
- **OpenCart Complete SEO Intelligence Suite:** [https://flexext.dev/en-gb/product/complete-seo-intelligence-suite-for-opencart](https://flexext.dev/en-gb/product/complete-seo-intelligence-suite-for-opencart)
- **Craft CMS 4 & 5 AI SEO Pro:** [https://flexext.dev/en-gb/product/craft-cms-ai-seo-agent-readiness-pro](https://flexext.dev/en-gb/product/craft-cms-ai-seo-agent-readiness-pro)
- **Native Joomla 4/5/6 E-Commerce Suite (`FlexExt Store`):** [https://flexext.store/](https://flexext.store/)
- **Free Open-Source GPL Community Plugins:** [https://github.com/flexext-dev/free](https://github.com/flexext-dev/free)
