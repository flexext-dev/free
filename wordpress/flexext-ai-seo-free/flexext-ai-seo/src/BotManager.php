<?php
/**
 * FlexExt AI SEO & Agent Readiness Pro for WordPress
 *
 * 50+ Bot Detection, Classification & Shielding Manager
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

class BotManager
{
    /**
     * Complete Bot Dictionary with Classification & Recommendations
     */
    public static function getDefaultBots(): array
    {
        return [
            // --- AI SEARCH & LLM AGENTS (RECOMMENDED / CRITICAL FOR AI SEO) ---
            [
                'bot_key'      => 'gptbot',
                'bot_name'     => 'GPTBot (OpenAI / ChatGPT)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'GPTBot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'oai_searchbot',
                'bot_name'     => 'OAI-SearchBot (ChatGPT Search Engine)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'OAI-SearchBot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'chatgpt_user',
                'bot_name'     => 'ChatGPT-User (Live User Queries)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'ChatGPT-User',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'claudebot',
                'bot_name'     => 'ClaudeBot (Anthropic Claude AI)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'ClaudeBot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'claude_web',
                'bot_name'     => 'Claude-Web (Anthropic Live Search)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Claude-Web',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'claude_searchbot',
                'bot_name'     => 'Claude-SearchBot (Anthropic Search)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Claude-SearchBot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'perplexitybot',
                'bot_name'     => 'PerplexityBot (Perplexity AI Search)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'PerplexityBot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'applebot_extended',
                'bot_name'     => 'Applebot-Extended (Apple Intelligence)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Applebot-Extended',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'google_extended',
                'bot_name'     => 'Google-Extended (Google Gemini)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Google-Extended',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'cohere_ai',
                'bot_name'     => 'Cohere-AI (Cohere RAG / Search)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'cohere-ai',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'meta_externalagent',
                'bot_name'     => 'Meta-ExternalAgent (Meta Llama AI)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Meta-ExternalAgent',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'diffbot',
                'bot_name'     => 'Diffbot (AI Knowledge Graph)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Diffbot',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'bytespider',
                'bot_name'     => 'Bytespider (ByteDance AI)',
                'bot_category' => 'ai_agent',
                'match_regex'  => 'Bytespider',
                'importance'   => 'recommended_ai',
                'action'       => 'allow'
            ],

            // --- TRADITIONAL SEARCH ENGINES ---
            [
                'bot_key'      => 'googlebot',
                'bot_name'     => 'Googlebot (Google Web Search)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'Googlebot(?!-Image|-Video|-News)',
                'importance'   => 'critical_seo',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'bingbot',
                'bot_name'     => 'Bingbot (Microsoft Bing / Copilot)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'bingbot',
                'importance'   => 'critical_seo',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'duckduckbot',
                'bot_name'     => 'DuckDuckBot (DuckDuckGo)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'DuckDuckBot',
                'importance'   => 'recommended_seo',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'applebot',
                'bot_name'     => 'Applebot (Apple Siri & Spotlight)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'Applebot(?!-Extended)',
                'importance'   => 'recommended_seo',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'yandexbot',
                'bot_name'     => 'YandexBot (Yandex Search)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'YandexBot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'baiduspider',
                'bot_name'     => 'Baiduspider (Baidu Search)',
                'bot_category' => 'search_engine',
                'match_regex'  => 'Baiduspider',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],

            // --- SEO TOOLS & AUDITORS ---
            [
                'bot_key'      => 'ahrefsbot',
                'bot_name'     => 'AhrefsBot (Ahrefs SEO Index)',
                'bot_category' => 'seo_tool',
                'match_regex'  => 'AhrefsBot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'semrushbot',
                'bot_name'     => 'SemrushBot (Semrush Marketing)',
                'bot_category' => 'seo_tool',
                'match_regex'  => 'SemrushBot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'mj12bot',
                'bot_name'     => 'MJ12bot (Majestic SEO)',
                'bot_category' => 'seo_tool',
                'match_regex'  => 'MJ12bot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],

            // --- AGGRESSIVE SCRAPERS & TRAINING CRAWLERS ---
            [
                'bot_key'      => 'ccbot',
                'bot_name'     => 'CCBot (Common Crawl Bulk Dataset)',
                'bot_category' => 'scraper',
                'match_regex'  => 'CCBot',
                'importance'   => 'bulk_scraper',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'turnitinbot',
                'bot_name'     => 'TurnitinBot (Turnitin Academic)',
                'bot_category' => 'scraper',
                'match_regex'  => 'TurnitinBot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'seekport',
                'bot_name'     => 'Seekport Crawler',
                'bot_category' => 'scraper',
                'match_regex'  => 'Seekport Crawler',
                'importance'   => 'heavy_crawler',
                'action'       => 'allow'
            ],
            [
                'bot_key'      => 'dotbot',
                'bot_name'     => 'DotBot (Moz Crawler)',
                'bot_category' => 'seo_tool',
                'match_regex'  => 'DotBot',
                'importance'   => 'neutral',
                'action'       => 'allow'
            ]
        ];
    }

    /**
     * Identifies bot from User-Agent string.
     */
    public static function identifyBot(?string $userAgent, array $customBots = []): array
    {
        if (empty($userAgent)) {
            return [
                'is_bot'       => false,
                'bot_key'      => 'unknown',
                'bot_name'     => 'Anonymous Client',
                'bot_category' => 'human',
                'action'       => 'allow'
            ];
        }

        $allBots = array_merge(self::getDefaultBots(), $customBots);

        foreach ($allBots as $bot) {
            $regex = '/' . str_replace('/', '\/', $bot['match_regex']) . '/i';
            if (@preg_match($regex, $userAgent)) {
                return [
                    'is_bot'       => true,
                    'bot_key'      => $bot['bot_key'],
                    'bot_name'     => $bot['bot_name'],
                    'bot_category' => $bot['bot_category'],
                    'action'       => $bot['action'] ?? 'allow'
                ];
            }
        }

        // Generic Bot Detection fallback
        if (preg_match('/(bot|crawler|spider|slurp|archiver|transcoder|fetcher|agent|curl|wget|python|urllib|httpclient)/i', $userAgent)) {
            return [
                'is_bot'       => true,
                'bot_key'      => 'generic_bot',
                'bot_name'     => 'Generic Web Crawler',
                'bot_category' => 'scraper',
                'action'       => 'allow'
            ];
        }

        return [
            'is_bot'       => false,
            'bot_key'      => 'human',
            'bot_name'     => 'Human Visitor',
            'bot_category' => 'human',
            'action'       => 'allow'
        ];
    }

    /**
     * Retrieves bots with their active firewall policies from settings
     */
    public static function getBotPolicies(array $settings = []): array
    {
        $custom = $settings['bot_policies'] ?? [];
        $bots = self::getDefaultBots();
        foreach ($bots as &$b) {
            if (isset($custom[$b['bot_key']])) {
                $b['action'] = $custom[$b['bot_key']];
            }
        }
        return $bots;
    }

    /**
     * Checks if current bot request should be blocked according to firewall rules
     */
    public static function shouldBlockBot(string $userAgent, array $settings = []): bool
    {
        $bot = self::identifyBot($userAgent);
        if (!$bot['is_bot']) {
            return false;
        }

        $policies = $settings['bot_policies'] ?? [];
        if (isset($policies[$bot['bot_key']]) && $policies[$bot['bot_key']] === 'block') {
            return true;
        }

        return false;
    }
}

