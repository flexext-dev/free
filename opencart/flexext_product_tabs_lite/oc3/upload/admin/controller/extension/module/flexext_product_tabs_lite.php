<?php
class ControllerExtensionModuleFlexextProductTabsLite extends Controller {
    private $error = array();
    private string $version = '1.0.0';

    public function index() {
        $this->load->language('extension/module/flexext_product_tabs_lite');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_product_tabs_lite', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/flexext_product_tabs_lite', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/flexext_product_tabs_lite', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_product_tabs_lite_status'])) {
            $data['status'] = $this->request->post['module_product_tabs_lite_status'];
        } else {
            $data['status'] = $this->config->get('module_product_tabs_lite_status') !== null ? $this->config->get('module_product_tabs_lite_status') : 1;
        }

        if (isset($this->request->post['module_product_tabs_lite_icon'])) {
            $data['tab_icon'] = $this->request->post['module_product_tabs_lite_icon'];
        } else {
            $data['tab_icon'] = $this->config->get('module_product_tabs_lite_icon') ?: 'fa fa-ruler';
        }

        if (isset($this->request->post['module_product_tabs_lite_sort_order'])) {
            $data['sort_order'] = $this->request->post['module_product_tabs_lite_sort_order'];
        } else {
            $data['sort_order'] = $this->config->get('module_product_tabs_lite_sort_order') !== null ? $this->config->get('module_product_tabs_lite_sort_order') : 2;
        }

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['module_product_tabs_lite_description'])) {
            $descriptions = $this->request->post['module_product_tabs_lite_description'];
        } else {
            $descriptions = $this->config->get('module_product_tabs_lite_description');
        }

        if (!is_array($descriptions)) {
            $descriptions = array();
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

        $data['version'] = $this->version;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/flexext_product_tabs_lite', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/flexext_product_tabs_lite')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
