<?php
declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Front-end Accessibility Toolbar for Visitors.
 * WCAG 2.2 AA Contrast Compliant & Fully Bilingual.
 */
final class AccessibilityToolbar
{
    public static function init(): void
    {
        $settings = AdminDashboard::getSettings();
        if (empty($settings['enable_toolbar'])) {
            return;
        }

        add_action('wp_enqueue_scripts', [self::class, 'enqueueAssets']);
        add_action('wp_footer', [self::class, 'renderToolbarHtml']);
    }

    public static function enqueueAssets(): void
    {
        wp_enqueue_style(
            'flexeaag-toolbar-style',
            plugins_url('assets/css/toolbar.css', FLEXEAAG_FILE),
            [],
            FLEXEAAG_VERSION
        );

        wp_enqueue_script(
            'flexeaag-toolbar-script',
            plugins_url('assets/js/toolbar.js', FLEXEAAG_FILE),
            [],
            FLEXEAAG_VERSION,
            true
        );
    }

    public static function renderToolbarHtml(): void
    {
        $settings = AdminDashboard::getSettings();
        $pos      = $settings['toolbar_position'] ?? 'bottom-left';
        $posStyle = ($pos === 'bottom-right') ? 'right: 20px;' : 'left: 20px;';
        $isPl     = AdminDashboard::isPl();
        ?>
        <div id="flx-wcag-toolbar-root" style="position: fixed; bottom: 20px; <?php echo esc_attr($posStyle); ?> z-index: 999999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <!-- Trigger Button -->
            <button type="button" id="flx-wcag-toggle-btn" aria-label="<?php echo esc_attr($isPl ? 'Ułatwienia dostępu WCAG' : 'WCAG Accessibility Tools'); ?>" aria-expanded="false" style="width: 50px; height: 50px; border-radius: 50%; background: #0f172a; color: #ffffff; border: 2px solid #38bdf8; box-shadow: 0 4px 14px rgba(0,0,0,0.3); font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;">
                ♿
            </button>

            <!-- Accessibility Panel Modal/Drawer -->
            <div id="flx-wcag-panel" role="region" aria-label="<?php echo esc_attr($isPl ? 'Panel Ułatwień Dostępności' : 'Accessibility Preferences'); ?>" style="display: none; position: absolute; bottom: 60px; <?php echo ($pos === 'bottom-right') ? 'right: 0;' : 'left: 0;'; ?> width: 280px; background: #ffffff; color: #0f172a; border: 2px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">
                    <strong style="font-size: 14px; color: #0f172a;">♿ <?php echo esc_html($isPl ? 'Ułatwienia Dostępności' : 'Accessibility Preferences'); ?></strong>
                    <button type="button" id="flx-wcag-close-btn" aria-label="<?php echo esc_attr($isPl ? 'Zamknij' : 'Close'); ?>" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">&times;</button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <!-- Font Size -->
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;"><?php echo esc_html($isPl ? 'Rozmiar tekstu:' : 'Text size:'); ?></div>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="flx-wcag-btn" data-action="font-normal" style="flex:1; padding: 6px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 4px; cursor: pointer; font-weight: 600;">100%</button>
                            <button type="button" class="flx-wcag-btn" data-action="font-large" style="flex:1; padding: 6px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 4px; cursor: pointer; font-weight: 600;">120%</button>
                            <button type="button" class="flx-wcag-btn" data-action="font-xlarge" style="flex:1; padding: 6px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 4px; cursor: pointer; font-weight: 600;">140%</button>
                        </div>
                    </div>

                    <!-- High Contrast -->
                    <button type="button" class="flx-wcag-btn" data-action="contrast" style="text-align: left; padding: 8px 10px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        🌓 <?php echo esc_html($isPl ? 'Wysoki Kontrast / Tryb Ciemny' : 'High Contrast / Dark Mode'); ?>
                    </button>

                    <!-- Highlight Links -->
                    <button type="button" class="flx-wcag-btn" data-action="links" style="text-align: left; padding: 8px 10px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        🔗 <?php echo esc_html($isPl ? 'Podkreśl wszystkie odnośniki' : 'Underline all links'); ?>
                    </button>

                    <!-- Reset -->
                    <button type="button" class="flx-wcag-btn" data-action="reset" style="text-align: center; padding: 8px 10px; border: 1px solid #e2e8f0; background: #f1f5f9; color: #475569; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 4px;">
                        🔄 <?php echo esc_html($isPl ? 'Zresetuj ustawienia' : 'Reset preferences'); ?>
                    </button>
                </div>

                <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; text-align: center;">
                    <?php echo esc_html($isPl ? 'Zgodność z WCAG 2.2 AA / EAA' : 'WCAG 2.2 AA / EAA Accessibility'); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
