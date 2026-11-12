<?php
namespace Opencart\Admin\Controller\Extension\FlexextProductTabsFreeLiteOc4\Module;

class ProductTabsLite extends \Opencart\System\Engine\Controller {
    private string $version = '1.0.0';

    private function getExtensionCode(): string {
        return (strpos(__NAMESPACE__, 'FreeLite') !== false) ? 'flexext_product_tabs_free_lite_oc4' : 'flexext_product_tabs_lite_oc4';
    }

    public function index(): void {
        $code = $this->getExtensionCode();
        $this->ensureInstalled();

        $this->load->language('extension/' . $code . '/module/product_tabs_lite');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/' . $code . '/module/product_tabs_lite', 'user_token=' . $this->session->data['user_token'])]
        ];

        $data['save'] = $this->url->link('extension/' . $code . '/module/product_tabs_lite.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['status'] = $this->config->get('module_product_tabs_lite_status') !== null ? (int)$this->config->get('module_product_tabs_lite_status') : 1;
        $data['tab_icon'] = $this->config->get('module_product_tabs_lite_icon') ?: 'fa-solid fa-ruler-combined';
        $data['sort_order'] = $this->config->get('module_product_tabs_lite_sort_order') !== null ? (int)$this->config->get('module_product_tabs_lite_sort_order') : 2;

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $descriptions = $this->config->get('module_product_tabs_lite_description');
        if (!is_array($descriptions)) {
            $descriptions = [];
        }

        foreach ($data['languages'] as $lang) {
            if (empty($descriptions[$lang['language_id']]['title'])) {
                $descriptions[$lang['language_id']]['title'] = 'Size Guide / Shipping Info';
            }
            if (!isset($descriptions[$lang['language_id']]['description'])) {
                $descriptions[$lang['language_id']]['description'] = '';
            }
        }
        $data['descriptions'] = $descriptions;

        $data['ckeditor'] = $this->config->get('config_language_admin');
        $data['user_token'] = $this->session->data['user_token'];
        $data['version'] = $this->version;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/' . $code . '/module/product_tabs_lite', $data));
    }

    public function save(): void {
        $code = $this->getExtensionCode();
        $this->load->language('extension/' . $code . '/module/product_tabs_lite');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/' . $code . '/module/product_tabs_lite')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_product_tabs_lite', $this->request->post);
            $this->ensureInstalled();
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void {
        $this->ensureInstalled();
    }

    public function uninstall(): void {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('flexext_product_tabs_lite');
    }

    private function ensureInstalled(): void {
        $code = $this->getExtensionCode();
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('flexext_product_tabs_lite');

        // 1. Storefront product page tab injection
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_product_tabs_lite',
            'description' => 'FlexExt Product Custom Tabs Lite Storefront',
            'trigger'     => 'catalog/view/product/product/after',
            'action'      => 'extension/' . $code . '/module/product_tabs_lite.eventCatalogProduct',
            'status'      => 1,
            'sort_order'  => 2
        ]);

        // 2. Admin Product Form Custom Tab injection
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_product_tabs_lite',
            'description' => 'FlexExt Product Custom Tabs Lite Product Form',
            'trigger'     => 'admin/view/catalog/product_form/after',
            'action'      => 'extension/' . $code . '/module/product_tabs_lite.eventProductForm',
            'status'      => 1,
            'sort_order'  => 2
        ]);
    }

    public function eventProductForm(string &$route, array &$args, mixed &$output): void {
        if (!$this->config->get('module_product_tabs_lite_status')) {
            return;
        }

        if (strpos($output, 'id="tab-custom-tabs-lite"') !== false || strpos($output, 'id="tab-custom-tabs"') !== false) {
            return;
        }

        $code = $this->getExtensionCode();
        $lang_id = (int)$this->config->get('config_language_id');
        $descriptions = $this->config->get('module_product_tabs_lite_description');
        $tab_title = !empty($descriptions[$lang_id]['title']) ? $descriptions[$lang_id]['title'] : 'Custom Tab';
        $tab_icon = $this->config->get('module_product_tabs_lite_icon') ?: 'fa-solid fa-ruler-combined';

        // 1. Nav button
        $nav_btn = '<li class="nav-item"><button type="button" data-bs-toggle="tab" data-bs-target="#tab-custom-tabs-lite" class="nav-link"><i class="' . $tab_icon . ' text-primary me-1"></i> Custom Tab <span class="badge bg-success small ms-1">Lite (1 Tab)</span></button></li>';
        $pattern = '/(<ul[^>]*class="[^"]*nav-tabs[^"]*"[^>]*>)(.*?)(<\/ul>)/is';
        if (preg_match($pattern, $output)) {
            $output = preg_replace($pattern, '$1$2' . $nav_btn . '$3', $output, 1);
        }

        // 2. Tab Pane with 1 Active Tab Preview + Aesthetic Upgrade Card
        $pane = '<div id="tab-custom-tabs-lite" class="tab-pane">';
        $pane .= '  <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 10px;">';
        $pane .= '    <div class="card-body p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">';
        $pane .= '      <div>';
        $pane .= '        <span class="badge bg-warning text-dark fw-bold mb-1">★ UNLOCK UNLIMITED TABS &amp; RULES (PRO)</span>';
        $pane .= '        <div class="fw-bold text-white small">Chcesz nieograniczoną liczbę zakładek, przypisywanie do kategorii, reguły klientów i wideo?</div>';
        $pane .= '        <div class="text-white-50" style="font-size: 0.8rem;">Pobierz wersję PRO na <strong>FlexExt.dev</strong> (10% rabatu z kodem promocyjnym <strong>OPENCART10</strong>).</div>';
        $pane .= '      </div>';
        $pane .= '      <div class="text-nowrap">';
        $pane .= '        <a href="https://flexext.dev/?utm_source=opencart_marketplace&utm_medium=free_lite_product_tab&utm_campaign=product_tabs_lite" target="_blank" rel="noopener" class="btn btn-primary btn-sm px-3 fw-bold" style="background: #0284c7; border: none;">';
        $pane .= '          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Pobierz PRO (10% OFF Kod: OPENCART10)';
        $pane .= '        </a>';
        $pane .= '      </div>';
        $pane .= '    </div>';
        $pane .= '  </div>';

        $pane .= '  <div class="card border shadow-sm mb-3">';
        $pane .= '    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">';
        $pane .= '      <div class="d-flex align-items-center gap-2">';
        $pane .= '        <i class="' . $tab_icon . ' text-primary"></i> <strong class="text-dark">' . htmlspecialchars($tab_title, ENT_QUOTES, 'UTF-8') . '</strong>';
        $pane .= '        <span class="badge bg-success">Active 1st Custom Tab</span>';
        $pane .= '      </div>';
        $pane .= '      <div>';
        $pane .= '        <a href="' . $this->url->link('extension/' . $code . '/module/product_tabs_lite', 'user_token=' . $this->session->data['user_token']) . '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-gear me-1"></i> Edit Global Tab Content</a>';
        $pane .= '        <button type="button" class="btn btn-secondary btn-sm ms-1" onclick="alert(\'★ Need more than 1 custom tab? Upgrade to FlexExt Product Custom Tabs PRO to unlock unlimited tabs, category rules, customer groups & video embeds! (Use 10% coupon: OPENCART10)\');"><i class="fa-solid fa-lock me-1"></i> + Add 2nd Tab [PRO Feature]</button>';
        $pane .= '      </div>';
        $pane .= '    </div>';
        $pane .= '    <div class="card-body p-3">';
        $pane .= '      <div class="text-muted small mb-2"><i class="fa-solid fa-circle-info text-info me-1"></i> This custom tab is active storewide. To edit the title, content, icon, and sort order, open module settings or upgrade to the PRO suite for individual product overrides and unlimited tabs.</div>';
        $pane .= '    </div>';
        $pane .= '  </div>';
        $pane .= '</div>';

        $content_pattern = '/(<div[^>]*class="[^"]*tab-content[^"]*"[^>]*>)(.*?)(<\/form>|<\/div>\s*<\/div>\s*<\/div>\s*\{\{\s*footer)/is';
        if (preg_match('/(<div[^>]*class="[^"]*tab-content[^"]*"[^>]*>)/is', $output, $m)) {
            $output = preg_replace('/(<div[^>]*class="[^"]*tab-content[^"]*"[^>]*>)/is', '$1' . $pane, $output, 1);
        }
    }
}

// Fallback alias in case someone extracts with code flexext_product_tabs_lite_oc4
if (!class_exists('Opencart\Admin\Controller\Extension\FlexextProductTabsLiteOc4\Module\ProductTabsLite', false)) {
    class_alias(
        \Opencart\Admin\Controller\Extension\FlexextProductTabsFreeLiteOc4\Module\ProductTabsLite::class,
        'Opencart\Admin\Controller\Extension\FlexextProductTabsLiteOc4\Module\ProductTabsLite'
    );
}
