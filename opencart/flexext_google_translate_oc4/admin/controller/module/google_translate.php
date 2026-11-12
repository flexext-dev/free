<?php
namespace Opencart\Admin\Controller\Extension\FlexextGoogleTranslateOc4\Module;

if (!class_exists('\Opencart\Admin\Controller\Extension\FlexextGoogleTranslateOc4\Module\GoogleTranslate', false)) {
class GoogleTranslate extends \Opencart\System\Engine\Controller {
    public const VERSION = '1.0.1';

    public static function getLanguageCatalog(): array {
        return [
            'en'    => ['code' => 'en',    'iso' => 'EN', 'flag' => 'gb', 'name_en' => 'English',              'name_native' => 'English',            'top_buyer' => true,  'region' => 'Global / US & UK'],
            'de'    => ['code' => 'de',    'iso' => 'DE', 'flag' => 'de', 'name_en' => 'German',               'name_native' => 'Deutsch',            'top_buyer' => true,  'region' => 'Europe (DACH)'],
            'pl'    => ['code' => 'pl',    'iso' => 'PL', 'flag' => 'pl', 'name_en' => 'Polish',               'name_native' => 'Polski',             'top_buyer' => true,  'region' => 'Europe'],
            'es'    => ['code' => 'es',    'iso' => 'ES', 'flag' => 'es', 'name_en' => 'Spanish',              'name_native' => 'Español',            'top_buyer' => true,  'region' => 'Europe & LatAm'],
            'fr'    => ['code' => 'fr',    'iso' => 'FR', 'flag' => 'fr', 'name_en' => 'French',               'name_native' => 'Français',           'top_buyer' => true,  'region' => 'Europe & Canada'],
            'it'    => ['code' => 'it',    'iso' => 'IT', 'flag' => 'it', 'name_en' => 'Italian',              'name_native' => 'Italiano',           'top_buyer' => true,  'region' => 'Europe'],
            'nl'    => ['code' => 'nl',    'iso' => 'NL', 'flag' => 'nl', 'name_en' => 'Dutch',                'name_native' => 'Nederlands',         'top_buyer' => true,  'region' => 'Europe (Benelux)'],
            'pt'    => ['code' => 'pt',    'iso' => 'PT', 'flag' => 'br', 'name_en' => 'Portuguese',           'name_native' => 'Português',          'top_buyer' => true,  'region' => 'Brazil & Portugal'],
            'cs'    => ['code' => 'cs',    'iso' => 'CS', 'flag' => 'cz', 'name_en' => 'Czech',                'name_native' => 'Čeština',            'top_buyer' => true,  'region' => 'Europe'],
            'ro'    => ['code' => 'ro',    'iso' => 'RO', 'flag' => 'ro', 'name_en' => 'Romanian',             'name_native' => 'Română',             'top_buyer' => true,  'region' => 'Europe'],
            'tr'    => ['code' => 'tr',    'iso' => 'TR', 'flag' => 'tr', 'name_en' => 'Turkish',              'name_native' => 'Türkçe',             'top_buyer' => true,  'region' => 'Europe & MENA'],
            'uk'    => ['code' => 'uk',    'iso' => 'UK', 'flag' => 'ua', 'name_en' => 'Ukrainian',            'name_native' => 'Українська',         'top_buyer' => true,  'region' => 'Europe'],
            'sv'    => ['code' => 'sv',    'iso' => 'SV', 'flag' => 'se', 'name_en' => 'Swedish',              'name_native' => 'Svenska',            'top_buyer' => true,  'region' => 'Nordics'],
            'el'    => ['code' => 'el',    'iso' => 'EL', 'flag' => 'gr', 'name_en' => 'Greek',                'name_native' => 'Ελληνικά',           'top_buyer' => true,  'region' => 'Europe'],
            'hu'    => ['code' => 'hu',    'iso' => 'HU', 'flag' => 'hu', 'name_en' => 'Hungarian',            'name_native' => 'Magyar',             'top_buyer' => true,  'region' => 'Europe'],
            'bg'    => ['code' => 'bg',    'iso' => 'BG', 'flag' => 'bg', 'name_en' => 'Bulgarian',            'name_native' => 'Български',          'top_buyer' => true,  'region' => 'Europe'],
            'ja'    => ['code' => 'ja',    'iso' => 'JA', 'flag' => 'jp', 'name_en' => 'Japanese',             'name_native' => '日本語',              'top_buyer' => true,  'region' => 'Asia-Pacific'],
            'ko'    => ['code' => 'ko',    'iso' => 'KO', 'flag' => 'kr', 'name_en' => 'Korean',               'name_native' => '한국어',              'top_buyer' => true,  'region' => 'Asia-Pacific'],
            'ar'    => ['code' => 'ar',    'iso' => 'AR', 'flag' => 'ae', 'name_en' => 'Arabic',               'name_native' => 'العربية',            'top_buyer' => true,  'region' => 'MENA / GCC'],
            'hi'    => ['code' => 'hi',    'iso' => 'HI', 'flag' => 'in', 'name_en' => 'Hindi',                'name_native' => 'हिन्दी',              'top_buyer' => true,  'region' => 'South Asia'],
            'vi'    => ['code' => 'vi',    'iso' => 'VI', 'flag' => 'vn', 'name_en' => 'Vietnamese',           'name_native' => 'Tiếng Việt',         'top_buyer' => true,  'region' => 'Southeast Asia'],
            'id'    => ['code' => 'id',    'iso' => 'ID', 'flag' => 'id', 'name_en' => 'Indonesian',           'name_native' => 'Bahasa Indonesia',   'top_buyer' => true,  'region' => 'Southeast Asia'],
            'da'    => ['code' => 'da',    'iso' => 'DA', 'flag' => 'dk', 'name_en' => 'Danish',               'name_native' => 'Dansk',              'top_buyer' => false, 'region' => 'Nordics'],
            'fi'    => ['code' => 'fi',    'iso' => 'FI', 'flag' => 'fi', 'name_en' => 'Finnish',              'name_native' => 'Suomi',              'top_buyer' => false, 'region' => 'Nordics'],
            'no'    => ['code' => 'no',    'iso' => 'NO', 'flag' => 'no', 'name_en' => 'Norwegian',            'name_native' => 'Norsk',              'top_buyer' => false, 'region' => 'Nordics'],
            'sk'    => ['code' => 'sk',    'iso' => 'SK', 'flag' => 'sk', 'name_en' => 'Slovak',               'name_native' => 'Slovenčina',         'top_buyer' => false, 'region' => 'Europe'],
            'hr'    => ['code' => 'hr',    'iso' => 'HR', 'flag' => 'hr', 'name_en' => 'Croatian',             'name_native' => 'Hrvatski',           'top_buyer' => false, 'region' => 'Europe'],
            'lt'    => ['code' => 'lt',    'iso' => 'LT', 'flag' => 'lt', 'name_en' => 'Lithuanian',           'name_native' => 'Lietuvių',           'top_buyer' => false, 'region' => 'Baltics'],
            'lv'    => ['code' => 'lv',    'iso' => 'LV', 'flag' => 'lv', 'name_en' => 'Latvian',              'name_native' => 'Latviešu',           'top_buyer' => false, 'region' => 'Baltics'],
            'et'    => ['code' => 'et',    'iso' => 'ET', 'flag' => 'ee', 'name_en' => 'Estonian',             'name_native' => 'Eesti',              'top_buyer' => false, 'region' => 'Baltics'],
            'sl'    => ['code' => 'sl',    'iso' => 'SL', 'flag' => 'si', 'name_en' => 'Slovenian',            'name_native' => 'Slovenščina',        'top_buyer' => false, 'region' => 'Europe'],
            'sr'    => ['code' => 'sr',    'iso' => 'SR', 'flag' => 'rs', 'name_en' => 'Serbian',              'name_native' => 'Српски',             'top_buyer' => false, 'region' => 'Europe'],
            'he'    => ['code' => 'he',    'iso' => 'HE', 'flag' => 'il', 'name_en' => 'Hebrew',               'name_native' => 'עברית',              'top_buyer' => false, 'region' => 'Middle East'],
            'th'    => ['code' => 'th',    'iso' => 'TH', 'flag' => 'th', 'name_en' => 'Thai',                 'name_native' => 'ไทย',                'top_buyer' => false, 'region' => 'Southeast Asia'],
            'ms'    => ['code' => 'ms',    'iso' => 'MS', 'flag' => 'my', 'name_en' => 'Malay',                'name_native' => 'Bahasa Melayu',      'top_buyer' => false, 'region' => 'Southeast Asia'],
            'zh-CN' => ['code' => 'zh-CN', 'iso' => 'ZH', 'flag' => 'cn', 'name_en' => 'Chinese (Simplified)', 'name_native' => '简体中文',            'top_buyer' => false, 'region' => 'Asia-Pacific'],
        ];
    }

    public static function getDefaultTopBuyerCodes(): array {
        $codes = [];
        foreach (self::getLanguageCatalog() as $code => $info) {
            if (!empty($info['top_buyer'])) {
                $codes[] = $code;
            }
        }
        return $codes;
    }

    public function install(): void {
        $this->load->model('setting/setting');

        $defaultKey = 'OPEN-SOURCE-GPL';

        $defaults = [
            'module_flexext_google_translate_oc4_status'          => 1,
            'module_flexext_google_translate_oc4_license_key'     => $defaultKey,
            'module_flexext_google_translate_oc4_source_lang'     => 'en',
            'module_flexext_google_translate_oc4_position'        => 'top_bar_right',
            'module_flexext_google_translate_oc4_custom_selector' => '#top .header-lang-currency',
            'module_flexext_google_translate_oc4_custom_insert'   => 'prepend',
            'module_flexext_google_translate_oc4_style_variant'   => 'dropdown_flag_code',
            'module_flexext_google_translate_oc4_button_theme'    => 'auto_adaptive',
            'module_flexext_google_translate_oc4_show_search'     => 1,
            'module_flexext_google_translate_oc4_show_native'     => 1,
            'module_flexext_google_translate_oc4_auto_restore'    => 1,
            'module_flexext_google_translate_oc4_hide_google_bar' => 1,
            'module_flexext_google_translate_oc4_languages'       => self::getDefaultTopBuyerCodes(),
        ];

        $this->model_setting_setting->editSetting('module_flexext_google_translate_oc4', $defaults);
        $this->registerEvents();
    }

    public function uninstall(): void {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('flexext_google_translate_oc4_view');
    }

    public function registerEvents(): void {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('flexext_google_translate_oc4_view');
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_google_translate_oc4_view',
            'description' => 'FlexExt Zero-Lag Google Translate Bar Storefront Injection',
            'trigger'     => 'catalog/view/common/header/after',
            'action'      => 'extension/flexext_google_translate_oc4/module/google_translate.eventViewHeaderAfter',
            'status'      => 1,
            'sort_order'  => 5
        ]);

        // Ensure Common FlexExt Menu & Dashboard Events exist
        $this->model_setting_event->deleteEventByCode('flexext_google_translate_menu');
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_google_translate_menu',
            'description' => 'FlexExt Google Translate Admin Sidebar Menu',
            'trigger'     => 'admin/view/common/column_left/before',
            'action'      => 'extension/flexext_google_translate_oc4/module/google_translate.eventColumnLeft',
            'status'      => 1,
            'sort_order'  => 2
        ]);

        $this->model_setting_event->deleteEventByCode('flexext_google_translate_dashboard');
        $this->model_setting_event->addEvent([
            'code'        => 'flexext_google_translate_dashboard',
            'description' => 'FlexExt Google Translate Admin Dashboard Hub',
            'trigger'     => 'admin/view/common/dashboard/after',
            'action'      => 'extension/flexext_google_translate_oc4/module/google_translate.eventDashboard',
            'status'      => 1,
            'sort_order'  => 2
        ]);
    }

    public function eventColumnLeft(string &$route, array &$data, mixed &$code = null): void {
        $lib = defined('DIR_SYSTEM') ? DIR_SYSTEM . 'library/flexext_menu.php' : '';
        if ($lib && is_file($lib)) {
            require_once($lib);
            if (class_exists('\Opencart\System\Library\FlexExtMenu')) {
                \Opencart\System\Library\FlexExtMenu::injectColumnLeft($this->registry, $data);
            }
        }
    }

    public function eventDashboard(string &$route, array &$data, string &$output): void {
        $lib = defined('DIR_SYSTEM') ? DIR_SYSTEM . 'library/flexext_menu.php' : '';
        if ($lib && is_file($lib)) {
            require_once($lib);
            if (class_exists('\Opencart\System\Library\FlexExtMenu')) {
                \Opencart\System\Library\FlexExtMenu::injectDashboard($this->registry, $output);
            }
        }
    }

    public function index(): void {
        $this->load->language('extension/flexext_google_translate_oc4/module/google_translate');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        $this->registerEvents();

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/flexext_google_translate_oc4/module/google_translate', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link('extension/flexext_google_translate_oc4/module/google_translate.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $domain = preg_replace('/^www\./', '', explode(':', $host)[0]);
        $isDemoHost = in_array($domain, ['oc4-demo.flexext.dev', 'oc3-demo.flexext.dev', 'demo.flexext.dev', 'localhost', '127.0.0.1'], true);

        $data['license_key'] = 'OPEN-SOURCE-GPL';
        $data['license_info'] = ['valid' => true, 'status' => 'valid', 'tier' => 'Open-Source Community Edition (GPLv2+)', 'domain' => $domain, 'message' => 'Free Open-Source Community Edition active.'];
        $data['is_demo_host'] = $isDemoHost;
        $data['version'] = self::VERSION;

        $data['status']          = (int)($this->config->get('module_flexext_google_translate_oc4_status') ?? 1);
        $data['source_lang']     = (string)($this->config->get('module_flexext_google_translate_oc4_source_lang') ?: 'en');
        $data['position']        = (string)($this->config->get('module_flexext_google_translate_oc4_position') ?: 'top_bar_right');
        $data['custom_selector'] = (string)($this->config->get('module_flexext_google_translate_oc4_custom_selector') ?: '#top .header-lang-currency');
        $data['custom_insert']   = (string)($this->config->get('module_flexext_google_translate_oc4_custom_insert') ?: 'prepend');
        $data['style_variant']   = (string)($this->config->get('module_flexext_google_translate_oc4_style_variant') ?: 'dropdown_flag_code');
        $data['button_theme']    = (string)($this->config->get('module_flexext_google_translate_oc4_button_theme') ?: 'auto_adaptive');
        $data['show_search']     = (int)($this->config->get('module_flexext_google_translate_oc4_show_search') ?? 1);
        $data['show_native']     = (int)($this->config->get('module_flexext_google_translate_oc4_show_native') ?? 1);
        $data['auto_restore']    = (int)($this->config->get('module_flexext_google_translate_oc4_auto_restore') ?? 1);
        $data['hide_google_bar'] = (int)($this->config->get('module_flexext_google_translate_oc4_hide_google_bar') ?? 1);

        $savedLangs = $this->config->get('module_flexext_google_translate_oc4_languages');
        if (!is_array($savedLangs) || empty($savedLangs)) {
            $savedLangs = self::getDefaultTopBuyerCodes();
        }
        $data['selected_languages'] = $savedLangs;
        $data['language_catalog']   = self::getLanguageCatalog();
        $data['top_buyer_codes']    = self::getDefaultTopBuyerCodes();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/flexext_google_translate_oc4/module/google_translate', $data));
    }

    public function save(): void {
        $this->load->language('extension/flexext_google_translate_oc4/module/google_translate');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/flexext_google_translate_oc4/module/google_translate')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $postData = $this->request->post;
                if (empty($postData['module_flexext_google_translate_oc4_languages']) || !is_array($postData['module_flexext_google_translate_oc4_languages'])) {
                    $postData['module_flexext_google_translate_oc4_languages'] = ['en'];
                }
                if (!in_array('en', $postData['module_flexext_google_translate_oc4_languages'], true)) {
                    array_unshift($postData['module_flexext_google_translate_oc4_languages'], 'en');
                }
                $this->model_setting_setting->editSetting('module_flexext_google_translate_oc4', $postData);
                $this->registerEvents();

                // Purge page cache if present so storefront reflects changes immediately
                $pageCacheDir = defined('DIR_CACHE') ? DIR_CACHE . 'page/' : '';
                if ($pageCacheDir && is_dir($pageCacheDir)) {
                    foreach ((array)glob($pageCacheDir . '*.html') as $f) {
                        if (is_file($f)) @unlink($f);
        }

                $json['success'] = $this->language->get('text_success');
                $json['reload'] = true;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
}
