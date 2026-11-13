<?php
/**
 * Plugin Name:       FlexExt AI SEO & Agent Readiness
 * Plugin URI:        https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro
 * Description:       Optimize your WordPress site for AI search engines and LLMs (ChatGPT, Claude, Perplexity, Gemini) with standard /llms.txt generation and robots.txt AI crawler control.
 * Version:           1.0.2
 * Author:            FlexExt Dev
 * Author URI:        https://flexext.dev/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flexext-ai-seo
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

declare(strict_types=1);

namespace FlexExt\WordPress\AiSeoFree;

if (!defined('ABSPATH')) {
    exit;
}

// Yield if the commercial PRO edition is active
if (class_exists('FlexExt\WordPress\AiSeo\Plugin')) {
    return;
}

define('FLEXAISEO_VERSION', '1.0.2');
define('FLEXAISEO_FILE', __FILE__);
define('FLEXAISEO_DIR', plugin_dir_path(__FILE__));
define('FLEXAISEO_URL', plugin_dir_url(__FILE__));

// Require Core Classes
require_once FLEXAISEO_DIR . 'src/BotManager.php';
require_once FLEXAISEO_DIR . 'src/AdminDashboard.php';

/**
 * Main Plugin Engine Class
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    public static function getInstance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Serve /llms.txt endpoint
        add_action('init', [$this, 'handleInit'], 1);

        // Inject AI crawler directives into virtual robots.txt
        add_filter('robots_txt', [$this, 'filterRobotsTxt'], 10, 2);

        // Admin interface
        if (is_admin()) {
            add_action('admin_menu', [$this, 'registerAdminMenu']);
            add_action('admin_post_flexaiseo_save_bots', [$this, 'handleSaveBots']);
            add_action('admin_post_flexaiseo_save_llms', [$this, 'handleSaveLlms']);
            add_filter('plugin_action_links_' . plugin_basename(FLEXAISEO_FILE), [$this, 'addPluginActionLinks']);
        }
    }

    public function handleInit(): void
    {
        $uri  = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $path = wp_parse_url($uri, PHP_URL_PATH);

        if ($path === '/llms.txt') {
            $settings = $this->getSettings();
            if (!empty($settings['enable_llms_txt'])) {
                header('Content-Type: text/plain; charset=utf-8');
                header('X-Robots-Tag: noindex, follow');

                $title   = !empty($settings['llms_title']) ? $settings['llms_title'] : get_bloginfo('name');
                $summary = !empty($settings['llms_summary']) ? $settings['llms_summary'] : get_bloginfo('description');

                echo "# " . esc_html((string) $title) . "\n\n";
                echo "> " . esc_html((string) $summary) . "\n\n";
                echo "## Primary Pages & Recent Content\n\n";
                echo "- [" . esc_html((string) $title) . "](" . esc_url(get_site_url()) . "): Main website.\n";

                $posts = get_posts([
                    'numberposts' => 5,
                    'post_status' => 'publish',
                    'post_type'   => ['page', 'post'],
                ]);
                foreach ($posts as $p) {
                    echo "- [" . esc_html($p->post_title) . "](" . esc_url((string) get_permalink($p->ID)) . ")\n";
                }
                exit;
            }
        }
    }

    public function filterRobotsTxt(string $output, bool $public): string
    {
        $settings = $this->getSettings();
        $policy   = $settings['bot_policy'] ?? [];
        $bots     = BotManager::getTrackedBots();

        $aiRules = "\n# --- FlexExt AI Bot Directives ---\n";
        foreach ($bots as $k => $b) {
            $p = $policy[$k] ?? 'allow';
            $aiRules .= "User-agent: " . $b['ua'] . "\n";
            if ($p === 'disallow') {
                $aiRules .= "Disallow: /\n";
            } else {
                $aiRules .= "Allow: /\n";
            }
        }
        $aiRules .= "# --- End AI Bot Directives ---\n\n";

        return $aiRules . $output;
    }

    public function getSettings(): array
    {
        $defaults = [
            'enable_llms_txt' => 1,
            'llms_title'      => get_bloginfo('name'),
            'llms_summary'    => get_bloginfo('description'),
            'bot_policy'      => [
                'gptbot'            => 'allow',
                'oai_searchbot'     => 'allow',
                'chatgpt_user'      => 'allow',
                'claudebot'         => 'allow',
                'perplexitybot'     => 'allow',
                'google_extended'   => 'allow',
                'applebot_extended' => 'allow',
                'meta_external'     => 'allow',
                'ccbot'             => 'disallow',
            ],
        ];
        $stored = get_option('flexaiseo_settings', []);
        if (empty($stored) || !is_array($stored)) {
            $legacy = get_option('flexext_ai_seo_free_settings', []);
            if (is_array($legacy) && !empty($legacy)) {
                $stored = $legacy;
            }
        }
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            __('AI SEO & LLMs.txt', 'flexext-ai-seo'),
            __('AI SEO & LLMs.txt', 'flexext-ai-seo'),
            'manage_options',
            'flexext-ai-seo',
            [$this, 'renderAdminPage'],
            'dashicons-superhero',
            79
        );
    }

    public function renderAdminPage(): void
    {
        AdminDashboard::render($this->getSettings());
    }

    public function handleSaveBots(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('flexaiseo_save_bots', 'flexaiseo_nonce');

        $settings      = $this->getSettings();
        $rawPolicy     = isset($_POST['bot_policy']) && is_array($_POST['bot_policy']) ? array_map('sanitize_text_field', wp_unslash($_POST['bot_policy'])) : [];
        $cleanedPolicy = [];
        foreach ($rawPolicy as $k => $v) {
            $cleanedPolicy[sanitize_key((string) $k)] = ($v === 'disallow') ? 'disallow' : 'allow';
        }
        $settings['bot_policy'] = $cleanedPolicy;
        update_option('flexaiseo_settings', $settings);

        wp_safe_redirect(admin_url('admin.php?page=flexext-ai-seo&tab=bots&saved=1'));
        exit;
    }

    public function handleSaveLlms(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('flexaiseo_save_llms', 'flexaiseo_nonce');

        $settings                    = $this->getSettings();
        $settings['enable_llms_txt'] = !empty($_POST['enable_llms_txt']) ? 1 : 0;
        $settings['llms_title']      = sanitize_text_field(wp_unslash($_POST['llms_title'] ?? ''));
        $settings['llms_summary']    = sanitize_textarea_field(wp_unslash($_POST['llms_summary'] ?? ''));
        update_option('flexaiseo_settings', $settings);

        wp_safe_redirect(admin_url('admin.php?page=flexext-ai-seo&tab=llms&saved=1'));
        exit;
    }

    public function addPluginActionLinks(array $links): array
    {
        $settingsUrl = admin_url('admin.php?page=flexext-ai-seo');
        $proUrl      = 'https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro';

        $custom = [
            '<a href="' . esc_url($settingsUrl) . '">' . esc_html__('Settings', 'flexext-ai-seo') . '</a>',
            '<a href="' . esc_url($proUrl) . '" target="_blank" rel="noopener noreferrer" style="color:#059669; font-weight:700;">' . esc_html__('PRO Edition', 'flexext-ai-seo') . '</a>',
        ];
        return array_merge($custom, $links);
    }
}

Plugin::getInstance();
