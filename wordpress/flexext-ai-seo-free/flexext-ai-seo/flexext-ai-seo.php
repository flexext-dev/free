<?php
/**
 * Plugin Name:       FlexExt AI SEO Agent Readiness for WordPress
 * Plugin URI:        https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro
 * Description:       Optymalizacja witryny pod kątem modeli językowych i wyszukiwarek AI (ChatGPT, Claude, Perplexity). Standard /llms.txt oraz kontrola robots.txt.
 * Version:           1.0.0
 * Author:            FlexExt Dev
 * Author URI:        https://flexext.dev/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flexext-ai-seo
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

declare(strict_types=1);

namespace FlexExt\WordPress\AiSeoFree;

if (!defined('ABSPATH')) {
    exit;
}

// Jeśli aktywna jest pełna wersja komercyjna PRO, wersja darmowa ustępuje pierwszeństwa
if (class_exists('FlexExt\WordPress\AiSeo\Plugin')) {
    return;
}

define('FLEXEXT_AI_SEO_FREE_VERSION', '1.0.0');
define('FLEXEXT_AI_SEO_FREE_FILE', __FILE__);
define('FLEXEXT_AI_SEO_FREE_DIR', plugin_dir_path(__FILE__));
define('FLEXEXT_AI_SEO_FREE_URL', plugin_dir_url(__FILE__));

// Require Core Classes
require_once FLEXEXT_AI_SEO_FREE_DIR . 'src/BotManager.php';
require_once FLEXEXT_AI_SEO_FREE_DIR . 'src/LlmsTxtGenerator.php';
require_once FLEXEXT_AI_SEO_FREE_DIR . 'src/AgentReadinessAuditor.php';
require_once FLEXEXT_AI_SEO_FREE_DIR . 'src/AdminDashboard.php';

/**
 * Main Plugin Engine Class (Free Edition)
 */
class Plugin
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
        // Obsługa żądań /llms.txt
        add_action('init', [$this, 'handleInit'], 1);

        // Modyfikacja wirtualnego pliku robots.txt
        add_filter('robots_txt', [$this, 'filterRobotsTxt'], 10, 2);

        // Panel administracyjny
        if (is_admin()) {
            add_action('admin_menu', [$this, 'registerAdminMenu']);
            add_action('admin_post_flx_ai_seo_save_bots', [$this, 'handleSaveBots']);
            add_action('admin_post_flx_ai_seo_save_llms', [$this, 'handleSaveLlms']);
            add_filter('plugin_action_links_' . plugin_basename(FLEXEXT_AI_SEO_FREE_FILE), [$this, 'addPluginActionLinks']);
        }
    }

    public function handleInit(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === '/llms.txt') {
            $settings = $this->getSettings();
            if (!empty($settings['enable_llms_txt'])) {
                header('Content-Type: text/plain; charset=utf-8');
                header('X-Robots-Tag: noindex, follow');
                
                $title = $settings['llms_title'] ?: get_bloginfo('name');
                $summary = $settings['llms_summary'] ?: get_bloginfo('description');
                
                echo "# " . esc_html($title) . "\n\n";
                echo "> " . esc_html($summary) . "\n\n";
                echo "## Primary Documentation\n\n";
                echo "- [" . esc_html($title) . "](" . esc_url(get_site_url()) . "): Main website and store.\n";
                
                // Pokaż ostatnie wpisy lub strony
                $posts = get_posts(['numberposts' => 10, 'post_status' => 'publish']);
                foreach ($posts as $p) {
                    echo "- [" . esc_html($p->post_title) . "](" . esc_url(get_permalink($p->ID)) . ")\n";
                }
                exit;
            }
        }
    }

    public function filterRobotsTxt(string $output, bool $public): string
    {
        $settings = $this->getSettings();
        $policy = $settings['bot_policy'] ?? [];
        $bots = BotManager::getTrackedBots();

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
                'gptbot'           => 'allow',
                'chatgpt_user'     => 'allow',
                'claudebot'        => 'allow',
                'perplexitybot'    => 'allow',
                'google_extended'  => 'allow',
                'applebot_extended'=> 'allow',
                'meta_external'    => 'allow',
            ]
        ];
        $stored = get_option('flexext_ai_seo_free_settings', []);
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            __('AI SEO & LLMs.txt', 'flexext-ai-seo'),
            __('AI SEO (Free)', 'flexext-ai-seo'),
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
        check_admin_referer('flx_ai_seo_save_bots', 'flx_ai_seo_nonce');

        $settings = $this->getSettings();
        $rawPolicy = $_POST['bot_policy'] ?? [];
        $cleanedPolicy = [];
        if (is_array($rawPolicy)) {
            foreach ($rawPolicy as $k => $v) {
                $cleanedPolicy[sanitize_key($k)] = ($v === 'disallow') ? 'disallow' : 'allow';
            }
        }
        $settings['bot_policy'] = $cleanedPolicy;
        update_option('flexext_ai_seo_free_settings', $settings);

        wp_safe_redirect(admin_url('admin.php?page=flexext-ai-seo&tab=bots&saved=1'));
        exit;
    }

    public function handleSaveLlms(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('flx_ai_seo_save_llms', 'flx_ai_seo_nonce');

        $settings = $this->getSettings();
        $settings['enable_llms_txt'] = !empty($_POST['enable_llms_txt']) ? 1 : 0;
        $settings['llms_title'] = sanitize_text_field(wp_unslash($_POST['llms_title'] ?? ''));
        $settings['llms_summary'] = sanitize_textarea_field(wp_unslash($_POST['llms_summary'] ?? ''));
        update_option('flexext_ai_seo_free_settings', $settings);

        wp_safe_redirect(admin_url('admin.php?page=flexext-ai-seo&tab=llms&saved=1'));
        exit;
    }

    public function addPluginActionLinks(array $links): array
    {
        $settingsUrl = admin_url('admin.php?page=flexext-ai-seo');
        $proUrl = 'https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro';

        $custom = [
            '<a href="' . esc_url($settingsUrl) . '">' . __('Ustawienia', 'flexext-ai-seo') . '</a>',
            '<a href="' . esc_url($proUrl) . '" target="_blank" style="color:#059669; font-weight:700;">' . __('Wersja PRO ($25)', 'flexext-ai-seo') . '</a>',
        ];
        return array_merge($custom, $links);
    }
}

// Inicjalizacja instancji
Plugin::getInstance();
