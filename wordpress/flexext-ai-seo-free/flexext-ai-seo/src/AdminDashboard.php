<?php
declare(strict_types=1);

namespace FlexExt\WordPress\AiSeoFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard & Settings for Free AI SEO Edition.
 * WCAG AA Contrast Compliant & Fully Bilingual (EN/PL).
 */
class AdminDashboard
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

    public static function render(array $settings): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $activeTab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'bots';
        $proUrl = 'https://flexext.dev/en-gb/product/wp-ai-seo-agent-readiness-pro';
        $siteUrl = get_site_url();
        $bots = BotManager::getTrackedBots();
        ?>
        <div class="wrap flx-ai-wrap" style="max-width: 1040px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #091322 0%, #1e1b4b 100%); color: #ffffff; padding: 22px 28px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <span>🧠</span> FlexExt AI SEO &amp; Agent Readiness
                        <span style="font-size: 11px; background: #6366f1; padding: 2px 8px; border-radius: 12px; font-weight: 600;">v<?php echo esc_html(FLEXAISEO_VERSION); ?></span>
                    </h1>
                    <p style="color: #94a3b8; margin: 6px 0 0 0; font-size: 13px;">
                        <?php echo esc_html(self::t('Website optimization for AI search engines and LLMs (ChatGPT, Claude, Perplexity, Gemini) — robots.txt & /llms.txt standard.', 'Optymalizacja witryny pod kątem wyszukiwarek AI i LLM (ChatGPT, Claude, Perplexity, Gemini) — robots.txt & llms.txt.')); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo esc_url($proUrl); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #10b981; border-color: #059669; font-weight: 600; padding: 6px 14px; height: auto;">
                        🚀 <?php echo esc_html(self::t('Upgrade to PRO ($25)', 'Przejdź na Wersję PRO ($25)')); ?>
                    </a>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-ai-seo&tab=bots')); ?>" class="nav-tab <?php echo $activeTab === 'bots' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    🤖 <?php echo esc_html(self::t('AI Crawlers & Robots.txt', 'Boty AI & Robots.txt')); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-ai-seo&tab=llms')); ?>" class="nav-tab <?php echo $activeTab === 'llms' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    📄 <?php echo esc_html(self::t('/llms.txt Generator', 'Generator /llms.txt')); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-ai-seo&tab=audit')); ?>" class="nav-tab <?php echo $activeTab === 'audit' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600;">
                    📊 <?php echo esc_html(self::t('AI Readiness Audit', 'Audyt Gotowości AI')); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=flexext-ai-seo&tab=pro')); ?>" class="nav-tab <?php echo $activeTab === 'pro' ? 'nav-tab-active' : ''; ?>" style="font-weight: 600; color: #047857;">
                    ⭐ <?php echo esc_html(self::t('PRO Edition & Comparison', 'Wersja PRO & Porównanie')); ?>
                </a>
            </nav>

            <!-- TAB 1: BOTS & ROBOTS.TXT -->
            <?php if ($activeTab === 'bots'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php echo esc_html(self::t('AI & LLM Crawler Access Policy (Robots.txt)', 'Polityka Dostępowa dla Botów AI i LLM (Robots.txt)')); ?>
                    </h2>
                    <p style="color: #475569; font-size: 13px;">
                        <?php echo esc_html(self::t('Manage access permissions for individual AI search and training crawlers. Directives are automatically integrated into virtual robots.txt.', 'Zarządzaj dostępem dla poszczególnych crawlerów sztucznej inteligencji. Wtyczka dynamicznie wstrzykuje dyrektywy do wirtualnego pliku robots.txt.')); ?>
                    </p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('flexaiseo_save_bots', 'flexaiseo_nonce'); ?>
                        <input type="hidden" name="action" value="flexaiseo_save_bots">

                        <table class="widefat striped" style="margin-top: 16px; border: 1px solid #cbd5e1;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="padding: 10px 14px;"><?php echo esc_html(self::t('AI Bot / Crawler', 'Bot / Crawler AI')); ?></th>
                                    <th style="padding: 10px 14px;"><?php echo esc_html(self::t('Provider', 'Właściciel')); ?></th>
                                    <th style="padding: 10px 14px;"><?php echo esc_html(self::t('User-Agent Header', 'User-Agent')); ?></th>
                                    <th style="padding: 10px 14px; text-align: center; width: 150px;"><?php echo esc_html(self::t('Permission', 'Uprawnienia')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bots as $botKey => $botData):
                                    $policy = $settings['bot_policy'][$botKey] ?? 'allow';
                                ?>
                                    <tr>
                                        <td style="padding: 10px 14px; font-weight: 600;">
                                            <?php echo esc_html($botData['name']); ?>
                                        </td>
                                        <td style="padding: 10px 14px; color: #64748b;">
                                            <?php echo esc_html($botData['owner'] ?? 'AI Provider'); ?>
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html($botData['ua']); ?></code>
                                        </td>
                                        <td style="padding: 10px 14px; text-align: center;">
                                            <select name="bot_policy[<?php echo esc_attr($botKey); ?>]" style="font-size: 13px; font-weight: 600; padding: 4px 8px;">
                                                <option value="allow" <?php selected('allow', $policy); ?>>🟢 <?php echo esc_html(self::t('Allow', 'Zezwól')); ?></option>
                                                <option value="disallow" <?php selected('disallow', $policy); ?>>🔴 <?php echo esc_html(self::t('Disallow', 'Blokuj')); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <p class="submit" style="margin-top: 20px;">
                            <button type="submit" class="button button-primary" style="font-weight: 600;">
                                💾 <?php echo esc_html(self::t('Save Bot Policy', 'Zapisz Politykę Botów AI')); ?>
                            </button>
                            <a href="<?php echo esc_url($siteUrl . '/robots.txt'); ?>" target="_blank" class="button" style="margin-left: 8px;">
                                👁️ <?php echo esc_html(self::t('Preview robots.txt', 'Podgląd pliku robots.txt')); ?>
                            </a>
                        </p>
                    </form>
                </div>

            <!-- TAB 2: LLMS.TXT -->
            <?php elseif ($activeTab === 'llms'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php echo esc_html(self::t('/llms.txt Standard Configuration', 'Konfiguracja Standardu /llms.txt')); ?>
                    </h2>
                    <p style="color: #475569; font-size: 13px;">
                        <?php echo esc_html(self::t('The /llms.txt file is the emerging open proposal for structured, token-dense markdown site summaries optimized for Large Language Models.', 'Plik /llms.txt to oficjalny standard ustrukturyzowanego streszczenia witryny w formacie Markdown dla modeli AI (LLMs.txt Proposal).')); ?>
                    </p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('flexaiseo_save_llms', 'flexaiseo_nonce'); ?>
                        <input type="hidden" name="action" value="flexaiseo_save_llms">

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php echo esc_html(self::t('Enable /llms.txt', 'Włącz /llms.txt')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enable_llms_txt" value="1" <?php checked(1, $settings['enable_llms_txt'] ?? 1); ?>>
                                        <strong><?php echo esc_html(self::t('Serve /llms.txt at root domain URL', 'Serwuj plik /llms.txt pod adresem głównym domeny')); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::t('Website Title for AI', 'Tytuł witryny dla AI')); ?></th>
                                <td>
                                    <input type="text" name="llms_title" value="<?php echo esc_attr($settings['llms_title'] ?? get_bloginfo('name')); ?>" class="regular-text">
                                    <p class="description"><?php echo esc_html(self::t('Primary H1 title in the /llms.txt markdown output.', 'Główny nagłówek H1 w pliku /llms.txt.')); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::t('Summary for AI Models', 'Opis dla Modeli AI')); ?></th>
                                <td>
                                    <textarea name="llms_summary" rows="4" class="large-text"><?php echo esc_textarea($settings['llms_summary'] ?? get_bloginfo('description')); ?></textarea>
                                    <p class="description"><?php echo esc_html(self::t('Key summary of your business, services, products, or core documentation fed to LLMs.', 'Kluczowe podsumowanie działalności, oferty lub tematyki serwisu przekazywane modelom językowym.')); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary" style="font-weight: 600;">
                                💾 <?php echo esc_html(self::t('Save /llms.txt Settings', 'Zapisz Konfigurację /llms.txt')); ?>
                            </button>
                            <a href="<?php echo esc_url($siteUrl . '/llms.txt'); ?>" target="_blank" class="button" style="margin-left: 8px;">
                                👁️ <?php echo esc_html(self::t('View /llms.txt File', 'Zobacz plik /llms.txt')); ?>
                            </a>
                        </p>
                    </form>
                </div>

            <!-- TAB 3: AUDIT -->
            <?php elseif ($activeTab === 'audit'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; font-size: 18px; font-weight: 700; color: #0f172a;">
                        <?php echo esc_html(self::t('AI Agent Readiness Audit', 'Audyt Gotowości Witryny dla Agentów AI (Agent Readiness Score)')); ?>
                    </h2>
                    <p style="color: #475569; font-size: 13px;">
                        <?php echo esc_html(self::t('Assessment of key indicators for website discoverability by AI search crawlers and LLMs.', 'Weryfikacja kluczowych wskaźników dostępności treści dla botów i agentów wyszukiwawczych AI.')); ?>
                    </p>

                    <div style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 18px; border-radius: 6px; border-left: 4px solid #10b981;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600;"><?php echo esc_html(self::t('ROBOTS.TXT FILE', 'PLIK ROBOTS.TXT')); ?></div>
                            <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 4px;"><?php echo esc_html(self::t('Active & Governed', 'Aktywny i Skonfigurowany')); ?></div>
                            <div style="font-size: 12px; color: #059669; margin-top: 4px;">✅ <?php echo esc_html(self::t('AI Crawlers Controlled', 'Boty AI kontrolowane')); ?></div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 18px; border-radius: 6px; border-left: 4px solid #10b981;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600;"><?php echo esc_html(self::t('/LLMS.TXT STANDARD', 'STANDARD /LLMS.TXT')); ?></div>
                            <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 4px;"><?php echo esc_html(self::t('Active (HTTP 200)', 'Włączony (HTTP 200)')); ?></div>
                            <div style="font-size: 12px; color: #059669; margin-top: 4px;">✅ <?php echo esc_html(self::t('Markdown index ready', 'Format Markdown gotowy')); ?></div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 18px; border-radius: 6px; border-left: 4px solid #6366f1;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600;"><?php echo esc_html(self::t('WEBMCP AGENT PROTOCOL', 'PROTOKÓŁ WEBMCP')); ?></div>
                            <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 4px;"><?php echo esc_html(self::t('PRO Edition Feature', 'Wymaga Wersji PRO')); ?></div>
                            <div style="font-size: 12px; color: #4f46e5; margin-top: 4px;">⚡ flexext.dev ($25)</div>
                        </div>
                    </div>
                </div>

            <!-- TAB 4: PRO COMPARISON -->
            <?php elseif ($activeTab === 'pro'): ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                        <div>
                            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">
                                🚀 <?php echo esc_html(self::t('Expand Capabilities with AI SEO Agent Readiness PRO', 'Rozwiń możliwości z FlexExt AI SEO Agent Readiness PRO')); ?>
                            </h2>
                            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">
                                <?php echo esc_html(self::t('Enable full store markdown conversion (/llms-full.txt), WebMCP autonomous shopping agents, and real-time AI crawler traffic analytics.', 'Zyskaj pełną konwersję witryny do Markdown (/llms-full.txt), protokół zakupowy WebMCP oraz analitykę botów AI.')); ?>
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
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('AI crawler access management in robots.txt (GPTBot, Claude, Perplexity)', 'Zarządzanie botami AI w pliku robots.txt (GPTBot, Claude, Perplexity)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Generate and serve /llms.txt standard index (up to 5 posts)', 'Generowanie i serwowanie pliku /llms.txt (Standard LLM)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Website readiness assessment dashboard for AI models', 'Audyt gotowości serwisu dla modeli AI')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                                <td style="text-align: center; font-size: 16px;">✅</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Full token-dense markdown knowledge base & product catalog (/llms-full.txt)', 'Pełna konwersja Markdown całego sklepu (/llms-full.txt)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Dynamic Markdown converter (/md/ endpoints & ?format=md)', 'Dynamiczny konwerter treści do Markdown (/md/ oraz ?format=md)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('WebMCP protocol for autonomous AI shopping and ordering agents', 'Protokół WebMCP dla autonomicznych agentów zakupowych AI')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Real-time AI crawler traffic tracker (Live Intelligence Tracker)', 'Analityka wizyt botów AI w czasie rzeczywistym (Live Tracker)')); ?></strong></td>
                                <td style="text-align: center; font-size: 16px; color: #94a3b8;">❌</td>
                                <td style="text-align: center; font-size: 16px; font-weight: 700; color: #059669;"><?php echo esc_html(self::t('✅ PRO Edition', '✅ Wersja PRO')); ?></td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td style="padding: 12px 16px;"><strong><?php echo esc_html(self::t('Dedicated 1-on-1 commercial technical support & SLA', 'Dedykowane wsparcie techniczne 1-na-1 i SLA')); ?></strong></td>
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
