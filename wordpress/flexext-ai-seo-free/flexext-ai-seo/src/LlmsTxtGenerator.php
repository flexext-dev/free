<?php
/**
 * FlexExt AI SEO & Agent Readiness Pro for WordPress
 *
 * Generates concise, structured /llms.txt and comprehensive /llms-full.txt
 * following the llmstxt.org specification with Smart RAG Chunking,
 * YAML Frontmatter, token estimates, and Rank Math / Yoast SEO Bridge.
 *
 * @package     FlexExt_AI_SEO
 * @author      FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 FlexExt. All rights reserved.
 * @license     GPLv2 or later
 * @link        https://flexext.dev
 */

namespace FlexExt\WordPress\AiSeo;

if (!defined('ABSPATH')) {
    exit;
}

class LlmsTxtGenerator
{
    /**
     * Estimates LLM token count from raw text (approx. 4 characters per token in English, 3 in Polish)
     */
    public static function estimateTokens(string $text): int
    {
        return (int)ceil(mb_strlen($text, 'UTF-8') / 3.5);
    }

    /**
     * Generates /llms.txt summary
     */
    public static function generateSummary(array $settings = []): string
    {
        $siteUrl = rtrim(home_url(), '/');
        $siteName = get_bloginfo('name') ?: 'WordPress Website';
        $siteSummary = !empty($settings['site_summary']) ? trim($settings['site_summary']) : (get_bloginfo('description') ?: 'Official website providing CCTV monitoring, security systems, products, and services.');

        $seoBridge = AgenticSeoBridge::getActiveSeoPlugin();

        $txt = "# {$siteName}\n\n";
        $txt .= "> {$siteSummary}\n\n";

        $txt .= "## Quick Navigation & Core Endpoints\n\n";
        $txt .= "- [Home Page]({$siteUrl}/): Main landing page.\n";
        $txt .= "- [Full Knowledge Base Context]({$siteUrl}/llms-full.txt): Comprehensive token-dense RAG knowledge feed with frontmatter and semantic chunks.\n";
        $txt .= "- [Agentic Commerce Catalog]({$siteUrl}/.well-known/acp/catalog.json): Structured product and service catalog for autonomous agents.\n";
        $txt .= "- [Machine-Readable Pricing]({$siteUrl}/.well-known/pricing.json): AP2 pricing tiers and inquiry endpoints.\n";
        $txt .= "- [API Catalog]({$siteUrl}/.well-known/api-catalog): RFC 9310 Linkset.\n";
        $txt .= "- [A2A Agent Card]({$siteUrl}/.well-known/agent-card.json): Agent capabilities and skills declaration.\n";
        $txt .= "- [MCP Server Card]({$siteUrl}/.well-known/mcp/server-card.json): Model Context Protocol tools definition.\n";
        $txt .= "- [Agent Access Policy]({$siteUrl}/auth.md): Authentication and polite crawler rate limits.\n\n";

        // Query published posts & pages
        $posts = function_exists('get_posts') ? get_posts([
            'post_type'      => ['post', 'page', 'service', 'cctv_product'],
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]) : [];

        if (!empty($posts)) {
            $txt .= "## Key Articles & Service Offerings\n\n";
            foreach ($posts as $p) {
                $seoData = AgenticSeoBridge::getPostSeoData($p->ID);
                $title = $seoData['title'];
                $url = $seoData['canonical_url'];
                $desc = $seoData['meta_description'];
                $kw = !empty($seoData['focus_keywords']) ? ' (Keywords: ' . implode(', ', array_slice($seoData['focus_keywords'], 0, 3)) . ')' : '';

                $txt .= "- [{$title}]({$url}): {$desc}{$kw}\n";
            }
            $txt .= "\n";
        }

        $txt .= "## Agent Directives & Capabilities\n\n";
        $txt .= "- **Content Negotiation:** This website natively supports `Accept: text/markdown` or `?format=markdown` on all URLs.\n";
        $txt .= "- **Lead Inquiries:** Autonomous agents can submit service quote requests directly via POST `{$siteUrl}/api/agent/inquiry`.\n";
        $txt .= "- **Active SEO Engine:** Integrated with {$seoBridge['name']}.\n";
        $txt .= "- **DNS Discovery:** Configured with RFC 9460 HTTPS and SVCB records for `_index._agents`.\n";

        return $txt;
    }

    /**
     * Generates /llms-full.txt full markdown context with YAML Frontmatter and RAG chunks
     */
    public static function generateFullContext(array $settings = []): string
    {
        $siteUrl = rtrim(home_url(), '/');
        $siteName = get_bloginfo('name') ?: 'WordPress Website';
        $siteDesc = get_bloginfo('description') ?: 'Full knowledge base and articles';
        $seoBridge = AgenticSeoBridge::getActiveSeoPlugin();

        $txt = "# {$siteName} — Full LLM Context & Knowledge Base\n\n";
        $txt .= "> High-density semantic knowledge base compiled for Large Language Model (LLM) context windows, RAG pipelines, and autonomous AI agents.\n\n";

        $posts = function_exists('get_posts') ? get_posts([
            'post_type'      => ['post', 'page', 'service', 'cctv_product'],
            'post_status'    => 'publish',
            'posts_per_page' => 40,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]) : [];

        $toc = "## Table of Contents\n\n";
        $contentBodies = "";

        foreach ($posts as $idx => $p) {
            $seoData = AgenticSeoBridge::getPostSeoData($p->ID);
            $title = $seoData['title'];
            $url = $seoData['canonical_url'];
            $date = get_the_date('Y-m-d', $p);
            $rawContent = $p->post_content;

            // Convert to clean Markdown
            $mdContent = HtmlToMarkdown::convert($rawContent, [], false);
            $tokenEst = self::estimateTokens($mdContent);

            $anchor = 'doc-' . ($idx + 1) . '-' . sanitize_title($title);
            $toc .= ($idx + 1) . ". [{$title}](#{$anchor}) — {$tokenEst} est. tokens\n";

            $keywordsList = !empty($seoData['focus_keywords']) ? json_encode(array_values($seoData['focus_keywords']), JSON_UNESCAPED_UNICODE) : '[]';

            $contentBodies .= "<a id=\"{$anchor}\"></a>\n\n";
            $contentBodies .= "```yaml\n";
            $contentBodies .= "document_id: " . $p->ID . "\n";
            $contentBodies .= "title: \"" . addcslashes($title, '"') . "\"\n";
            $contentBodies .= "url: \"" . $url . "\"\n";
            $contentBodies .= "category: \"" . addcslashes($seoData['category'], '"') . "\"\n";
            $contentBodies .= "keywords: " . $keywordsList . "\n";
            $contentBodies .= "published_date: \"" . $date . "\"\n";
            $contentBodies .= "estimated_tokens: " . $tokenEst . "\n";
            $contentBodies .= "seo_source: \"" . addcslashes($seoBridge['name'], '"') . "\"\n";
            $contentBodies .= "```\n\n";

            $contentBodies .= "## {$title}\n\n";
            if (!empty($seoData['meta_description'])) {
                $contentBodies .= "> **Summary:** " . $seoData['meta_description'] . "\n\n";
            }
            $contentBodies .= $mdContent . "\n\n";
            $contentBodies .= "---\n\n";
        }

        return $txt . $toc . "\n\n" . $contentBodies;
    }
}
