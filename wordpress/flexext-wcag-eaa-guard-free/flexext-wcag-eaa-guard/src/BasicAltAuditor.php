<?php
declare(strict_types=1);

namespace FlexExt\WcagEaaGuardFree;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Basic Alt Auditor.
 * Checks on post save if images have missing alt attributes (WCAG 1.1.1).
 */
final class BasicAltAuditor
{
    public static function init(): void
    {
        add_action('save_post', [self::class, 'checkPostImagesOnSave'], 20, 2);
        add_action('admin_notices', [self::class, 'renderAdminNotice']);
    }

    public static function checkPostImagesOnSave(int $postId, \WP_Post $post): void
    {
        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        if (!in_array($post->post_type, ['post', 'page', 'product'], true)) {
            return;
        }

        $content      = $post->post_content;
        $missingCount = 0;

        if (preg_match_all('/<img\b([^>]*)>/i', $content, $matches)) {
            foreach ($matches[1] as $attrs) {
                if (!preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $attrs, $altMatch)) {
                    $missingCount++;
                } else {
                    $altVal = trim($altMatch[2]);
                    if ($altVal === '') {
                        $missingCount++;
                    }
                }
            }
        }

        update_post_meta($postId, '_flexeaag_missing_alts', $missingCount);

        if ($missingCount > 0) {
            set_transient('flexeaag_notice_' . $postId, $missingCount, 30);
        } else {
            delete_transient('flexeaag_notice_' . $postId);
        }
    }

    public static function renderAdminNotice(): void
    {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['post', 'edit'], true)) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $postId = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;
        if (!$postId) {
            return;
        }

        $missing = get_transient('flexeaag_notice_' . $postId);
        if ($missing !== false && (int) $missing > 0) {
            ?>
            <div class="notice notice-warning is-dismissible" style="border-left-color: #f59e0b;">
                <p>
                    <strong>♿ FlexExt Web Accessibility &amp; EAA Guard:</strong>
                    <?php
                    $alt_notice = sprintf(
                        /* translators: %d: count of images missing alt attribute */
                        esc_html__('Ten wpis zawiera %d obraz(ów) bez wymaganego opisu alternatywnego (alt). Uzupełnij je w edytorze mediów, aby spełnić kryterium WCAG 1.1.1.', 'flexext-wcag-eaa-guard'),
                        (int) $missing
                    );
                    echo esc_html($alt_notice);
                    ?>
                    <a href="https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-wordpress" target="_blank" rel="noopener noreferrer" style="margin-left: 8px; font-weight: 700; color: #059669;">
                        <?php esc_html_e('Automatyczna naprawa 1-klik w Wersji PRO &rarr;', 'flexext-wcag-eaa-guard'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}
