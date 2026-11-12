<?php
/**
 * Plugin Name:       FlexExt WCAG 2.2 & EAA Guard for WordPress
 * Plugin URI:        https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla
 * Description:       Strażnik dostępności cyfrowej dla edytora Gutenberg i Classic (WCAG 2.2 AA / Dyrektywa EAA 2025). Weryfikacja kontrastu, brakujących altów i hierarchii nagłówków w czasie rzeczywistym.
 * Version:           1.0.0
 * Author:            FlexExt Dev
 * Author URI:        https://flexext.dev/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flexext-wcag-guard
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

// Jeśli aktywna jest pełna wersja komercyjna PRO, wersja darmowa ustępuje pierwszeństwa
if (class_exists('Visacom\WpEaaGuard\Plugin')) {
    return;
}

define('FLEXEXT_WCAG_GUARD_VERSION', '1.0.0');
define('FLEXEXT_WCAG_GUARD_FILE', __FILE__);
define('FLEXEXT_WCAG_GUARD_DIR', plugin_dir_path(__FILE__));
define('FLEXEXT_WCAG_GUARD_URL', plugin_dir_url(__FILE__));

// Require Core Classes
require_once FLEXEXT_WCAG_GUARD_DIR . 'src/ContentAuditor.php';
require_once FLEXEXT_WCAG_GUARD_DIR . 'src/EditorGuard.php';
require_once FLEXEXT_WCAG_GUARD_DIR . 'src/AdminDashboard.php';

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
        add_action('init', [$this, 'initPlugin']);
        add_filter('plugin_action_links_' . plugin_basename(FLEXEXT_WCAG_GUARD_FILE), [self::class, 'addPluginActionLinks']);
    }

    public function initPlugin(): void
    {
        load_plugin_textdomain('flexext-wcag-guard', false, dirname(plugin_basename(FLEXEXT_WCAG_GUARD_FILE)) . '/languages');

        EditorGuard::init();
        if (is_admin()) {
            AdminDashboard::init();
        }
    }

    public static function addPluginActionLinks(array $links): array
    {
        $settingsUrl = admin_url('admin.php?page=flexext-wcag-guard');
        $proUrl = 'https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla';

        $custom = [
            '<a href="' . esc_url($settingsUrl) . '">' . __('Ustawienia', 'flexext-wcag-guard') . '</a>',
            '<a href="' . esc_url($proUrl) . '" target="_blank" style="color:#059669; font-weight:700;">' . __('Wersja PRO ($25)', 'flexext-wcag-guard') . '</a>',
        ];
        return array_merge($custom, $links);
    }
}

// Inicjalizacja instancji
Plugin::getInstance();
