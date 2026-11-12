<?php
namespace Opencart\Admin\Controller\Extension\FlexextCharLimitFreeLiteOc4\Module;

class CharLimitLite extends \Opencart\System\Engine\Controller {
    private string $version = '1.0.0';

    public function index(): void {
        $this->ensureInstalled();

        $this->load->language('extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite', 'user_token=' . $this->session->data['user_token'])]
        ];

        $data['save'] = $this->url->link('extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['status'] = $this->config->get('module_char_limit_lite_status') !== null ? (int)$this->config->get('module_char_limit_lite_status') : 1;
        $data['default_limit'] = $this->config->get('module_char_limit_lite_default_limit') ?: 100;
        $data['user_token'] = $this->session->data['user_token'];
        $data['version'] = $this->version;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite', $data));
    }

    public function save(): void {
        $this->load->language('extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_char_limit_lite', $this->request->post);
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
        $this->model_setting_event->deleteEventByCode('flexext_char_limit_lite');
    }

    private function ensureInstalled(): void {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('flexext_char_limit_lite');
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_char_limit_lite',
            'description' => 'FlexExt Option Character Limit Lite',
            'trigger'     => 'catalog/view/product/product/after',
            'action'      => 'extension/flexext_char_limit_free_lite_oc4/module/char_limit_lite.eventProductAfter',
            'status'      => 1,
            'sort_order'  => 2
        ]);
    }
}

// Fallback alias in case someone extracts with code flexext_char_limit_lite_oc4
if (!class_exists('Opencart\Admin\Controller\Extension\FlexextCharLimitLiteOc4\Module\CharLimitLite', false)) {
    class_alias(
        \Opencart\Admin\Controller\Extension\FlexextCharLimitFreeLiteOc4\Module\CharLimitLite::class,
        'Opencart\Admin\Controller\Extension\FlexextCharLimitLiteOc4\Module\CharLimitLite'
    );
}
