<?php
namespace Opencart\Catalog\Controller\Extension\FlexextProductTabsFreeLiteOc4\Module;

class ProductTabsLite extends \Opencart\System\Engine\Controller {
    public function eventCatalogProduct(string &$route, array &$args, mixed &$output): void {
        if (!$this->config->get('module_product_tabs_lite_status')) {
            return;
        }

        if (strpos($output, 'id="tab-custom-lite"') !== false) {
            return;
        }

        $lang_id = (int)$this->config->get('config_language_id');
        $descriptions = $this->config->get('module_product_tabs_lite_description');

        $title = !empty($descriptions[$lang_id]['title']) ? $descriptions[$lang_id]['title'] : '';
        $description = !empty($descriptions[$lang_id]['description']) ? html_entity_decode($descriptions[$lang_id]['description'], ENT_QUOTES, 'UTF-8') : '';

        // If current language description is empty, check fallback language
        if (empty($title) && is_array($descriptions)) {
            foreach ($descriptions as $d) {
                if (!empty($d['title'])) {
                    $title = $d['title'];
                    $description = html_entity_decode($d['description'] ?? '', ENT_QUOTES, 'UTF-8');
                    break;
                }
            }
        }

        if (empty($title) && empty($description)) {
            return;
        }

        $icon = $this->config->get('module_product_tabs_lite_icon') ?: 'fa-solid fa-ruler-combined';

        // 1. Inject Tab Button into <ul class="nav nav-tabs">
        $tab_button = '<li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#tab-custom-lite" aria-controls="tab-custom-lite" aria-selected="false"><i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' me-1"></i> ' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</button></li>';

        $pattern = '/(<ul[^>]*class="[^"]*nav-tabs[^"]*"[^>]*>.*?)(<\/ul>)/is';
        if (preg_match($pattern, $output)) {
            $output = preg_replace($pattern, '$1' . $tab_button . '$2', $output, 1);
        }

        // 2. Inject Tab Pane into <div class="tab-content">
        $tab_pane = '<div id="tab-custom-lite" class="tab-pane fade mb-4">';
        $tab_pane .= '  <div class="p-3 bg-white border rounded">';
        $tab_pane .= '    ' . $description;
        $tab_pane .= '  </div>';
        $tab_pane .= '</div>';

        if (preg_match('/(<div[^>]*class="[^"]*tab-content[^"]*"[^>]*>.*?)(<\/div>\s*<\/div>)/is', $output, $m)) {
            $output = preg_replace('/(<div[^>]*class="[^"]*tab-content[^"]*"[^>]*>)(.*?)(<\/div>)/is', '$1$2' . $tab_pane . '$3', $output, 1);
        }
    }
}

// Fallback alias in case someone extracts with code flexext_product_tabs_lite_oc4
if (!class_exists('Opencart\Catalog\Controller\Extension\FlexextProductTabsLiteOc4\Module\ProductTabsLite', false)) {
    class_alias(
        \Opencart\Catalog\Controller\Extension\FlexextProductTabsFreeLiteOc4\Module\ProductTabsLite::class,
        'Opencart\Catalog\Controller\Extension\FlexextProductTabsLiteOc4\Module\ProductTabsLite'
    );
}
