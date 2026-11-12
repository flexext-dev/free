<?php
namespace Opencart\Catalog\Controller\Extension\FlexextCharLimitFreeLiteOc4\Module;

class CharLimitLite extends \Opencart\System\Engine\Controller {
    public function eventProductAfter(string &$route, array &$args, string &$output): void {
        if (!$this->config->get('module_char_limit_lite_status')) {
            return;
        }

        $limit = (int)($this->config->get('module_char_limit_lite_default_limit') ?: 100);

        $js = "
<!-- FlexExt Option Character Limit Lite -->
<style>
.flexext-char-limit-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    font-weight: 500;
    padding: 3px 10px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    margin-top: 6px;
    transition: all 0.2s ease;
}
.flexext-char-limit-badge.danger {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
</style>
<script>
(function() {
    function initCharLimitLite() {
        var maxLimit = " . $limit . ";
        var inputs = document.querySelectorAll('input[type=\"text\"][name^=\"option[\"], textarea[name^=\"option[\"]');
        inputs.forEach(function(el) {
            if (el.dataset.charLimitLiteInit) return;
            el.dataset.charLimitLiteInit = '1';
            el.setAttribute('maxlength', maxLimit);
            
            var badge = document.createElement('div');
            badge.className = 'flexext-char-limit-badge';
            badge.innerHTML = '<span class=\"cur\">0</span> / ' + maxLimit + ' chars remaining';
            if (el.nextSibling) {
                el.parentNode.insertBefore(badge, el.nextSibling);
            } else {
                el.parentNode.appendChild(badge);
            }

            function update() {
                var len = el.value.length;
                var rem = Math.max(0, maxLimit - len);
                var curSpan = badge.querySelector('.cur');
                if (curSpan) curSpan.textContent = len;
                if (rem === 0) {
                    badge.classList.add('danger');
                } else {
                    badge.classList.remove('danger');
                }
            }
            el.addEventListener('input', update);
            update();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharLimitLite);
    } else {
        initCharLimitLite();
    }
})();
</script>
<!-- /FlexExt Option Character Limit Lite -->
";

        if (strpos($output, '</body>') !== false) {
            $output = str_replace('</body>', $js . '</body>', $output);
        } else {
            $output .= $js;
        }
    }
}

// Fallback alias in case someone extracts with code flexext_char_limit_lite_oc4
if (!class_exists('Opencart\Catalog\Controller\Extension\FlexextCharLimitLiteOc4\Module\CharLimitLite', false)) {
    class_alias(
        \Opencart\Catalog\Controller\Extension\FlexextCharLimitFreeLiteOc4\Module\CharLimitLite::class,
        'Opencart\Catalog\Controller\Extension\FlexextCharLimitLiteOc4\Module\CharLimitLite'
    );
}
