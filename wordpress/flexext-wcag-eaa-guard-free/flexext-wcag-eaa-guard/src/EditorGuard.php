<?php
declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pre-Publish Gatekeeper & Editor Assets Enqueuer for Gutenberg & Classic Editor (Free Edition).
 */
final class EditorGuard
{
    public static function init(): void
    {
        // Enqueue assets for Gutenberg Block Editor
        add_action('enqueue_block_editor_assets', [self::class, 'enqueueBlockEditorAssets']);

        // Enqueue assets for Classic Editor / TinyMCE
        add_action('admin_enqueue_scripts', [self::class, 'enqueueClassicEditorAssets']);

        // Server-side pre-publish gatekeeper
        add_filter('wp_insert_post_data', [self::class, 'guardPostSave'], 10, 2);
        add_filter('rest_pre_insert_post', [self::class, 'guardRestPostSave'], 10, 2);
        add_filter('rest_pre_insert_page', [self::class, 'guardRestPostSave'], 10, 2);

        // Also dynamically hook all other public REST post types
        add_action('init', function () {
            $postTypes = get_post_types(['show_in_rest' => true], 'names');
            foreach ($postTypes as $pt) {
                if (!in_array($pt, ['post', 'page'], true)) {
                    add_filter("rest_pre_insert_{$pt}", [self::class, 'guardRestPostSave'], 10, 2);
                }
            }
        }, 99);

        // Admin notice when post publication is blocked
        add_action('admin_notices', [self::class, 'renderBlockedNotice']);

        // AJAX handlers for real-time audit & auto-fix
        add_action('wp_ajax_eaaguard_autofix', [self::class, 'ajaxAutoFix']);
        add_action('wp_ajax_eaaguard_audit', [self::class, 'ajaxAudit']);
    }

    /**
     * Enqueue Gutenberg assets
     */
    public static function enqueueBlockEditorAssets(): void
    {
        $isPl = str_starts_with(strtolower(get_user_locale()), 'pl');

        wp_enqueue_style(
            'flexext-wcag-guard-editor-style',
            plugins_url('assets/css/editor-wcag-guard.css', FLEXEXT_WCAG_GUARD_FILE),
            [],
            FLEXEXT_WCAG_GUARD_VERSION
        );

        wp_enqueue_script(
            'flexext-wcag-guard-editor-script',
            plugins_url('assets/js/editor-wcag-guard.js', FLEXEXT_WCAG_GUARD_FILE),
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-editor', 'wp-dom-ready'],
            FLEXEXT_WCAG_GUARD_VERSION,
            true
        );

        wp_localize_script('flexext-wcag-guard-editor-script', 'EaaGuardConfig', [
            'lang'        => $isPl ? 'pl' : 'en',
            'strictMode'  => true,
            'isLicensed'  => true, // 100% Free and unlocked in Community Edition
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('eaaguard_editor_nonce'),
            'i18n'        => [
                'publishBlocked' => $isPl ? 'Publikacja wstrzymana: wykryto błędy dostępności WCAG 2.2 / EAA!' : 'Publication paused: WCAG 2.2 / EAA accessibility violations detected!',
                'missingAlt'     => $isPl ? 'Obraz musi posiadać opis alternatywny (alt) zgodny z WCAG 1.1.1.' : 'Image must have alternative text (alt) per WCAG 1.1.1.',
                'headingOrder'   => $isPl ? 'Błąd hierarchii nagłówków: pominięto poziom struktury (np. po H1 następuje H3).' : 'Heading hierarchy error: skipped structure level (e.g. H1 followed by H3).',
                'tableNoHeaders' => $isPl ? 'Tabela musi posiadać nagłówki (elementy th ze scope) zgodne z WCAG 1.3.1.' : 'Table must include header cells (th elements with scope) per WCAG 1.3.1.',
            ]
        ]);
    }

    /**
     * Enqueue Classic Editor / TinyMCE assets
     */
    public static function enqueueClassicEditorAssets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $currentScreen = get_current_screen();
        if ($currentScreen && method_exists($currentScreen, 'is_block_editor') && $currentScreen->is_block_editor()) {
            return;
        }

        $isPl = str_starts_with(strtolower(get_user_locale()), 'pl');

        wp_enqueue_style(
            'flexext-wcag-guard-editor-style',
            plugins_url('assets/css/editor-wcag-guard.css', FLEXEXT_WCAG_GUARD_FILE),
            [],
            FLEXEXT_WCAG_GUARD_VERSION
        );

        wp_enqueue_script(
            'flexext-wcag-guard-editor-script',
            plugins_url('assets/js/editor-wcag-guard.js', FLEXEXT_WCAG_GUARD_FILE),
            ['jquery'],
            FLEXEXT_WCAG_GUARD_VERSION,
            true
        );

        wp_localize_script('flexext-wcag-guard-editor-script', 'EaaGuardConfig', [
            'lang'        => $isPl ? 'pl' : 'en',
            'strictMode'  => true,
            'isLicensed'  => true,
            'isClassic'   => true,
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('eaaguard_editor_nonce'),
            'i18n'        => []
        ]);
    }

    /**
     * Server-side Pre-Publish Gatekeeper
     */
    public static function guardPostSave(array $data, array $postarr): array
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $data;
        }

        if (wp_is_post_revision($postarr['ID'] ?? 0)) {
            return $data;
        }

        $newStatus = $data['post_status'] ?? '';
        if ($newStatus !== 'publish') {
            return $data;
        }

        $settings = get_option('flexext_wcag_guard_settings', []);
        $strict = !empty($settings['block_publish_on_error']);
        if (!$strict) {
            return $data;
        }

        $content = $data['post_content'] ?? '';
        $audit = ContentAuditor::audit($content);

        if (!empty($audit['hasErrors'])) {
            $data['post_status'] = 'draft';
            set_transient('flexext_wcag_blocked_' . get_current_user_id(), [
                'postId' => $postarr['ID'] ?? 0,
                'errors' => $audit['errors'] ?? []
            ], 45);
        }

        return $data;
    }

    /**
     * REST API Guard
     */
    public static function guardRestPostSave($prepared_post, $request)
    {
        $status = $request->get_param('status');
        if ($status !== 'publish') {
            return $prepared_post;
        }

        $settings = get_option('flexext_wcag_guard_settings', []);
        if (empty($settings['block_publish_on_error'])) {
            return $prepared_post;
        }

        $content = $prepared_post->post_content ?? '';
        $audit = ContentAuditor::audit($content);

        if (!empty($audit['hasErrors'])) {
            return new \WP_Error(
                'rest_wcag_guard_blocked',
                __('Publikacja zablokowana przez Strażnika WCAG / EAA: treść zawiera krytyczne błędy dostępności.', 'flexext-wcag-guard'),
                ['status' => 400, 'wcag_errors' => $audit['errors'] ?? []]
            );
        }

        return $prepared_post;
    }

    /**
     * Admin notice when publication is blocked
     */
    public static function renderBlockedNotice(): void
    {
        $data = get_transient('flexext_wcag_blocked_' . get_current_user_id());
        if (!$data) {
            return;
        }
        delete_transient('flexext_wcag_blocked_' . get_current_user_id());

        $errors = $data['errors'] ?? [];
        ?>
        <div class="notice notice-error is-dismissible" style="border-left-width: 4px; border-color: #ef4444;">
            <p><strong>⚠️ <?php esc_html_e('Publikacja wstrzymana przez Strażnika WCAG / EAA!', 'flexext-wcag-guard'); ?></strong></p>
            <p><?php esc_html_e('Wpis został cofnięty do wersji roboczej (Szkic), ponieważ zawiera błędy dostępności cyfrowej:', 'flexext-wcag-guard'); ?></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo esc_html(is_array($err) ? ($err['message'] ?? '') : $err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * AJAX: Live Audit
     */
    public static function ajaxAudit(): void
    {
        check_ajax_referer('eaaguard_editor_nonce', 'nonce');
        $content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        $audit = ContentAuditor::audit((string)$content);
        wp_send_json_success($audit);
    }

    /**
     * AJAX: Auto-Fix
     */
    public static function ajaxAutoFix(): void
    {
        check_ajax_referer('eaaguard_editor_nonce', 'nonce');
        $content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
        $fixed = ContentAuditor::autoRemediate((string)$content);
        wp_send_json_success(['remediatedHtml' => $fixed]);
    }
}
