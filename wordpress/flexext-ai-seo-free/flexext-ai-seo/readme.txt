=== FlexExt AI SEO & Agent Readiness ===
Contributors: flexext, pawelagit
Donate link: https://flexext.dev/
Tags: ai seo, llms txt, robots txt, chatgpt, ai crawlers
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Optimize your WordPress site for AI search engines and LLMs (ChatGPT, Claude, Perplexity) with standard /llms.txt and robots.txt AI crawler control.

== Description ==

**FlexExt AI SEO & Agent Readiness** prepares your WordPress website for generative AI search engines and Large Language Models.

As search habits expand toward AI answer engines (ChatGPT Search, Anthropic Claude, Perplexity, Google Gemini), your website benefits from structured machine-readable summaries and explicit crawler access directives. This plugin generates the open **`/llms.txt` proposal standard** and gives you fine-grained control over AI bot crawler permissions via dynamic `robots.txt` rules.

### Key Features

* **Standard `/llms.txt` Generator:** Dynamically serves a clean, curated Markdown overview of your website and primary pages at `/llms.txt` for Large Language Models.
* **Smart Robots.txt AI Bot Manager:** One-click toggles allowing or blocking specific AI crawlers (`GPTBot`, `OAI-SearchBot`, `ChatGPT-User`, `ClaudeBot`, `PerplexityBot`, `Google-Extended`, `Applebot-Extended`, `Meta-ExternalAgent`, `CCBot`).
* **AI Agent Readiness Audit:** Built-in admin scorecard checking your website's discoverability for generative AI crawlers.
* **Lightweight & Zero External Requests:** Uses native WordPress APIs with zero external HTTP calls and zero frontend performance overhead.
* **Bilingual Admin Interface (EN / PL):** Automatically adapts to English and Polish WordPress locales.

### Informacje po polsku / Polish Summary

Wtyczka **FlexExt AI SEO & Agent Readiness** optymalizuje witrynę WordPress pod kątem wyszukiwarek AI i modeli językowych (ChatGPT, Claude, Perplexity, Google Gemini). Automatycznie generuje ustrukturyzowany plik `/llms.txt` w formacie Markdown oraz pozwala jednym kliknięciem zezwalać lub blokować wybrane boty AI w wirtualnym pliku `robots.txt`.

### Looking for Advanced WooCommerce & Agent Protocols?

For stores and publishers needing full-catalog Markdown indexing and autonomous agent protocols, **FlexExt AI SEO & Agent Readiness PRO** is available at [flexext.dev](https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro) with:
* Full token-dense Markdown knowledge base and WooCommerce product catalog (`/llms-full.txt`)
* Dynamic per-post and per-product Markdown conversion (`?format=md` and `Accept: text/markdown`)
* WebMCP, A2A Agent Card (`.well-known/agent-card.json`), and RFC 9727 API Catalog endpoints
* Real-time AI crawler visit analytics and IndexNow auto-ping

== Installation ==

1. Upload the `flexext-ai-seo` folder to `/wp-content/plugins/`, or install the `.zip` archive via **Plugins > Add New Plugin > Upload Plugin**.
2. Activate **FlexExt AI SEO & Agent Readiness** through the **Plugins** menu in WordPress.
3. Configure crawler permissions and `/llms.txt` settings under **AI SEO & LLMs.txt** in your WordPress admin menu.

== Frequently Asked Questions ==

= Does this plugin require an API key or registration? =
No. The plugin is 100% open-source under GPLv2 and works out of the box without any API keys or registration.

= Where can I view the generated /llms.txt file? =
Once enabled, visit `https://your-domain.com/llms.txt` to inspect the generated Markdown summary.

= Does this plugin send any data to external servers? =
No. All `/llms.txt` generation and `robots.txt` filtering are performed 100% locally on your WordPress server.

== Screenshots ==

1. AI Bot Management dashboard controlling crawler access in robots.txt.
2. The `/llms.txt` configuration tab.
3. The AI Agent Readiness audit scorecard.

== Changelog ==

= 1.0.2 =
* Standardized all global constants, option names, and nonces to the `FLEXAISEO_` / `flexaiseo_` prefix for WordPress.org Plugin Directory compliance.
* Removed unused classes and redundant `load_plugin_textdomain()` call.
* Removed promotional note from public `/llms.txt` output in accordance with WordPress.org Guideline 10.
* Unified `BotManager::getTrackedBots()` namespace and crawler dictionary.

= 1.0.1 =
* Verified compatibility with WordPress 7.1 and PHP 8.3.
* Added bilingual EN/PL admin interface support.

= 1.0.0 =
* Initial public release.
