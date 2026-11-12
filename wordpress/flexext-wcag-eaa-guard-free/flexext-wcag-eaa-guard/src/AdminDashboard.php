<?php
declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard & Settings for Free WCAG Edition.
 */
final class AdminDashboard
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('admin_init', [self::class, 'handleSave']);
    }

    public static function registerMenu(): void
    {
        add_menu_page(
            __('Strażnik WCAG & EAA', 'flexext-wcag-guard'),
            __('WCAG & EAA Guard', 'flexext-wcag-guard'),
            'manage_options',
            'flexext-wcag-guard',
            [self::class, 'renderPage'],
            'dashicons-universal-access',
            78
        );
    }

    public static function handleSave(): void
    {
        if (!isset($_POST['flx_wcag_save_action']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('flx_wcag_admin_nonce', 'flx_wcag_nonce');

        $action = sanitize_text_field($_POST['flx_wcag_save_action']);

        if ($action === 'save_settings') {
            $settings = [
                'block_publish_on_error' => !empty($_POST['block_publish_on_error']) ? 1 : 0,
                'check_contrast'         => !empty($_POST['check_contrast']) ? 1 : 0,
                'check_alt_text'         => !empty($_POST['check_alt_text']) ? 1 : 0,
                'check_headings'         => !empty($_POST['check_headings']) ? 1 : 0,
                'check_link_text'        => !empty($_POST['check_link_text']) ? 1 : 0,
            ];
            update_option('flexext_wcag_guard_settings', $settings);
            add_settings_error('flx_wcag_messages', 'saved', __('Ustawienia strażnika WCAG zostały pomyślnie zapisane!', 'flexext-wcag-guard'), 'success');
        }
    }

    public static function getSettings(): array
    {
        $defaults = [
            'block_publish_on_error' => 0,
            'check_contrast'         => 1,
            'check_alt_text'         => 1,
            'check_headings'         => 1,
            'check_link_text'        => 1,
        ];
        $stored = get_option('flexext_wcag_guard_settings', []);
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    public static function renderPage(): void
    {
        $settings = self::getSettings();
        $activeTab = sanitize_text_field($_GET['tab'] ?? 'settings');
        $proUrl = 'https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla'; // Also available for WP
        ?>
        <div class="wrap flx-wcag-wrap" style="max-width: 1040px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #091322 0%, #172554 100%); color: #ffffff; padding: 22px 28px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span>♿</span> FlexExt WCAG 2.2 &amp; EAA Guard for WordPress
                        <span style="font-size: 11px; background: #0284c7; padding: 2px 8px; border-radius: 12px; font-weight: 600;">v<?php echo esc_html(FLEXEXT_WCAG_GUARD_VERSION); ?> Free</span>
                    </h1>
                    <p style="color: #94a3b8; margin: 6px 0 0 0; font-size: 13px;">
                        Strażnik przedpublikacyjny dostępności cyfrowej dla edytora Gutenberg i Classic Editor (Dyrektywa EAA 2025 / WCAG 2.2 AA).
                    </p>
                </div>
                <div>
                    <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #10b981; border-color: #059669; font-weight: 600; padding: 6px 14px; height: auto;">
                        🚀 Przejdź na Wersję PRO ($25)
                    </a>
                </div>
            </div>

            <?php settings_errors('flx_wcag_messages'); ?>

            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-guard&tab=settings')); ?>" class="nav-tab <?php echo $activeTab === 'settings' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    🛡️ <?php esc_html_e('Strażnik Edytora & Ustawienia', 'flexext-wcag-guard'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-guard&tab=tester')); ?>" class="nav-tab <?php echo $activeTab === 'tester' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    🧪 <?php esc_html_e('Szybki Tester Treści', 'flexext-wcag-guard'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-wcag-guard&tab=pro')); ?>" class="nav-tab <?php echo $activeTab === 'pro' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600; color: #047857;">
                    ⭐ <?php esc_html_e('Wersja PRO & Porównanie', 'flexext-wcag-guard'); ?>
                </a>
            </nav>

            <!-- TAB 1: SETTINGS -->
            <?php if ($activeTab === 'settings'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php esc_html_e('Konfiguracja Strażnika Przedpublikacyjnego', 'flexext-wcag-guard'); ?>
                    </h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('flx_wcag_admin_nonce', 'flx_wcag_nonce'); ?>
                        <input type="hidden" name="flx_wcag_save_action" value="save_settings">

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e('Blokada Publikacji', 'flexext-wcag-guard'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="block_publish_on_error" value="1" <?php checked(1, $settings['block_publish_on_error']); ?>>
                                        <strong><?php esc_html_e('Zablokuj publikację wpisu / strony, jeśli wykryto błędy WCAG', 'flexext-wcag-guard'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Gdy włączone, redaktor nie może opublikować wpisu ze statusem "Opublikowano", dopóki nie usunie błędów kontrastu, brakujących altów lub złej hierarchii nagłówków.', 'flexext-wcag-guard'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Kontrast Kolorów (WCAG 1.4.3)', 'flexext-wcag-guard'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="check_contrast" value="1" <?php checked(1, $settings['check_contrast']); ?>>
                                        <?php esc_html_e('Weryfikuj minimalny współczynnik kontrastu tekstu do tła (min. 4.5:1 / 3:1 dla dużego tekstu)', 'flexext-wcag-guard'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Opisy Alternatywne (WCAG 1.1.1)', 'flexext-wcag-guard'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="check_alt_text" value="1" <?php checked(1, $settings['check_alt_text']); ?>>
                                        <?php esc_html_e('Wymagaj atrybutu alt dla wszystkich osadzonych obrazów (wykrywa brakujące i puste opisy)', 'flexext-wcag-guard'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Hierarchia Nagłówków (WCAG 1.3.1)', 'flexext-wcag-guard'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="check_headings" value="1" <?php checked(1, $settings['check_headings']); ?>>
                                        <?php esc_html_e('Sprawdzaj kolejność poziomów nagłówków (zakaz przeskakiwania np. z H1 bezpośrednio do H3)', 'flexext-wcag-guard'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Czytelność Linków (WCAG 2.4.4)', 'flexext-wcag-guard'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="check_link_text" value="1" <?php checked(1, $settings['check_link_text']); ?>>
                                        <?php esc_html_e('Wykrywaj niejednoznaczne linki (np. "kliknij tutaj", "więcej", "czytaj")', 'flexext-wcag-guard'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary" style="font-weight: 600;">
                                💾 <?php esc_html_e('Zapisz Ustawienia Strażnika', 'flexext-wcag-guard'); ?>
                            </button>
                        </p>
                    </form>
                </div>

            <!-- TAB 2: TESTER -->
            <?php elseif ($activeTab === 'tester'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php esc_html_e('Szybki Tester i Remediacja Dostępności Treści', 'flexext-wcag-guard'); ?>
                    </h2>
                    <p style="color: #475569; font-size: 13px;">
                        Wklej dowolny kod HTML lub treść artykułu, aby natychmiast przetestować zgodność z WCAG 2.2 AA / EAA oraz zobaczyć automatyczną naprawę.
                    </p>

                    <div style="margin-top: 16px;">
                        <textarea id="flx-wcag-test-input" rows="8" style="width: 100%; font-family: monospace; font-size: 13px; padding: 10px;" placeholder="<h2>Przykładowy nagłówek</h2><img src='sample.jpg'><p style='color: #ccc; background: #fff;'>Tekst ze słabym kontrastem</p><a href='#'>kliknij tutaj</a>"></textarea>
                    </div>

                    <div style="margin-top: 12px; display: flex; gap: 10px;">
                        <button type="button" id="flx-wcag-btn-test" class="button button-primary" style="font-weight: 600;">
                            🔍 <?php esc_html_e('Uruchom Audyt WCAG', 'flexext-wcag-guard'); ?>
                        </button>
                        <button type="button" id="flx-wcag-btn-autofix" class="button" style="font-weight: 600; color: #059669;">
                            ✨ <?php esc_html_e('1-Klik Napraw (Auto-Remediate)', 'flexext-wcag-guard'); ?>
                        </button>
                    </div>

                    <div id="flx-wcag-test-results" style="margin-top: 20px; display: none;">
                        <h4 style="margin-bottom: 8px; font-size: 14px; color: #0f172a;"><?php esc_html_e('Wyniki audytu dostępności:', 'flexext-wcag-guard'); ?></h4>
                        <div id="flx-wcag-test-output" style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 6px; font-family: monospace; font-size: 12px; line-height: 1.5; max-height: 350px; overflow-y: auto;"></div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var btnTest = document.getElementById('flx-wcag-btn-test');
                        var btnFix = document.getElementById('flx-wcag-btn-autofix');
                        var input = document.getElementById('flx-wcag-test-input');
                        var resDiv = document.getElementById('flx-wcag-test-results');
                        var output = document.getElementById('flx-wcag-test-output');

                        btnTest.addEventListener('click', function() {
                            var content = input.value.trim();
                            if (!content) { alert('Wprowadź treść do audytu!'); return; }
                            resDiv.style.display = 'block';
                            output.innerText = 'Trwa audytowanie treści pod kątem WCAG 2.2 AA...';

                            var form = new FormData();
                            form.append('action', 'eaaguard_audit');
                            form.append('nonce', '<?php echo wp_create_nonce('eaaguard_editor_nonce'); ?>');
                            form.append('content', content);

                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: form })
                            .then(function(r) { return r.json(); })
                            .then(function(d) {
                                output.innerText = JSON.stringify(d.data || d, null, 2);
                            })
                            .catch(function(e) { output.innerText = 'Błąd: ' + e.message; });
                        });

                        btnFix.addEventListener('click', function() {
                            var content = input.value.trim();
                            if (!content) { alert('Wprowadź treść do naprawy!'); return; }
                            resDiv.style.display = 'block';
                            output.innerText = 'Trwa automatyczna naprawa błędów WCAG...';

                            var form = new FormData();
                            form.append('action', 'eaaguard_autofix');
                            form.append('nonce', '<?php echo wp_create_nonce('eaaguard_editor_nonce'); ?>');
                            form.append('content', content);

                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: form })
                            .then(function(r) { return r.json(); })
                            .then(function(d) {
                                if (d.success && d.data.remediatedHtml) {
                                    input.value = d.data.remediatedHtml;
                                    output.innerText = 'Naprawa zakończona! Zaktualizowano pole edycji powyżej.';
                                } else {
                                    output.innerText = JSON.stringify(d, null, 2);
                                }
                            })
                            .catch(function(e) { output.innerText = 'Błąd: ' + e.message; });
                        });
                    });
                    </script>
                </div>

            <!-- TAB 3: PRO COMPARISON -->
            <?php elseif ($activeTab === 'pro'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                        <div>
                            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">
                                🚀 Rozszerz ochronę z WCAG 2.2 &amp; EAA Guard PRO
                            </h2>
                            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">
                                Zyskaj audytor plików PDF, automatyczny generator Deklaracji Dostępności oraz skaner całego serwisu.
                            </p>
                        </div>
                        <div>
                            <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #059669; border-color: #047857; font-size: 15px; font-weight: 700; padding: 8px 20px; height: auto;">
                                🛒 Kup Wersję PRO za $25 na flexext.dev
                            </a>
                        </div>
                    </div>

                    <h3 style="margin-top: 26px; font-size: 16px; color: #0f172a; font-weight: 700;">
                        Tabela porównawcza: Wersja Free vs PRO
                    </h3>

                    <table class="widefat striped" style="margin-top: 14px; border: 1px solid #cbd5e1;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700;">Funkcjonalność</th>
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: center; width: 140px;">Wersja Free</th>
                                <th style="padding: 12px 16px; font-size: 14px; font-weight: 700; text-align: center; width: 160px; color: #047857;">Wersja PRO ($25)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px 16px;"><strong>Strażnik edytora Gutenberg i Classic w czasie rzeczywistym</strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong>Weryfikator kontrastu tekstu do tła (WCAG 1.4.3 AA)</strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong>Wykrywanie brakujących opisów alternatywnych (alt)</strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong>Kontrola kolejności nagłówków (H1-H6)</strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong>Silnik 1-Klik Napraw (Auto-Remediation)</strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong>Audytor plików PDF pod kątem dostępności (WCAG PDF/UA)</strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;">✅ Wersja PRO</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong>Generator Ustawowej Deklaracji Dostępności (EAA 2025)</strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;">✅ Wersja PRO</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong>Skaner całego serwisu (Automatyczny Crawler URL)</strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;">✅ Wersja PRO</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong>Frontendowy widget paska dostępności dla osób słabowidzących</strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;">✅ Wersja PRO</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong>Dedykowane wsparcie komercyjne 1-na-1 i SLA</strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;">✅ Wersja PRO</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 24px; text-align: center;">
                        <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #059669; border-color: #047857; font-size: 16px; font-weight: 700; padding: 10px 28px; height: auto;">
                            👉 Przejdź do sklepu flexext.dev i kup wersję PRO za $25
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }
}
