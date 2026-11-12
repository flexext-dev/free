<?php
namespace Opencart\Catalog\Controller\Extension\FlexextGoogleTranslateOc4\Module;

if (!class_exists('\Opencart\Catalog\Controller\Extension\FlexextGoogleTranslateOc4\Module\GoogleTranslate', false)) {
class GoogleTranslate extends \Opencart\System\Engine\Controller {
    public const VERSION = '1.0.1';

    public static function getLanguageCatalog(): array {
        return [
            'en'    => ['code' => 'en',    'iso' => 'EN', 'flag' => 'gb', 'name_en' => 'English',              'name_native' => 'English',            'top_buyer' => true],
            'de'    => ['code' => 'de',    'iso' => 'DE', 'flag' => 'de', 'name_en' => 'German',               'name_native' => 'Deutsch',            'top_buyer' => true],
            'pl'    => ['code' => 'pl',    'iso' => 'PL', 'flag' => 'pl', 'name_en' => 'Polish',               'name_native' => 'Polski',             'top_buyer' => true],
            'es'    => ['code' => 'es',    'iso' => 'ES', 'flag' => 'es', 'name_en' => 'Spanish',              'name_native' => 'Español',            'top_buyer' => true],
            'fr'    => ['code' => 'fr',    'iso' => 'FR', 'flag' => 'fr', 'name_en' => 'French',               'name_native' => 'Français',           'top_buyer' => true],
            'it'    => ['code' => 'it',    'iso' => 'IT', 'flag' => 'it', 'name_en' => 'Italian',              'name_native' => 'Italiano',           'top_buyer' => true],
            'nl'    => ['code' => 'nl',    'iso' => 'NL', 'flag' => 'nl', 'name_en' => 'Dutch',                'name_native' => 'Nederlands',         'top_buyer' => true],
            'pt'    => ['code' => 'pt',    'iso' => 'PT', 'flag' => 'br', 'name_en' => 'Portuguese',           'name_native' => 'Português',          'top_buyer' => true],
            'cs'    => ['code' => 'cs',    'iso' => 'CS', 'flag' => 'cz', 'name_en' => 'Czech',                'name_native' => 'Čeština',            'top_buyer' => true],
            'ro'    => ['code' => 'ro',    'iso' => 'RO', 'flag' => 'ro', 'name_en' => 'Romanian',             'name_native' => 'Română',             'top_buyer' => true],
            'tr'    => ['code' => 'tr',    'iso' => 'TR', 'flag' => 'tr', 'name_en' => 'Turkish',              'name_native' => 'Türkçe',             'top_buyer' => true],
            'uk'    => ['code' => 'uk',    'iso' => 'UK', 'flag' => 'ua', 'name_en' => 'Ukrainian',            'name_native' => 'Українська',         'top_buyer' => true],
            'sv'    => ['code' => 'sv',    'iso' => 'SV', 'flag' => 'se', 'name_en' => 'Swedish',              'name_native' => 'Svenska',            'top_buyer' => true],
            'el'    => ['code' => 'el',    'iso' => 'EL', 'flag' => 'gr', 'name_en' => 'Greek',                'name_native' => 'Ελληνικά',           'top_buyer' => true],
            'hu'    => ['code' => 'hu',    'iso' => 'HU', 'flag' => 'hu', 'name_en' => 'Hungarian',            'name_native' => 'Magyar',             'top_buyer' => true],
            'bg'    => ['code' => 'bg',    'iso' => 'BG', 'flag' => 'bg', 'name_en' => 'Bulgarian',            'name_native' => 'Български',          'top_buyer' => true],
            'ja'    => ['code' => 'ja',    'iso' => 'JA', 'flag' => 'jp', 'name_en' => 'Japanese',             'name_native' => '日本語',              'top_buyer' => true],
            'ko'    => ['code' => 'ko',    'iso' => 'KO', 'flag' => 'kr', 'name_en' => 'Korean',               'name_native' => '한국어',              'top_buyer' => true],
            'ar'    => ['code' => 'ar',    'iso' => 'AR', 'flag' => 'ae', 'name_en' => 'Arabic',               'name_native' => 'العربية',            'top_buyer' => true],
            'hi'    => ['code' => 'hi',    'iso' => 'HI', 'flag' => 'in', 'name_en' => 'Hindi',                'name_native' => 'हिन्दी',              'top_buyer' => true],
            'vi'    => ['code' => 'vi',    'iso' => 'VI', 'flag' => 'vn', 'name_en' => 'Vietnamese',           'name_native' => 'Tiếng Việt',         'top_buyer' => true],
            'id'    => ['code' => 'id',    'iso' => 'ID', 'flag' => 'id', 'name_en' => 'Indonesian',           'name_native' => 'Bahasa Indonesia',   'top_buyer' => true],
            'da'    => ['code' => 'da',    'iso' => 'DA', 'flag' => 'dk', 'name_en' => 'Danish',               'name_native' => 'Dansk',              'top_buyer' => false],
            'fi'    => ['code' => 'fi',    'iso' => 'FI', 'flag' => 'fi', 'name_en' => 'Finnish',              'name_native' => 'Suomi',              'top_buyer' => false],
            'no'    => ['code' => 'no',    'iso' => 'NO', 'flag' => 'no', 'name_en' => 'Norwegian',            'name_native' => 'Norsk',              'top_buyer' => false],
            'sk'    => ['code' => 'sk',    'iso' => 'SK', 'flag' => 'sk', 'name_en' => 'Slovak',               'name_native' => 'Slovenčina',         'top_buyer' => false],
            'hr'    => ['code' => 'hr',    'iso' => 'HR', 'flag' => 'hr', 'name_en' => 'Croatian',             'name_native' => 'Hrvatski',           'top_buyer' => false],
            'lt'    => ['code' => 'lt',    'iso' => 'LT', 'flag' => 'lt', 'name_en' => 'Lithuanian',           'name_native' => 'Lietuvių',           'top_buyer' => false],
            'lv'    => ['code' => 'lv',    'iso' => 'LV', 'flag' => 'lv', 'name_en' => 'Latvian',              'name_native' => 'Latviešu',           'top_buyer' => false],
            'et'    => ['code' => 'et',    'iso' => 'ET', 'flag' => 'ee', 'name_en' => 'Estonian',             'name_native' => 'Eesti',              'top_buyer' => false],
            'sl'    => ['code' => 'sl',    'iso' => 'SL', 'flag' => 'si', 'name_en' => 'Slovenian',            'name_native' => 'Slovenščina',        'top_buyer' => false],
            'sr'    => ['code' => 'sr',    'iso' => 'SR', 'flag' => 'rs', 'name_en' => 'Serbian',              'name_native' => 'Српски',             'top_buyer' => false],
            'he'    => ['code' => 'he',    'iso' => 'HE', 'flag' => 'il', 'name_en' => 'Hebrew',               'name_native' => 'עברית',              'top_buyer' => false],
            'th'    => ['code' => 'th',    'iso' => 'TH', 'flag' => 'th', 'name_en' => 'Thai',                 'name_native' => 'ไทย',                'top_buyer' => false],
            'ms'    => ['code' => 'ms',    'iso' => 'MS', 'flag' => 'my', 'name_en' => 'Malay',                'name_native' => 'Bahasa Melayu',      'top_buyer' => false],
            'zh-CN' => ['code' => 'zh-CN', 'iso' => 'ZH', 'flag' => 'cn', 'name_en' => 'Chinese (Simplified)', 'name_native' => '简体中文',            'top_buyer' => false],
        ];
    }

    public static function getFlagSvgDataUri(string $flagCode): string {
        $svgs = [
            'gb' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30"><rect width="60" height="30" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#C8102E" stroke-width="2.5"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></svg>',
            'de' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3"><rect width="5" height="3" fill="#000"/><rect width="5" height="2" y="1" fill="#D00"/><rect width="5" height="1" y="2" fill="#FFCE00"/></svg>',
            'pl' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 5"><rect width="8" height="5" fill="#dc143c"/><rect width="8" height="2.5" fill="#fff"/></svg>',
            'es' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#AA151B"/><rect width="3" height="1" y="0.5" fill="#F1BF00"/></svg>',
            'fr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#ED2939"/><rect width="2" height="2" fill="#fff"/><rect width="1" height="2" fill="#002395"/></svg>',
            'it' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#CE2B37"/><rect width="2" height="2" fill="#fff"/><rect width="1" height="2" fill="#009246"/></svg>',
            'nl' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6"><rect width="9" height="6" fill="#21468B"/><rect width="9" height="4" fill="#fff"/><rect width="9" height="2" fill="#AE1C28"/></svg>',
            'br' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 7"><rect width="10" height="7" fill="#009c3b"/><polygon points="5,0.8 9.2,3.5 5,6.2 0.8,3.5" fill="#ffdf00"/><circle cx="5" cy="3.5" r="1.4" fill="#002776"/></svg>',
            'cz' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 4"><rect width="6" height="4" fill="#d7141a"/><rect width="6" height="2" fill="#fff"/><polygon points="0,0 3,2 0,4" fill="#11457e"/></svg>',
            'ro' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#CE1126"/><rect width="2" height="2" fill="#FCD116"/><rect width="1" height="2" fill="#002B7F"/></svg>',
            'tr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 20"><rect width="30" height="20" fill="#E30A17"/><circle cx="11" cy="10" r="5" fill="#fff"/><circle cx="12.5" cy="10" r="4" fill="#E30A17"/><polygon points="16,10 19.5,11.2 17.3,8.2 17.3,11.8 19.5,8.8" fill="#fff"/></svg>',
            'ua' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#FFD700"/><rect width="3" height="1" fill="#0057B7"/></svg>',
            'se' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 10"><rect width="16" height="10" fill="#006AA7"/><rect width="2" height="10" x="5" fill="#FECC02"/><rect width="16" height="2" y="4" fill="#FECC02"/></svg>',
            'gr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 18"><rect width="27" height="18" fill="#0D5EAF"/><path d="M0,3 h27 M0,7 h27 M0,11 h27 M0,15 h27" stroke="#fff" stroke-width="2"/><rect width="10" height="10" fill="#0D5EAF"/><path d="M5,0 v10 M0,5 h10" stroke="#fff" stroke-width="2"/></svg>',
            'hu' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 3"><rect width="6" height="3" fill="#477050"/><rect width="6" height="2" fill="#fff"/><rect width="6" height="1" fill="#CE2939"/></svg>',
            'bg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3"><rect width="5" height="3" fill="#D62612"/><rect width="5" height="2" fill="#00966E"/><rect width="5" height="1" fill="#fff"/></svg>',
            'jp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#fff"/><circle cx="1.5" cy="1" r="0.6" fill="#bc002d"/></svg>',
            'kr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#fff"/><circle cx="1.5" cy="1" r="0.5" fill="#cd2e3a"/><path d="M1,1 a0.5,0.5 0 0,0 1,0 a0.25,0.25 0 0,0 -0.5,0 a0.25,0.25 0 0,1 -0.5,0" fill="#0047a0"/></svg>',
            'ae' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 3"><rect width="6" height="3" fill="#000"/><rect width="6" height="2" fill="#fff"/><rect width="6" height="1" fill="#00732f"/><rect width="1.5" height="3" fill="#f00"/></svg>',
            'in' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6"><rect width="9" height="6" fill="#138808"/><rect width="9" height="4" fill="#fff"/><rect width="9" height="2" fill="#FF9933"/><circle cx="4.5" cy="3" r="0.8" fill="#000080"/></svg>',
            'vn' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 20"><rect width="30" height="20" fill="#da251d"/><polygon points="15,4 11.5,14.8 20.7,8.1 9.3,8.1 18.5,14.8" fill="#ffff00"/></svg>',
            'id' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#fff"/><rect width="3" height="1" fill="#ce1126"/></svg>',
            'dk' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 28"><rect width="37" height="28" fill="#C8102E"/><rect width="4" height="28" x="12" fill="#fff"/><rect width="37" height="4" y="12" fill="#fff"/></svg>',
            'fi' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 11"><rect width="18" height="11" fill="#fff"/><rect width="3" height="11" x="5" fill="#003580"/><rect width="18" height="3" y="4" fill="#003580"/></svg>',
            'no' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 16"><rect width="22" height="16" fill="#BA0C2F"/><rect width="4" height="16" x="6" fill="#fff"/><rect width="22" height="4" y="6" fill="#fff"/><rect width="2" height="16" x="7" fill="#00205B"/><rect width="22" height="2" y="7" fill="#00205B"/></svg>',
            'sk' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 4"><rect width="6" height="4" fill="#EE1C25"/><rect width="6" height="2.66" fill="#0B4EA2"/><rect width="6" height="1.33" fill="#fff"/></svg>',
            'hr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 3"><rect width="6" height="3" fill="#171796"/><rect width="6" height="2" fill="#fff"/><rect width="6" height="1" fill="#ff0000"/></svg>',
            'lt' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3"><rect width="5" height="3" fill="#C1272D"/><rect width="5" height="2" fill="#006A44"/><rect width="5" height="1" fill="#FDB913"/></svg>',
            'lv' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 5"><rect width="10" height="5" fill="#9E3039"/><rect width="10" height="1" y="2" fill="#fff"/></svg>',
            'ee' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33 21"><rect width="33" height="21" fill="#fff"/><rect width="33" height="14" fill="#000"/><rect width="33" height="7" fill="#0072CE"/></svg>',
            'si' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 3"><rect width="6" height="3" fill="#ed1c24"/><rect width="6" height="2" fill="#005da4"/><rect width="6" height="1" fill="#fff"/></svg>',
            'rs' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 4"><rect width="6" height="4" fill="#fff"/><rect width="6" height="2.66" fill="#0C4076"/><rect width="6" height="1.33" fill="#C6363C"/></svg>',
            'il' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 16"><rect width="22" height="16" fill="#fff"/><rect width="22" height="2.5" y="1.5" fill="#0038b8"/><rect width="22" height="2.5" y="12" fill="#0038b8"/><polygon points="11,5 8.5,9.5 13.5,9.5" fill="none" stroke="#0038b8" stroke-width="0.9"/><polygon points="11,11 8.5,6.5 13.5,6.5" fill="none" stroke="#0038b8" stroke-width="0.9"/></svg>',
            'th' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6"><rect width="9" height="6" fill="#A51931"/><rect width="9" height="4" y="1" fill="#F4F5F8"/><rect width="9" height="2" y="2" fill="#2D2A4A"/></svg>',
            'my' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 14"><rect width="28" height="14" fill="#cc0000"/><path d="M0,2 h28 M0,6 h28 M0,10 h28 M0,13 h28" stroke="#fff" stroke-width="1.5"/><rect width="14" height="8" fill="#010066"/><circle cx="6.5" cy="4" r="2.2" fill="#ffcc00"/></svg>',
            'cn' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 20"><rect width="30" height="20" fill="#de2910"/><polygon points="5,2 3.2,7.5 8,4.2 2,4.2 6.8,7.5" fill="#ffde00"/></svg>',
        ];
        $svg = $svgs[$flagCode] ?? $svgs['gb'];
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    public function eventViewHeaderAfter(string &$route, array &$data, string &$output): void {
        $status = $this->config->get('module_flexext_google_translate_oc4_status');
        if ($status === null) {
            $status = 1;
        }
        if (!(int)$status) {
            return;
        }

        // Open-Source Community Edition (GPLv2+)

        $catalog = self::getLanguageCatalog();
        $enabledCodes = $this->config->get('module_flexext_google_translate_oc4_languages');
        if (!is_array($enabledCodes) || empty($enabledCodes)) {
            $enabledCodes = [];
            foreach ($catalog as $c => $info) {
                if (!empty($info['top_buyer'])) {
                    $enabledCodes[] = $c;
                }
            }
        }

        $sourceLang = (string)($this->config->get('module_flexext_google_translate_oc4_source_lang') ?: 'en');
        if (!in_array($sourceLang, $enabledCodes, true)) {
            array_unshift($enabledCodes, $sourceLang);
        }

        $languages = [];
        foreach ($enabledCodes as $code) {
            if (isset($catalog[$code])) {
                $item = $catalog[$code];
                $item['flag_svg'] = self::getFlagSvgDataUri($item['flag']);
                $languages[] = $item;
            }
        }

        if (empty($languages)) {
            return;
        }

        $sourceInfo = $catalog[$sourceLang] ?? $catalog['en'];
        $sourceInfo['flag_svg'] = self::getFlagSvgDataUri($sourceInfo['flag']);

        $position = (string)($this->config->get('module_flexext_google_translate_oc4_position') ?: 'top_bar_right');
        $viewData = [
            'source_lang'     => $sourceLang,
            'source_info'     => $sourceInfo,
            'position'        => $position,
            'custom_selector' => (string)($this->config->get('module_flexext_google_translate_oc4_custom_selector') ?: '#top .header-lang-currency'),
            'custom_insert'   => (string)($this->config->get('module_flexext_google_translate_oc4_custom_insert') ?: 'prepend'),
            'style_variant'   => (string)($this->config->get('module_flexext_google_translate_oc4_style_variant') ?: 'dropdown_flag_code'),
            'button_theme'    => (string)($this->config->get('module_flexext_google_translate_oc4_button_theme') ?: 'auto_adaptive'),
            'show_search'     => (int)($this->config->get('module_flexext_google_translate_oc4_show_search') ?? 1),
            'show_native'     => (int)($this->config->get('module_flexext_google_translate_oc4_show_native') ?? 1),
            'auto_restore'    => (int)($this->config->get('module_flexext_google_translate_oc4_auto_restore') ?? 1),
            'hide_google_bar' => (int)($this->config->get('module_flexext_google_translate_oc4_hide_google_bar') ?? 1),
            'languages'       => $languages,
        ];

        $widgetHtml = $this->load->view('extension/flexext_google_translate_oc4/module/google_translate', $viewData);

        if ($position === 'top_bar_right') {
            // Try direct server-side injection into #top ul.header-lang-currency or #top .float-end ul.list-inline for 0 CLS
            if (preg_match('/(<ul[^>]*class="[^"]*header-lang-currency[^"]*"[^>]*>)/i', $output, $m, PREG_OFFSET_CAPTURE)) {
                $insertPos = $m[0][1] + strlen($m[0][0]);
                $output = substr_replace($output, "\n<li class=\"list-inline-item flx-gt-top-li\">" . $widgetHtml . "</li>\n", $insertPos, 0);
                return;
            }
            if (preg_match('/(<div[^>]*class="[^"]*float-end[^"]*"[^>]*>\s*<ul[^>]*class="[^"]*list-inline[^"]*"[^>]*>)/i', $output, $m, PREG_OFFSET_CAPTURE)) {
                $insertPos = $m[0][1] + strlen($m[0][0]);
                $output = substr_replace($output, "\n<li class=\"list-inline-item flx-gt-top-li\">" . $widgetHtml . "</li>\n", $insertPos, 0);
                return;
            }
        } elseif ($position === 'top_bar_left') {
            if (preg_match('/(<ul[^>]*class="[^"]*header-social[^"]*"[^>]*>)/i', $output, $m, PREG_OFFSET_CAPTURE)) {
                $insertPos = $m[0][1] + strlen($m[0][0]);
                $output = substr_replace($output, "\n<li class=\"list-inline-item flx-gt-top-li\">" . $widgetHtml . "</li>\n", $insertPos, 0);
                return;
            }
        }

        // Fallback: append widgetHtml to header output and let lightweight inline script place it in exact target
        $output .= "\n" . $widgetHtml;
    }
}
}
