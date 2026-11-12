<?php
namespace Opencart\Catalog\Controller\Extension\FlexextHideQtyFreeLiteOc4\Module;

class HideQtyLite extends \Opencart\System\Engine\Controller {
    public function eventProductAfter(string &$route, array &$args, string &$output): void {
        if (!$this->config->get('module_hide_qty_lite_status')) {
            return;
        }

        $fullWidth = (bool)($this->config->get('module_hide_qty_lite_full_width') ?? 1);
        $fullWidthCss = $fullWidth ? '
#button-cart {
    display: block !important;
    width: 100% !important;
    border-radius: var(--bs-border-radius, 6px) !important;
}
.input-group:has(#button-cart) {
    display: block !important;
}
' : '';

        $injection = "
<!-- FlexExt Hide Quantity Field Lite -->
<style>
/* Hide quantity input and Qty label only */
#input-quantity,
input[name=\"quantity\"],
label[for=\"input-quantity\"],
.input-group:has(#button-cart) > .input-group-text,
.input-group:has(#button-cart) > input,
.qty-stepper,
.product-quantity {
    display: none !important;
}

/* Ensure Add to Cart button remains visible */
#button-cart {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

{$fullWidthCss}
</style>
<script>
(function() {
    function initHideQtyLite() {
        var qtyInput = document.getElementById('input-quantity') || document.querySelector('input[name=\"quantity\"]');
        if (qtyInput) {
            qtyInput.style.setProperty('display', 'none', 'important');
            var prev = qtyInput.previousElementSibling;
            if (prev && prev.classList.contains('input-group-text')) {
                prev.style.setProperty('display', 'none', 'important');
            }
        }
        var btnCart = document.getElementById('button-cart');
        if (btnCart) {
            btnCart.style.setProperty('display', 'block', 'important');
            " . ($fullWidth ? "
            btnCart.style.setProperty('width', '100%', 'important');
            btnCart.style.setProperty('border-radius', '6px', 'important');
            var parent = btnCart.closest('.input-group');
            if (parent) {
                parent.style.setProperty('display', 'block', 'important');
            }
            " : "") . "
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHideQtyLite);
    } else {
        initHideQtyLite();
    }
})();
</script>
<!-- /FlexExt Hide Quantity Field Lite -->
";

        if (strpos($output, '</body>') !== false) {
            $output = str_replace('</body>', $injection . '</body>', $output);
        } else {
            $output .= $injection;
        }
    }
}

if (!class_exists('Opencart\Catalog\Controller\Extension\FlexextHideQtyLiteOc4\Module\HideQtyLite', false)) {
    class_alias(
        \Opencart\Catalog\Controller\Extension\FlexextHideQtyFreeLiteOc4\Module\HideQtyLite::class,
        'Opencart\Catalog\Controller\Extension\FlexextHideQtyLiteOc4\Module\HideQtyLite'
    );
}
