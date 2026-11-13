<?php
declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard & Settings for Free WCAG Edition.
 * WCAG AA Contrast Compliant & Fully Bilingual (EN/PL).
 */
final class AdminDashboard
{
    public static function isPl(): bool
    {
        $locale = function_exists('get_user_locale') ? get_user_locale() : (function_exists('get_locale') ? get_locale() : 'en_US');
        return 0 === strpos(strtolower((string)$locale), 'pl');
    }

    public static function t(string $en, string $pl): string
    {
        return self::isPl() ? $pl : $en;
    }

    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('admin_init', [self::class, 'handleSave']);
    }

    public static function registerMenu(): void
    {
        add_menu_page(
            self::t('WCAG Accessibility Toolbar', 'Pasek Dostępności WCAG'),
            self::t('WCAG Guard', 'WCAG Guard'),
            'manage_options',
            'flexext-wcag-eaa-guard',
            [self::class, 'renderPage'],
            'dashicons-universal-access',
            78
        );
    }

    public static function handleSave(): void
    {
        if (!isset($_POST['flexeaag_save_action']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('flexeaag_admin_nonce', 'flexeaag_nonce');

        $action = isset($_POST['flexeaag_save_action']) ? sanitize_text_field(wp_unslash($_POST['flexeaag_save_action'])) : '';

        if ($action === 'save_settings') {
            $raw_pos = isset($_POST['toolbar_position']) ? sanitize_text_field(wp_unslash($_POST['toolbar_position'])) : 'bottom-left';
            $settings = [
                'enable_toolbar'   => !empty($_POST['enable_toolbar']) ? 1 : 0,
                'toolbar_position' => in_array($raw_pos, ['bottom-left', 'bottom-right'], true) ? $raw_pos : 'bottom-left',
            ];
            update_option('flexeaag_settings', $settings);
            add_settings_error('flexeaag_messages', 'saved', self::t('WCAG toolbar settings saved successfully!', 'Ustawienia paska dostępności WCAG zostały pomyślnie zapisane!'), 'success');
        }
    }

    public static function getSettings(): array
    {
        $defaults = [
            'enable_toolbar'   => 1,
            'toolbar_position' => 'bottom-left',
        ];
        $stored = get_option('flexeaag_settings', []);
        if (empty($stored) || !is_array($stored)) {
            $legacy = get_option('flexext_wcag_guard_settings', []);
            if (is_array($legacy) && !empty($legacy)) {
                $stored = $legacy;
            }
        }
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    public static function renderPage(): void
    {
        $settings = self::getSettings();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $activeTab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
        $proUrl = 'https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress';

        // Query posts with missing alts
        $postsWithIssues = get_posts([
            'post_type'      => ['post', 'page', 'product'],
            'post_status'    => 'publish',
            'meta_key'       => '_flexeaag_missing_alts',
            'meta_value'     => 0,
            'meta_compare'   => '>',
            'posts_per_page' => 15,
        ]);
        ?>
        <div class="wrap flx-wcag-wrap" style="max-width: 1040px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #091322 0%, #172554 100%); color: #ffffff; padding: 22px 28px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span>♿</span> FlexExt Web Accessibility &amp; EAA Guard
                        <span style="font-size: 11px; background: #0284c7; padding: 2px 8px; border-radius: 12px; font-weight: 600;">v<?php echo esc_html(FLEXEAAG_VERSION); ?></span>
                    </h1>
                    <p style="color: #94a3b8; margin: 6px 0 0 0; font-size: 13px;">
                        <?php echo esc_html(self::t('Frontend accessibility toolbar for website visitors and missing image alt auditor. Real-time Gutenberg guard and 1-click auto-fix in PRO.', 'Pasek ułatwień dostępu dla odwiedzających oraz detektor brakujących opisów alternatywnych (alt). Strażnik edytora Gutenberg w czasie rzeczywistym i auto-remediacja w wersji PRO.')); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #10b981; border-color: #059669; font-weight: 600; padding: 6px 14px; height: auto;">
                        🚀 <?php echo esc_html(self::t('Upgrade to PRO ($25)', 'Przejdź na Wersję PRO ($25)')); ?>
                    </a>
                </div>
            </div>

            <?php settings_errors('flexeaag_messages'); ?>

            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-eaa-guard&tab=settings')); ?>" class="nav-tab <?php echo $activeTab === 'settings' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    ♿ <?php echo esc_html(self::t('On-Page Accessibility Toolbar', 'Pasek Dostępności na Stronie')); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-eaa-guard&tab=issues')); ?>" class="nav-tab <?php echo $activeTab === 'issues' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    🖼️ <?php echo esc_html(self::t('Missing Alt Attributes', 'Brakujące Opisy Alt')); ?> (<?php echo count($postsWithIssues); ?>)
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-eaa-guard&tab=pro')); ?>" class="nav-tab <?php echo $activeTab === 'pro' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600; color: #047857;">
                    ⭐ <?php echo esc_html(self::t('PRO Edition & Comparison', 'Wersja PRO & Porównanie')); ?>
                </a>
            </nav>

            <!-- TAB 1: TOOLBAR SETTINGS -->
            <?php if ($activeTab === 'settings'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php echo esc_html(self::t('Accessibility Toolbar Configuration (Frontend Widget)', 'Konfiguracja Paska Ułatwień Dostępności (Frontend Widget)')); ?>
                    </h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('flexeaag_admin_nonce', 'flexeaag_nonce'); ?>
                        <input type="hidden" name="flexeaag_save_action" value="save_settings">

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php echo esc_html(self::t('Toolbar Status', 'Status paska na stronie')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enable_toolbar" value="1" <?php checked(1, $settings['enable_toolbar']); ?>>
                                        <strong><?php echo esc_html(self::t('Enable floating accessibility button (♿) for website visitors', 'Włącz pływający przycisk dostępności (♿) dla odwiedzających stronę')); ?></strong>
                                    </label>
                                    <p class="description"><?php echo esc_html(self::t('Allows visitors to enlarge font size, toggle high-contrast dark mode, and underline all links.', 'Umożliwia użytkownikom zmianę rozmiaru tekstu, włączenie wysokiego kontrastu oraz podkreślenie odnośników.')); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::t('Button Position', 'Pozycja przycisku')); ?></th>
                                <td>
                                    <label style="margin-right: 15px;">
                                        <input type="radio" name="toolbar_position" value="bottom-left" <?php checked('bottom-left', $settings['toolbar_position']); ?>>
                                        <?php echo esc_html(self::t('Bottom Left Corner', 'Lewy dolny róg')); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="toolbar_position" value="bottom-right" <?php checked('bottom-right', $settings['toolbar_position']); ?>>
                                        <?php echo esc_html(self::t('Bottom Right Corner', 'Prawy dolny róg')); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr style="background: #f8fafc;">
                                <th scope="row" style="color: #64748b;"><?php echo esc_html(self::t('Real-Time Gutenberg & Classic Editor Guard', 'Strażnik Edytora Gutenberg w czasie rzeczywistym')); ?></th>
                                <td>
                                    <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;"><?php echo esc_html(self::t('⭐ PRO Edition Feature ($25)', '⭐ Dostępne wyłącznie w Wersji PRO ($25)')); ?></span>
                                    <p class="description" style="color: #64748b; margin-top: 4px;">
                                        <?php echo esc_html(self::t('Live sidebar auditing contrast ratios, heading hierarchy, and missing alts with an automated pre-publish blocker is available in PRO.', 'Podgląd błędów kontrastu, nagłówków i brakujących opisów alt na żywo w pasku bocznym edytora Gutenberg i Classic z blokadą publikacji wadliwych artykułów.')); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr style="background: #f8fafc;">
                                <th scope="row" style="color: #64748b;"><?php echo esc_html(self::t('1-Click Auto-Remediation Engine', '1-Klik Napraw (Auto-Remediation)')); ?></th>
                                <td>
                                    <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;"><?php echo esc_html(self::t('⭐ PRO Edition Feature ($25)', '⭐ Dostępne wyłącznie w Wersji PRO ($25)')); ?></span>
                                    <p class="description" style="color: #64748b; margin-top: 4px;">
                                        <?php echo esc_html(self::t('Automatic contrast enhancement to 4.5:1 (WCAG AA), ARIA attribute synthesis, and table structure repair in one click.', 'Automatyczne podnoszenie kontrastu kolorów do 4.5:1 (WCAG AA), uzupełnianie atrybutów ARIA oraz naprawianie tabel jednym kliknięciem.')); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary" style="font-weight: 600;">
                                💾 <?php echo esc_html(self::t('Save Toolbar Settings', 'Zapisz Ustawienia')); ?>
                            </button>
                        </p>
                    </form>
                </div>

            <!-- TAB 2: ISSUES -->
            <?php elseif ($activeTab === 'issues'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php echo esc_html(self::t('Articles with Missing Alternative Text (alt)', 'Wpisy z brakującymi opisami alternatywnymi (alt)')); ?>
                    </h2>
                    <p style="color: #475569; font-size: 13px;">
                        <?php echo esc_html(self::t('The Free version automatically audits posts during save to detect missing image descriptions (WCAG 1.1.1 Non-text Content).', 'Wersja Free automatycznie sprawdza wpisy podczas ich zapisywania pod kątem brakujących opisów obrazów (WCAG 1.1.1).')); ?>
                    </p>

                    <?php if (empty($postsWithIssues)): ?>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 18px; border-radius: 6px; margin-top: 15px;">
                            ✅ <strong><?php echo esc_html(self::t('All clean!', 'Świetnie!')); ?></strong> <?php echo esc_html(self::t('No articles with missing alt descriptions were detected during recent saves.', 'Nie wykryto wpisów z brakującymi opisami alt podczas ostatnich zapisów.')); ?>
                        </div>
                    <?php else: ?>
                        <table class="widefat striped" style="margin-top: 15px; border: 1px solid #cbd5e1;">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(self::t('Post Title', 'Tytuł wpisu')); ?></th>
                                    <th><?php echo esc_html(self::t('Post Type', 'Typ')); ?></th>
                                    <th><?php echo esc_html(self::t('Missing Alts Count', 'Liczba brakujących altów')); ?></th>
                                    <th><?php echo esc_html(self::t('Action', 'Akcja')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($postsWithIssues as $p): 
                                    $missing = (int)get_post_meta($p->ID, '_flexeaag_missing_alts', true);
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($p->post_title ?: self::t('(Untitled)', '(Bez tytułu)')); ?></strong></td>
                                        <td><code><?php echo esc_html($p->post_type); ?></code></td>
                                        <td><span style="background: #fef2f2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: 700;"><?php echo esc_html((string)$missing); ?></span></td>
                                        <td>
                                            <a href="<?php echo esc_url(get_edit_post_link($p->ID)); ?>" class="button button-small">
                                                ✏️ <?php echo esc_html(self::t('Edit post', 'Edytuj wpis')); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <!-- TAB 3: PRO COMPARISON -->
            <?php elseif ($activeTab === 'pro'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                        <div>
                            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">
                                🚀 <?php echo esc_html(self::t('Expand Capabilities with WCAG 2.2 & EAA Guard PRO', 'Rozwiń możliwości z WCAG 2.2 & EAA Guard PRO')); ?>
                            </h2>
                            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">
                                <?php echo esc_html(self::t('Achieve full legal compliance and protection against statutory fines (European Accessibility Act 2025).', 'Zyskaj pełne bezpieczeństwo prawne przed karami za brak dostępności (Europejski Akt o Dostępności 2025).')); ?>
                            </p>
                        </div>
                        <div>
                            <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #059669; border-color: #047857; font-size: 15px; font-weight: 700; padding: 8px 20px; height: auto;">
                                🛒 <?php echo esc_html(self::t('Buy PRO Edition ($25) on flexext.dev', 'Kup Wersję PRO za $25 na flexext.dev')); ?>
                            </a>
                        </div>
                    </div>

                    <h3 style="margin-top: 26px; font-size: 16px; color: #0f172a; font-weight: 700;">
                        <?php echo esc_html(self::t('Feature Comparison Matrix: Free vs PRO', 'Tabela porównawcza: Wersja Free vs PRO')); ?>
                    </h3>

                    <table class="widefat striped" style="margin-top: 14px; border: 1px solid #cbd5e1;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700;"><?php echo esc_html(self::t('Feature / Capability', 'Funkcjonalność')); ?></th>
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: center; width: 150px;"><?php echo esc_html(self::t('FREE Edition (WP.org)', 'Wersja Free')); ?></th>
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: center; width: 170px; color: #047857;"><?php echo esc_html(self::t('PRO Edition ($25)', 'Wersja PRO ($25)')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Frontend visitor accessibility toolbar (font resize, contrast, links)', 'Frontendowy pasek ułatwień dostępu dla odwiedzających (Toolbar)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Missing image alt description auditing on post save', 'Wykrywanie brakujących opisów alt przy zapisie wpisu')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Real-time Gutenberg & Classic live sidebar inspector', 'Strażnik edytora Gutenberg & Classic w czasie rzeczywistym')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Automated pre-publish publication blocker for non-compliant posts', 'Automatyczna blokada publikacji artykułów niespełniających WCAG')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('1-Click Auto-Remediation engine (contrast ratio 4.5:1 & ARIA auto-fix)', 'Silnik 1-Klik Automatyczna Naprawa (Auto-Remediacja kontrastu i ARIA)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('PDF media accessibility auditor (PDF/UA tagging check)', 'Audytor plików PDF pod kątem dostępności cyfrowej')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Statutory EAA 2025 Accessibility Statement generator', 'Generator Urzędowej Deklaracji Dostępności (EAA 2025)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Official EAA Compliance Audit PDF export', 'Eksport Oficjalnego Raportu Audytu EAA do PDF')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Dedicated 1-on-1 commercial SLA & priority support', 'Dedykowane wsparcie komercyjne 1-na-1 i SLA')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 24px; text-align: center;">
                        <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #059669; border-color: #047857; font-size: 16px; font-weight: 700; padding: 10px 28px; height: auto;">
                            👉 <?php echo esc_html(self::t('Go to flexext.dev and purchase PRO for $25', 'Przejdź do sklepu flexext.dev i kup wersję PRO za $25')); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }
}
