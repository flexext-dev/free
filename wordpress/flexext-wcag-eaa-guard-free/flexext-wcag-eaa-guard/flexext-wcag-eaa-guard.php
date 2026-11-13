<?php
/**
 * Plugin Name:       FlexExt Web Accessibility & EAA Guard
 * Plugin URI:        https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress
 * Description:       Frontend WCAG 2.2 accessibility toolbar (font scaling, high contrast, link underlining) and automated missing image alt attribute auditor on post save.
 * Version:           1.0.2
 * Author:            FlexExt Dev
 * Author URI:        https://flexext.dev/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flexext-wcag-eaa-guard
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

// Yield if the commercial PRO edition is active
if (class_exists('Visacom\WpEaaGuard\Plugin')) {
    return;
}

define('FLEXEAAG_VERSION', '1.0.2');
define('FLEXEAAG_FILE', __FILE__);
define('FLEXEAAG_DIR', plugin_dir_path(__FILE__));
define('FLEXEAAG_URL', plugin_dir_url(__FILE__));

// Require Core Classes (Frontend Accessibility Toolbar + Basic Alt Auditor + Admin Dashboard)
require_once FLEXEAAG_DIR . 'src/AccessibilityToolbar.php';
require_once FLEXEAAG_DIR . 'src/BasicAltAuditor.php';
require_once FLEXEAAG_DIR . 'src/AdminDashboard.php';

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
        add_filter('plugin_action_links_' . plugin_basename(FLEXEAAG_FILE), [self::class, 'addPluginActionLinks']);
    }

    public function initPlugin(): void
    {
        AccessibilityToolbar::init();
        BasicAltAuditor::init();
        if (is_admin()) {
            AdminDashboard::init();
        }
    }

    public static function addPluginActionLinks(array $links): array
    {
        $settingsUrl = admin_url('admin.php?page=flexext-wcag-eaa-guard');
        $proUrl      = 'https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress';

        $custom = [
            '<a href="' . esc_url($settingsUrl) . '">' . esc_html__('Settings', 'flexext-wcag-eaa-guard') . '</a>',
            '<a href="' . esc_url($proUrl) . '" target="_blank" rel="noopener noreferrer" style="color:#059669; font-weight:700;">' . esc_html__('PRO Edition', 'flexext-wcag-eaa-guard') . '</a>',
        ];
        return array_merge($custom, $links);
    }
}

Plugin::getInstance();
