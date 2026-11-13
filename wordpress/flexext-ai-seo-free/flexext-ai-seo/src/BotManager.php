<?php
/**
 * AI Bot Dictionary & Robots.txt Policy Manager
 *
 * @package     FlexExt_AI_SEO
 * @author      FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 FlexExt. All rights reserved.
 * @license     GPLv2 or later
 * @link        https://flexext.dev
 */

declare(strict_types=1);

namespace FlexExt\WordPress\AiSeoFree;

if (!defined('ABSPATH')) {
    exit;
}

final class BotManager
{
    /**
     * Returns the dictionary of tracked AI search and LLM crawlers.
     *
     * @return array<string, array{name: string, owner: string, ua: string}>
     */
    public static function getTrackedBots(): array
    {
        return [
            'gptbot' => [
                'name'  => 'GPTBot (OpenAI / ChatGPT)',
                'owner' => 'OpenAI',
                'ua'    => 'GPTBot',
            ],
            'oai_searchbot' => [
                'name'  => 'OAI-SearchBot (ChatGPT Search)',
                'owner' => 'OpenAI',
                'ua'    => 'OAI-SearchBot',
            ],
            'chatgpt_user' => [
                'name'  => 'ChatGPT-User (Live Browsing)',
                'owner' => 'OpenAI',
                'ua'    => 'ChatGPT-User',
            ],
            'claudebot' => [
                'name'  => 'ClaudeBot (Anthropic Claude AI)',
                'owner' => 'Anthropic',
                'ua'    => 'ClaudeBot',
            ],
            'perplexitybot' => [
                'name'  => 'PerplexityBot (Perplexity AI Search)',
                'owner' => 'Perplexity AI',
                'ua'    => 'PerplexityBot',
            ],
            'google_extended' => [
                'name'  => 'Google-Extended (Google Gemini / Vertex AI)',
                'owner' => 'Google',
                'ua'    => 'Google-Extended',
            ],
            'applebot_extended' => [
                'name'  => 'Applebot-Extended (Apple Intelligence)',
                'owner' => 'Apple',
                'ua'    => 'Applebot-Extended',
            ],
            'meta_external' => [
                'name'  => 'Meta-ExternalAgent (Meta Llama AI)',
                'owner' => 'Meta',
                'ua'    => 'Meta-ExternalAgent',
            ],
            'ccbot' => [
                'name'  => 'CCBot (Common Crawl Dataset)',
                'owner' => 'Common Crawl',
                'ua'    => 'CCBot',
            ],
        ];
    }
}
