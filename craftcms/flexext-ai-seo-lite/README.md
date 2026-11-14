# FlexExt AI SEO & llms.txt Lite for Craft CMS 4 & 5

Lightweight, zero-bloat AI Search & GEO discovery starter plugin for **Craft CMS 4.5+ and Craft CMS 5.x**.

## Included Free Features (MIT License)

- **Dynamic `/llms.txt` Endpoint:** Automatically generates a clean Markdown index of up to 50 live Craft CMS Entries for the active site handle (`OAI-SearchBot`, `ClaudeBot`, `PerplexityBot`, `Google-Extended`).
- **HTTP Discovery Header:** Injects `Link: </llms.txt>; rel="llms-txt"` on public HTML responses so AI crawlers discover your Markdown index automatically.
- **Base `Schema.org` JSON-LD Graph:** Injects clean `WebSite` + `Organization` JSON-LD metadata on public HTML pages when not already present.
- **Control Panel Preview & Scanner Shortcut:** Live `/llms.txt` preview inside Craft CP plus 1-click verification via the free [FlexExt AI Website Checker](https://aichecker.flexext.dev/).

## Installation

```bash
composer require flexext/flexext-ai-seo-lite
php craft plugin/install flexext-ai-seo-lite
```

## Need Full AI Agent Negotiation, MCP Server Cards & Bot Analytics?

Upgrade to **[FlexExt AI SEO & Agent Readiness Pro](https://flexext.dev/en-gb/product/craft-cms-ai-seo-agent-readiness-pro)** for:
- `Accept: text/markdown` HTTP content negotiation (`Vary: Accept`) converting any Craft HTML page into token-efficient Markdown (~80% fewer tokens)
- Full `/llms-full.txt` knowledge base export with custom field extraction
- `/.well-known/mcp/server-card.json` (Model Context Protocol) + live read-only Entry search endpoint & Google A2A `/.well-known/agent-card.json`
- Real-time AI Crawler Traffic Analytics & selective 403 Training Bot Blocker (14 AI crawler signatures)
- Live Craft 5 Sandbox Demo: [https://demo.flexext.dev/craft/](https://demo.flexext.dev/craft/)
