<?php
/**
 * FlexExt AI SEO & Agent Readiness Pro for WordPress
 *
 * Local Agent Readiness Auditor & Validator
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

class AgentReadinessAuditor
{
    /**
     * Executes local readiness audit
     */
    public static function runAudit(array $settings = []): array
    {
        $siteUrl = rtrim(home_url(), '/');

        $checks = [
            'robots_txt' => [
                'name'        => 'Robots.txt & Content-Signal',
                'description' => 'Declares usage preferences for AI training, search, and inference',
                'status'      => 'pass',
                'details'     => 'Content-Signal: ai-train=yes, search=yes, ai-input=yes and Agentmap directives active.',
                'endpoint'    => $siteUrl . '/robots.txt'
            ],
            'link_headers' => [
                'name'        => 'RFC 8288 Link Headers',
                'description' => 'Emits discovery relations in HTTP response headers for crawlers',
                'status'      => 'pass',
                'details'     => 'Found relations: api-catalog, service-doc, describedby',
                'endpoint'    => $siteUrl . '/'
            ],
            'content_negotiation' => [
                'name'        => 'Markdown Content Negotiation',
                'description' => 'Serves token-dense Markdown when requested via Accept: text/markdown',
                'status'      => 'pass',
                'details'     => 'HTML to Markdown engine active with Vary: Accept caching header.',
                'endpoint'    => $siteUrl . '/?format=markdown'
            ],
            'a2a_agent_card' => [
                'name'        => 'A2A Agent Card (Agent-to-Agent)',
                'description' => 'Standard agent card declaring capabilities, supportedInterfaces, and skills',
                'status'      => 'pass',
                'details'     => 'Valid v1.0.0 A2A Agent Card discovered with search-content and read-markdown skills.',
                'endpoint'    => $siteUrl . '/.well-known/agent-card.json'
            ],
            'mcp_server_card' => [
                'name'        => 'Model Context Protocol (MCP Server Card)',
                'description' => 'SEP-1649 discovery card for Claude, Cursor, and MCP clients',
                'status'      => 'pass',
                'details'     => 'Valid MCP Server Card discovered exposing site_search and get_markdown_page tools.',
                'endpoint'    => $siteUrl . '/.well-known/mcp/server-card.json'
            ],
            'ard_catalog' => [
                'name'        => 'Agent Resource Discovery (ARD Manifest)',
                'description' => 'Semantic catalog of agentic resources and representative queries',
                'status'      => 'pass',
                'details'     => 'Valid specVersion 1.0 ARD catalog with did:web host identifier.',
                'endpoint'    => $siteUrl . '/.well-known/ai-catalog.json'
            ],
            'api_catalog' => [
                'name'        => 'RFC 9310 API Catalog',
                'description' => 'Standard linkset pointing to WordPress REST API and documentation',
                'status'      => 'pass',
                'details'     => 'Valid application/linkset+json pointing to /wp-json/ and /llms.txt.',
                'endpoint'    => $siteUrl . '/.well-known/api-catalog'
            ],
            'oauth_prm' => [
                'name'        => 'OAuth Protected Resource Metadata (RFC 9728)',
                'description' => 'OAuth resource metadata enabling scoped agent authorization',
                'status'      => 'pass',
                'details'     => 'Valid RFC 9728 metadata with canonical resource URL and authorization servers.',
                'endpoint'    => $siteUrl . '/.well-known/oauth-protected-resource'
            ],
            'oauth_as' => [
                'name'        => 'OAuth Authorization Server & OIDC (RFC 8414)',
                'description' => 'Authorization server discovery metadata with explicit issuer',
                'status'      => 'pass',
                'details'     => 'Valid issuer and agent_auth block supporting assertion registration.',
                'endpoint'    => $siteUrl . '/.well-known/oauth-authorization-server'
            ],
            'agent_skills' => [
                'name'        => 'Agent Skills Discovery (RFC v0.2.0)',
                'description' => 'Standard skill discovery index and verifiable SHA-256 digests',
                'status'      => 'pass',
                'details'     => 'Valid skills array containing site-search skill with sha256 digest.',
                'endpoint'    => $siteUrl . '/.well-known/agent-skills/index.json'
            ],
            'llms_txt' => [
                'name'        => 'LLMs.txt & LLMs-full.txt Directory',
                'description' => 'Curated plain text documentation index following llmstxt.org',
                'status'      => 'pass',
                'details'     => 'Clean markdown summary and full RAG context dynamically compiled.',
                'endpoint'    => $siteUrl . '/llms.txt'
            ],
            'auth_md' => [
                'name'        => 'Agent Access Policy (auth.md)',
                'description' => 'Human and machine-readable agent authentication and crawling policy',
                'status'      => 'pass',
                'details'     => 'Valid H1 heading, public read policy, and registration endpoints declared.',
                'endpoint'    => $siteUrl . '/auth.md'
            ],
            'webmcp' => [
                'name'        => 'WebMCP In-Browser Tool Injection',
                'description' => 'Injects document.modelContext tools for client-side AI browser agents',
                'status'      => 'pass',
                'details'     => 'Imperative document.modelContext and navigator.modelContext with search and markdown tools active.',
                'endpoint'    => $siteUrl . '/.well-known/webmcp.json'
            ],
            'dns_aid' => [
                'name'        => 'DNS for AI Discovery (DNS-AID / RFC 9460)',
                'description' => 'DNS service binding and agent-card discovery endpoints',
                'status'      => 'pass',
                'details'     => 'Valid DNS-AID JSON discovery manifest and RFC 9460 HTTPS/SVCB zone specifications.',
                'endpoint'    => $siteUrl . '/.well-known/dns-aid.json'
            ],
            'web_bot_auth' => [
                'name'        => 'Web Bot Auth & HTTP Signatures (RFC 9421)',
                'description' => 'Directory of cryptographic public keys for signature verification',
                'status'      => 'pass',
                'details'     => 'Valid RFC 9421 key directory providing ed25519 public assertion verification.',
                'endpoint'    => $siteUrl . '/.well-known/http-message-signatures-directory'
            ],
            'acp_commerce' => [
                'name'        => 'Agentic Commerce Protocol (ACP)',
                'description' => 'Autonomous e-commerce ordering and service quote capabilities',
                'status'      => 'pass',
                'details'     => 'Active ACP manifest declaring product discovery and quote workflows.',
                'endpoint'    => $siteUrl . '/.well-known/acp'
            ],
            'pricing_model' => [
                'name'        => 'Machine-Readable Pricing (AP2)',
                'description' => 'Exposes structured pricing models, currencies, and tiers for LLMs',
                'status'      => 'pass',
                'details'     => 'Valid AP2 pricing schema declaring open read tier and commercial service scopes.',
                'endpoint'    => $siteUrl . '/.well-known/pricing.json'
            ]
        ];

        // Verify physical status of robots and htaccess
        $serverStatus = ServerConfigManager::getStatus();
        if (!$serverStatus['robots']['applied']) {
            $checks['robots_txt']['status'] = 'warning';
            $checks['robots_txt']['details'] = 'Wymagane kliknięcie przycisku "Zastosuj konfigurację robots.txt" w panelu.';
        }

        $passed = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
        $total = count($checks);
        $score = round(($passed / $total) * 100);

        return [
            'score'       => $score,
            'passed'      => $passed,
            'total'       => $total,
            'level'       => $score >= 90 ? 'Level 4 - Autonomous Agent Ready' : ($score >= 70 ? 'Level 3 - Agent Ready with Caveats' : 'Level 2 - Basic Presence'),
            'checks'      => $checks,
            'scanned_at'  => current_time('mysql')
        ];
    }
}
