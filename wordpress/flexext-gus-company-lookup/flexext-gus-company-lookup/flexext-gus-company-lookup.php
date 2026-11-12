<?php
/**
 * Plugin Name: FlexExt Company Lookup for GUS
 * Plugin URI: https://flexext.dev/en-gb/product/auto-gus-mf-vies-checkout-filler-woocommerce
 * Description: Automated Polish NIP lookup, Modulo 11 checksum validation, and company address autofill for checkout forms via the official Ministry of Finance registry (HPOS Ready).
 * Version: 1.0.4
 * Author: FlexExt Dev
 * Author URI: https://flexext.dev/
 * Text Domain: flexext-gus-company-lookup
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FLEXCOLO_VERSION', '1.0.4');
define('FLEXCOLO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FLEXCOLO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class: FlexExt Company Lookup for GUS
 */
final class FLEXCOLO_Company_Lookup {

    /**
     * Plugin version
     */
    const VERSION = '1.0.4';

    /**
     * Plugin admin page slug
     */
    const PAGE_SLUG = 'flexext-gus-company-lookup';

    /**
     * Official Ministry of Finance VAT taxpayer registry API (White List) base URL.
     * Documented in readme.txt under "External services".
     */
    const MF_API_BASE = 'https://wykazpodatnikowvat.mf.gov.pl/api/search/nip/';

    /**
     * Singleton instance
     *
     * @var FLEXCOLO_Company_Lookup|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return FLEXCOLO_Company_Lookup
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Declare compatibility with WooCommerce High-Performance Order Storage (HPOS) & Checkout Blocks
        add_action('before_woocommerce_init', [$this, 'declare_hpos_compatibility']);

        // Register REST API lookup endpoint
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // WooCommerce checkout field & order hooks
        add_filter('woocommerce_billing_fields', [$this, 'add_billing_nip_field'], 20, 1);
        add_filter('woocommerce_form_field', [$this, 'render_lookup_button_in_field'], 10, 4);
        add_action('woocommerce_after_checkout_billing_form', [$this, 'render_checkout_nip_field_blocks_fallback']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_checkout_scripts']);

        // Save NIP & lookup details in order metadata
        add_action('woocommerce_checkout_create_order', [$this, 'save_nip_to_order'], 20, 2);

        // Display NIP information in admin order view, thank-you page, and order emails
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'display_nip_in_admin_order'], 10, 1);
        add_action('woocommerce_order_details_after_customer_details', [$this, 'display_nip_in_order_received'], 10, 1);
        add_action('woocommerce_email_after_order_table', [$this, 'display_nip_in_email'], 10, 4);

        // Admin dashboard interface
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_init', [$this, 'handle_admin_settings_save']);
            add_action('admin_init', [$this, 'add_privacy_policy_content']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_action_links']);
        }
    }

    /**
     * Suggest privacy policy text for site owners (Settings > Privacy).
     *
     * @return void
     */
    public function add_privacy_policy_content() {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = '<h2>' . esc_html__('Company lookup by Polish NIP', 'flexext-gus-company-lookup') . '</h2>';
        $content .= '<p>' . esc_html__('When a customer enters a Polish NIP (tax identification number) at checkout and starts a company lookup, only that NIP and the current date are sent over HTTPS to the public VAT taxpayer registry API of the Polish Ministry of Finance (wykazpodatnikowvat.mf.gov.pl). No other customer data is sent. The returned public company data (name, registered address, VAT status, request identifier) is cached on this site for a limited time and, after an order is placed, stored in the order metadata together with the NIP.', 'flexext-gus-company-lookup') . '</p>';
        $content .= '<p>' . esc_html__('Privacy information of the service provider:', 'flexext-gus-company-lookup') . ' <a href="https://www.gov.pl/web/gov/polityka-prywatnosci" target="_blank" rel="noopener noreferrer">https://www.gov.pl/web/gov/polityka-prywatnosci</a></p>';

        wp_add_privacy_policy_content('FlexExt Company Lookup for GUS', wp_kses_post($content));
    }

    /**
     * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS)
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        }
    }

    /**
     * Check if current WordPress locale is Polish (PHP 7.4 compatible)
     *
     * @return bool
     */
    public static function is_pl(): bool {
        $locale = function_exists('get_user_locale') ? get_user_locale() : (function_exists('get_locale') ? get_locale() : 'en_US');
        return strpos(strtolower((string) $locale), 'pl') === 0;
    }

    /**
     * Bilingual helper (EN default, PL for Polish locale)
     *
     * @param string $en
     * @param string $pl
     * @return string
     */
    public static function txt(string $en, string $pl): string {
        return self::is_pl() ? $pl : $en;
    }

    /**
     * Get required capability for managing plugin settings
     *
     * @return string
     */
    private function get_admin_capability(): string {
        return class_exists('WooCommerce') ? 'manage_woocommerce' : 'manage_options';
    }

    /**
     * Retrieve plugin settings with defaults
     *
     * @return array
     */
    public function get_settings(): array {
        $defaults = [
            'enabled'            => 1,
            'enable_mf'          => 1,
            'nip_required'       => 0,
            'auto_lookup_digits' => 1,
            'button_label'       => self::txt('Fetch Company Data', 'Pobierz dane po NIP'),
            'hint_label'         => self::txt('Polish Ministry of Finance (MF) White List verification', 'Pobieranie nazwy i adresu firmy po NIP (PL)'),
            'cache_hours'        => 24,
        ];
        $stored = get_option('flexcolo_settings', []);
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    /**
     * Register submenu page under WooCommerce (or Settings if WooCommerce is inactive)
     */
    public function register_admin_menu() {
        $parent_slug = class_exists('WooCommerce') ? 'woocommerce' : 'options-general.php';
        $cap = $this->get_admin_capability();

        add_submenu_page(
            $parent_slug,
            self::txt('Company Lookup for GUS', 'Pobieranie Danych GUS / MF'),
            self::txt('Company Lookup GUS', 'Pobieranie GUS / NIP'),
            $cap,
            self::PAGE_SLUG,
            [$this, 'render_admin_page']
        );
    }

    /**
     * Enqueue admin styles and scripts strictly on plugin screens
     *
     * @param string $hook_suffix
     */
    public function enqueue_admin_assets($hook_suffix) {
        $is_plugin_page = strpos((string) $hook_suffix, self::PAGE_SLUG) !== false;
        $is_order_screen = in_array($hook_suffix, ['post.php', 'post-new.php', 'woocommerce_page_wc-orders'], true);

        if (!$is_plugin_page && !$is_order_screen) {
            return;
        }

        $css_url = FLEXCOLO_PLUGIN_URL . 'assets/admin-settings.css';
        wp_enqueue_style(
            'flexcolo-admin',
            $css_url,
            [],
            self::VERSION
        );

        if ($is_plugin_page) {
            $js_url = FLEXCOLO_PLUGIN_URL . 'assets/admin-settings.js';
            wp_enqueue_script(
                'flexcolo-admin',
                $js_url,
                [],
                self::VERSION,
                true
            );

            wp_localize_script('flexcolo-admin', 'flexcolo_admin_config', [
                'restUrl'           => esc_url_raw(rest_url('flexcolo/v1/lookup')),
                'nonce'             => wp_create_nonce('wp_rest'),
                'confirmClearCache' => self::txt('Are you sure you want to clear the NIP transients cache?', 'Czy na pewno chcesz wyczyścić pamięć podręczną zapytań NIP?'),
                'emptyNipAlert'     => self::txt('Please enter a 10-digit Polish NIP number!', 'Wprowadź 10-cyfrowy numer NIP!'),
                'testingLabel'      => self::txt('Testing...', 'Sprawdzanie...'),
                'queryingLabel'     => self::txt('Querying Ministry of Finance registry server...', 'Trwa odpytywanie rejestru Ministerstwa Finansów...'),
                'btnDefaultLabel'   => '🔍 ' . self::txt('Run Diagnostic Query', 'Uruchom Test API'),
            ]);
        }
    }

    /**
     * Add quick links on the Plugins list page
     *
     * @param array $links
     * @return array
     */
    public function add_plugin_action_links($links) {
        $parent = class_exists('WooCommerce') ? 'admin.php' : 'options-general.php';
        $settings_url = admin_url($parent . '?page=' . self::PAGE_SLUG);
        $action_links = [
            '<a href="' . esc_url($settings_url) . '">' . esc_html(self::txt('Settings', 'Ustawienia')) . '</a>',
        ];
        return array_merge($action_links, $links);
    }

    /**
     * Handle settings form submission and cache clearing in admin
     */
    public function handle_admin_settings_save() {
        if (!isset($_POST['flexcolo_save_action'])) {
            return;
        }

        if (!current_user_can($this->get_admin_capability())) {
            return;
        }

        check_admin_referer('flexcolo_nonce_action', 'flexcolo_nonce');

        $action = sanitize_text_field(wp_unslash($_POST['flexcolo_save_action']));

        if ($action === 'save_settings') {
            $settings = [
                'enabled'            => !empty($_POST['flexcolo_enabled']) ? 1 : 0,
                'enable_mf'          => !empty($_POST['flexcolo_enable_mf']) ? 1 : 0,
                'nip_required'       => !empty($_POST['flexcolo_nip_required']) ? 1 : 0,
                'auto_lookup_digits' => !empty($_POST['flexcolo_auto_lookup_digits']) ? 1 : 0,
                'button_label'       => !empty($_POST['flexcolo_button_label']) ? sanitize_text_field(wp_unslash($_POST['flexcolo_button_label'])) : self::txt('Fetch Company Data', 'Pobierz dane po NIP'),
                'hint_label'         => !empty($_POST['flexcolo_hint_label']) ? sanitize_text_field(wp_unslash($_POST['flexcolo_hint_label'])) : self::txt('Polish Ministry of Finance (MF) White List verification', 'Pobieranie nazwy i adresu firmy po NIP (PL)'),
                'cache_hours'        => isset($_POST['flexcolo_cache_hours']) ? max(1, absint(wp_unslash($_POST['flexcolo_cache_hours']))) : 24,
            ];

            update_option('flexcolo_settings', $settings);
            add_settings_error('flexcolo_messages', 'flexcolo_msg', self::txt('Settings saved successfully!', 'Ustawienia zostały pomyślnie zapisane!'), 'success');
        } elseif ($action === 'clear_cache') {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_flexcolo_%' OR option_name LIKE '_transient_timeout_flexcolo_%'");
            add_settings_error('flexcolo_messages', 'flexcolo_msg', self::txt('NIP Transients cache has been completely cleared.', 'Pamięć podręczna NIP (Transients) została całkowicie wyczyszczona.'), 'success');
        }
    }

    /**
     * Render admin settings & diagnostics page
     */
    public function render_admin_page() {
        $settings = $this->get_settings();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
        $parent_file = class_exists('WooCommerce') ? 'admin.php' : 'options-general.php';

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $cached_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_flexcolo_%'");
        ?>
        <div class="wrap flexcolo-admin-wrap">
            
            <div class="flexcolo-admin-header">
                <div>
                    <h1>
                        <span>🛡️</span> <?php echo esc_html(self::txt('FlexExt Company Lookup for GUS', 'FlexExt Company Lookup for GUS')); ?>
                        <span class="flexcolo-version-badge">v<?php echo esc_html(self::VERSION); ?></span>
                    </h1>
                    <p class="flexcolo-admin-subtitle">
                        <?php echo esc_html(self::txt('Automated Polish NIP Modulo 11 validation and company address autofill in checkout (HPOS Ready).', 'Automatyczna walidacja Modulo 11 numeru NIP i pobieranie danych adresowych firmy w kasie (HPOS Ready).')); ?>
                    </p>
                </div>
            </div>

            <?php settings_errors('flexcolo_messages'); ?>

            <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url(admin_url($parent_file . '?page=' . self::PAGE_SLUG . '&tab=settings')); ?>" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    ⚙️ <?php echo esc_html(self::txt('Checkout & API Settings', 'Ustawienia Kasy i API')); ?>
                </a>
                <a href="<?php echo esc_url(admin_url($parent_file . '?page=' . self::PAGE_SLUG . '&tab=diagnostics')); ?>" class="nav-tab <?php echo $active_tab === 'diagnostics' ? 'nav-tab-active' : ''; ?>">
                    🧪 <?php echo esc_html(self::txt('Diagnostics & API Test', 'Diagnostyka i Test API')); ?>
                </a>
            </nav>

            <?php if ($active_tab === 'settings'): ?>
                <div class="flexcolo-admin-card">
                    <h2>
                        <?php echo esc_html(self::txt('Checkout Configuration', 'Konfiguracja Formularza Kasy')); ?>
                    </h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('flexcolo_nonce_action', 'flexcolo_nonce'); ?>
                        <input type="hidden" name="flexcolo_save_action" value="save_settings">

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Plugin Status', 'Status Wtyczki')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="flexcolo_enabled" value="1" <?php checked(1, $settings['enabled']); ?>>
                                        <strong><?php echo esc_html(self::txt('Enable automatic company data lookup and NIP field in checkout', 'Włącz automatyczne pobieranie danych firmy i pole NIP w kasie')); ?></strong>
                                    </label>
                                    <p class="description"><?php echo esc_html(self::txt('When enabled, the NIP field and lookup button are displayed in checkout and My Account billing forms.', 'Gdy zaznaczone, pole NIP oraz przycisk pobierania pojawiają się w formularzu kasy i na koncie klienta.')); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Ministry of Finance Registry API (Poland)', 'API Wykazu Podatników VAT MF (Polska)')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="flexcolo_enable_mf" value="1" <?php checked(1, $settings['enable_mf']); ?>>
                                        <?php echo esc_html(self::txt('Look up Polish NIP numbers via official Ministry of Finance API (wykazpodatnikowvat.mf.gov.pl)', 'Pobieraj dane polskich firm przez oficjalne API Ministerstwa Finansów (wykazpodatnikowvat.mf.gov.pl)')); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Mandatory NIP Field', 'Wymagalność Pola NIP')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="flexcolo_nip_required" value="1" <?php checked(1, $settings['nip_required']); ?>>
                                        <?php echo esc_html(self::txt('Make NIP field mandatory to complete checkout', 'Ustaw pole NIP jako obowiązkowe do złożenia zamówienia')); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Auto-Lookup on Typing', 'Automatyczne pobieranie po wpisaniu')); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="flexcolo_auto_lookup_digits" value="1" <?php checked(1, $settings['auto_lookup_digits']); ?>>
                                        <?php echo esc_html(self::txt('Automatically fetch company details after customer types 10 valid NIP digits', 'Automatycznie pobieraj dane firmy po wpisaniu 10 cyfr poprawnego numeru NIP')); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Button Label', 'Etykieta przycisku')); ?></th>
                                <td>
                                    <input type="text" name="flexcolo_button_label" value="<?php echo esc_attr($settings['button_label']); ?>" class="regular-text">
                                    <p class="description"><?php echo esc_html(self::txt('Default: Fetch Company Data / Pobierz dane po NIP', 'Domyślnie: Pobierz dane po NIP')); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Helper Hint Text', 'Tekst pomocniczy obok przycisku')); ?></th>
                                <td>
                                    <input type="text" name="flexcolo_hint_label" value="<?php echo esc_attr($settings['hint_label']); ?>" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html(self::txt('Cache Duration (Transients)', 'Czas buforowania (Transients)')); ?></th>
                                <td>
                                    <input type="number" name="flexcolo_cache_hours" value="<?php echo esc_attr($settings['cache_hours']); ?>" min="1" max="720" style="width: 80px;"> <?php echo esc_html(self::txt('hours (default: 24h)', 'godzin (domyślnie 24h)')); ?>
                                    <p class="description"><?php echo esc_html(self::txt('Caches registry responses to avoid redundant external HTTP requests for the same NIP.', 'Buforuje odpowiedzi rejestru, aby unikać powtarzających się zapytań dla tego samego numeru NIP.')); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary">
                                💾 <?php echo esc_html(self::txt('Save Settings', 'Zapisz Ustawienia')); ?>
                            </button>
                        </p>
                    </form>

                    <div class="flexcolo-cache-bar">
                        <div>
                            <strong><?php echo esc_html(self::txt('Cached NIP queries:', 'Pamięć podręczna zapytań NIP:')); ?></strong>
                            <span class="flexcolo-cache-badge">
                                <?php echo esc_html((string) $cached_count); ?> <?php echo esc_html(self::txt('saved companies', 'zapisanych firm')); ?>
                            </span>
                        </div>
                        <form method="post" action="">
                            <?php wp_nonce_field('flexcolo_nonce_action', 'flexcolo_nonce'); ?>
                            <input type="hidden" name="flexcolo_save_action" value="clear_cache">
                            <button type="submit" class="button" id="flexcolo-clear-cache-btn">
                                🗑️ <?php echo esc_html(self::txt('Clear NIP Transients Cache', 'Wyczyść pamięć podręczną NIP')); ?>
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($active_tab === 'diagnostics'): ?>
                <div class="flexcolo-admin-card">
                    <h2>
                        <?php echo esc_html(self::txt('API Connectivity Diagnostics & Live Test', 'Diagnostyka Połączenia z API Ministerstwa Finansów')); ?>
                    </h2>
                    <p>
                        <?php echo esc_html(self::txt('Test live communication with the official Polish Ministry of Finance White List API (wykazpodatnikowvat.mf.gov.pl).', 'Przetestuj połączenie na żywo z oficjalnym serwerem API Wykazu Podatników VAT Ministerstwa Finansów (wykazpodatnikowvat.mf.gov.pl).')); ?>
                    </p>

                    <div class="flexcolo-diag-controls">
                        <input type="text" id="flexcolo-diag-nip" class="flexcolo-diag-input" placeholder="<?php echo esc_attr(self::txt('Enter 10-digit NIP (e.g. 5261040828)', 'Wpisz 10-cyfrowy NIP (np. 5261040828)')); ?>">
                        <button type="button" id="flexcolo-diag-btn" class="button button-primary">
                            🔍 <?php echo esc_html(self::txt('Run Diagnostic Query', 'Uruchom Test API')); ?>
                        </button>
                    </div>

                    <div id="flexcolo-diag-result" style="margin-top: 20px; display: none;">
                        <h4><?php echo esc_html(self::txt('Server response:', 'Odpowiedź serwera:')); ?></h4>
                        <pre id="flexcolo-diag-output" class="flexcolo-diag-output"></pre>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }

    /**
     * Register REST API endpoint (/wp-json/flexcolo/v1/lookup)
     */
    public function register_rest_routes() {
        register_rest_route('flexcolo/v1', '/lookup', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_rest_lookup'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle NIP lookup via REST API
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function handle_rest_lookup(\WP_REST_Request $request) {
        $settings = $this->get_settings();
        if (empty($settings['enabled']) || empty($settings['enable_mf'])) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Moduł weryfikacji NIP jest obecnie wyłączony.', 'flexext-gus-company-lookup'),
            ], 403);
        }

        $raw_nip = (string) $request->get_param('nip');
        $clean_nip = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $raw_nip));

        if (strpos($clean_nip, 'PL') === 0) {
            $clean_nip = substr($clean_nip, 2);
        }

        if (empty($clean_nip)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Proszę podać 10-cyfrowy polski numer NIP.', 'flexext-gus-company-lookup'),
            ], 400);
        }

        // Validate Polish NIP Modulo 11 checksum before querying external API
        if (!$this->validate_polish_nip_checksum($clean_nip)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Nieprawidłowa suma kontrolna polskiego numeru NIP.', 'flexext-gus-company-lookup'),
            ], 422);
        }

        // Check WordPress Transients cache (prefixed with flexcolo_)
        $cache_key = 'flexcolo_pl_' . md5($clean_nip);
        $cached = get_transient($cache_key);
        if (false !== $cached && is_array($cached)) {
            $cached['cached'] = true;
            return new \WP_REST_Response($cached, 200);
        }

        $result = $this->query_polish_mf_api($clean_nip);

        if ($result && !empty($result['success'])) {
            $cache_hours = max(1, (int) ($settings['cache_hours'] ?? 24));
            set_transient($cache_key, $result, $cache_hours * HOUR_IN_SECONDS);
            return new \WP_REST_Response($result, 200);
        }

        $status_code = ($result && !empty($result['status_code'])) ? (int) $result['status_code'] : 404;
        return new \WP_REST_Response($result ?: [
            'success' => false,
            'message' => __('Nie odnaleziono podmiotu w rejestrze skarbowym.', 'flexext-gus-company-lookup'),
        ], $status_code);
    }

    /**
     * Validate 10-digit Polish NIP using weighted Modulo 11 checksum algorithm
     *
     * @param string $nip
     * @return bool
     */
    public function validate_polish_nip_checksum(string $nip): bool {
        if (strlen($nip) !== 10 || !ctype_digit($nip)) {
            return false;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $nip[$i]) * $weights[$i];
        }

        $control = $sum % 11;
        if ($control === 10) {
            return false;
        }

        return $control === ((int) $nip[9]);
    }

    /**
     * Query the official Polish Ministry of Finance White List API (wykazpodatnikowvat.mf.gov.pl)
     *
     * @param string $nip
     * @return array
     */
    private function query_polish_mf_api(string $nip): array {
        $today = gmdate('Y-m-d');
        $endpoint = self::MF_API_BASE . rawurlencode($nip) . '?date=' . rawurlencode($today);

        $response = wp_remote_get($endpoint, [
            'timeout'    => 10,
            'user-agent' => 'FlexExt-GUS-Company-Lookup/' . self::VERSION . ' (' . get_site_url() . ')',
            'headers'    => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return [
                'success'     => false,
                'status_code' => 503,
                'message'     => __('Błąd połączenia z serwerem Ministerstwa Finansów: ', 'flexext-gus-company-lookup') . $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code === 200 && isset($data['result']['subject']) && is_array($data['result']['subject'])) {
            $subject = $data['result']['subject'];
            $raw_address = !empty($subject['workingAddress']) ? (string) $subject['workingAddress'] : (string) ($subject['residenceAddress'] ?? '');
            $parsed_address = $this->parse_polish_address($raw_address);
            $status_vat = sanitize_text_field((string) ($subject['statusVat'] ?? ''));
            $request_id = sanitize_text_field((string) ($data['result']['requestId'] ?? ''));

            return [
                'success'     => true,
                'source'      => 'Biała Lista MF (wykazpodatnikowvat.mf.gov.pl)',
                'name'        => sanitize_text_field((string) ($subject['name'] ?? '')),
                'nip'         => sanitize_text_field((string) ($subject['nip'] ?? $nip)),
                'status_vat'  => $status_vat,
                'is_active'   => ($status_vat === 'Czynny'),
                'street'      => $parsed_address['street'],
                'postcode'    => $parsed_address['postcode'],
                'city'        => $parsed_address['city'],
                'address'     => $parsed_address,
                'raw_address' => sanitize_text_field($raw_address),
                'request_id'  => $request_id,
            ];
        }

        $error_msg = isset($data['message']) ? (string) $data['message'] : __('Nie znaleziono firmy o podanym numerze NIP w wykazie Ministerstwa Finansów.', 'flexext-gus-company-lookup');
        return [
            'success'     => false,
            'status_code' => $code > 0 ? $code : 404,
            'message'     => sanitize_text_field($error_msg),
        ];
    }

    /**
     * Parse Polish single-line registry address into street, postcode (XX-XXX), and city
     *
     * @param string $raw
     * @return array
     */
    public function parse_polish_address(string $raw): array {
        $result = [
            'street'   => '',
            'postcode' => '',
            'city'     => '',
        ];

        if (empty($raw)) {
            return $result;
        }

        $raw = trim($raw);

        if (preg_match('/(\d{2}-\d{3})\s+([^,]+)/u', $raw, $matches)) {
            $result['postcode'] = trim($matches[1]);
            $result['city'] = trim($matches[2]);
            $street_part = trim(str_replace($matches[0], '', $raw), " ,;\t\n\r\0\x0B");
            $street_part = preg_replace('/^(ul\.|al\.|pl\.)\s*/iu', '', $street_part);
            $result['street'] = trim((string) $street_part);
        } else {
            $parts = array_map('trim', explode(',', $raw));
            if (count($parts) >= 2) {
                $result['street'] = $parts[0];
                $result['city'] = $parts[1];
            } else {
                $result['street'] = $raw;
            }
        }

        return $result;
    }

    /**
     * Add billing_vat_nip field to WooCommerce checkout form
     *
     * @param array $fields
     * @return array
     */
    public function add_billing_nip_field($fields) {
        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return $fields;
        }

        if (empty($fields['billing_company'])) {
            $fields['billing_company'] = [
                'type'         => 'text',
                'label'        => self::txt('Company name', 'Nazwa firmy'),
                'placeholder'  => self::txt('Company name (autofilled from NIP)', 'Nazwa firmy (pobierana automatycznie po NIP)'),
                'required'     => false,
                'class'        => ['form-row-wide'],
                'priority'     => 20,
                'clear'        => true,
                'autocomplete' => 'organization',
            ];
        } else {
            $fields['billing_company']['required'] = false;
            $fields['billing_company']['class'] = array_values(array_diff($fields['billing_company']['class'] ?? [], ['hidden']));
            $fields['billing_company']['priority'] = 20;
            $fields['billing_company']['placeholder'] = self::txt('Company name (autofilled from NIP)', 'Nazwa firmy (pobierana automatycznie po NIP)');
        }

        $fields['billing_vat_nip'] = [
            'type'         => 'text',
            'label'        => self::txt('NIP (Tax ID)', 'NIP firmy'),
            'placeholder'  => self::txt('Enter 10-digit Polish NIP (e.g. 5261040828)', 'Wpisz 10-cyfrowy NIP firmy (np. 5261040828)'),
            'required'     => !empty($settings['nip_required']),
            'class'        => ['form-row-wide', 'flexcolo-nip-field-wrap'],
            'priority'     => 25,
            'clear'        => true,
            'autocomplete' => 'vat-id',
        ];

        return $fields;
    }

    /**
     * Render lookup button next to billing_vat_nip field
     *
     * @param string $field
     * @param string $key
     * @param array  $args
     * @param string $value
     * @return string
     */
    public function render_lookup_button_in_field($field, $key, $args, $value) {
        if ($key !== 'billing_vat_nip') {
            return $field;
        }

        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return $field;
        }

        $btn_text = !empty($settings['button_label']) ? esc_html($settings['button_label']) : esc_html(self::txt('Fetch Company Data', 'Pobierz dane po NIP'));
        $hint_text = !empty($settings['hint_label']) ? esc_html($settings['hint_label']) : esc_html(self::txt('Polish Ministry of Finance (MF) Registry', 'Biała Lista MF (PL)'));

        $button_html = '<div class="flexcolo-action-wrapper">'
            . '<div class="flexcolo-action-row">'
            . '  <button type="button" class="button button-secondary flexcolo-lookup-btn" id="flexcolo-lookup-btn">'
            . '    <span class="flexcolo-btn-icon" aria-hidden="true">🔍</span>'
            . '    <span class="flexcolo-btn-text">' . $btn_text . '</span>'
            . '    <span class="flexcolo-spinner" style="display:none;" aria-hidden="true">⏳</span>'
            . '  </button>'
            . '  <small class="flexcolo-hint">' . $hint_text . '</small>'
            . '</div>'
            . '<div class="flexcolo-status-msg" id="flexcolo-status-msg"></div>'
            . '</div>';

        return $field . $button_html;
    }

    /**
     * Anchor element for WooCommerce Checkout Blocks
     */
    public function render_checkout_nip_field_blocks_fallback() {
        echo '<div id="flexcolo-checkout-anchor" style="display:none;" data-version="' . esc_attr(self::VERSION) . '"></div>';
    }

    /**
     * Enqueue frontend checkout CSS and JS properly via wp_enqueue_*
     */
    public function enqueue_checkout_scripts() {
        if (!function_exists('is_checkout') || (!is_checkout() && !is_account_page())) {
            return;
        }

        $settings = $this->get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        $css_url = FLEXCOLO_PLUGIN_URL . 'assets/checkout-nip.css';
        $css_path = FLEXCOLO_PLUGIN_DIR . 'assets/checkout-nip.css';
        $css_ver = file_exists($css_path) ? (string) filemtime($css_path) : self::VERSION;

        wp_enqueue_style(
            'flexcolo-checkout',
            $css_url,
            [],
            $css_ver
        );

        $js_url = FLEXCOLO_PLUGIN_URL . 'assets/checkout-nip.js';
        $js_path = FLEXCOLO_PLUGIN_DIR . 'assets/checkout-nip.js';
        $js_ver = file_exists($js_path) ? (string) filemtime($js_path) : self::VERSION;

        wp_enqueue_script(
            'flexcolo-checkout',
            $js_url,
            [],
            $js_ver,
            true
        );

        $config_data = [
            'ajax_url'           => admin_url('admin-ajax.php'),
            'restUrl'            => esc_url_raw(rest_url('flexcolo/v1/lookup')),
            'rest_url'           => esc_url_raw(rest_url('flexcolo/v1/lookup')),
            'nonce'              => wp_create_nonce('wp_rest'),
            'button_label'       => esc_html($settings['button_label']),
            'auto_lookup_digits' => !empty($settings['auto_lookup_digits']),
            'enable_mf'          => !empty($settings['enable_mf']),
            'is_pl'              => self::is_pl(),
            'i18n'               => [
                'btnDefault'    => esc_html($settings['button_label']),
                'btnLoading'    => self::txt('Loading...', 'Pobieranie...'),
                'emptyNip'      => self::txt('Please enter a valid NIP before fetching data.', 'Wpisz numer NIP przed kliknięciem pobierania.'),
                'loading'       => self::txt('Verifying NIP in registry...', 'Weryfikacja NIP w rejestrze...'),
                'success'       => self::txt('Company details fetched successfully!', 'Dane firmy pobrane pomyślnie!'),
                'invalid_nip'   => self::txt('Invalid Modulo 11 tax ID checksum.', 'Podany NIP ma nieprawidłową sumę kontrolną.'),
                'not_found'     => self::txt('Company not found in registry.', 'Nie odnaleziono firmy w rejestrze MF.'),
                'digits_error'  => self::txt('Polish NIP must consist of exactly 10 digits.', 'Polski NIP musi składać się dokładnie z 10 cyfr.'),
                'network_error' => self::txt('Error connecting to NIP verification server.', 'Wystąpił błąd podczas łączenia z serwerem weryfikacji NIP.'),
            ],
        ];

        wp_localize_script('flexcolo-checkout', 'flexcolo_checkout_config', $config_data);
    }

    /**
     * Save NIP and lookup metadata in WooCommerce order
     *
     * @param \WC_Order $order
     * @param array     $data
     */
    public function save_nip_to_order($order, $data) {
        // Nonce is verified upstream by WooCommerce core checkout submission.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if (!empty($_POST['billing_vat_nip'])) {
            $nip = sanitize_text_field(wp_unslash($_POST['billing_vat_nip']));
            $order->update_meta_data('_billing_vat_nip', $nip);
            $order->update_meta_data('billing_vat_nip', $nip);

            if (!empty($_POST['flexcolo_vat_status'])) {
                $order->update_meta_data('_flexcolo_vat_nip_status', sanitize_text_field(wp_unslash($_POST['flexcolo_vat_status'])));
            }
            if (!empty($_POST['flexcolo_request_id'])) {
                $order->update_meta_data('_flexcolo_vat_nip_request_id', sanitize_text_field(wp_unslash($_POST['flexcolo_request_id'])));
            }
            if (!empty($_POST['flexcolo_source'])) {
                $order->update_meta_data('_flexcolo_vat_nip_source', sanitize_text_field(wp_unslash($_POST['flexcolo_source'])));
            }
            $order->update_meta_data('_flexcolo_vat_nip_verified_at', current_time('mysql'));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Display NIP in WooCommerce admin order view
     *
     * @param \WC_Order $order
     */
    public function display_nip_in_admin_order($order) {
        $nip = $order->get_meta('_billing_vat_nip') ?: $order->get_meta('billing_vat_nip');
        if (!$nip) {
            return;
        }
        $status = $order->get_meta('_flexcolo_vat_nip_status');
        $req_id = $order->get_meta('_flexcolo_vat_nip_request_id');
        ?>
        <div class="flexcolo-order-audit-proof">
            <p style="margin: 0; font-size: 13px;">
                <strong><?php esc_html_e('NIP / VAT ID:', 'flexext-gus-company-lookup'); ?></strong>
                <code style="font-size: 13px; font-weight: 700;"><?php echo esc_html((string) $nip); ?></code>
                <?php if (!empty($status)): ?>
                    <span style="font-size: 12px; margin-left: 6px;">(<?php echo esc_html((string) $status); ?>)</span>
                <?php endif; ?>
            </p>
            <?php if (!empty($req_id)): ?>
                <p style="margin: 4px 0 0 0; font-size: 11px;">
                    <strong><?php echo esc_html(self::txt('MF Request ID:', 'Identyfikator sprawdzenia MF:')); ?></strong>
                    <code><?php echo esc_html((string) $req_id); ?></code>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Display NIP on order confirmation (Thank You) page
     *
     * @param \WC_Order $order
     */
    public function display_nip_in_order_received($order) {
        $nip = $order->get_meta('_billing_vat_nip') ?: $order->get_meta('billing_vat_nip');
        if ($nip) {
            echo '<p><strong>' . esc_html__('NIP / VAT ID:', 'flexext-gus-company-lookup') . '</strong> ' . esc_html((string) $nip) . '</p>';
        }
    }

    /**
     * Display NIP in WooCommerce order emails
     *
     * @param \WC_Order $order
     * @param bool      $sent_to_admin
     * @param bool      $plain_text
     * @param \WC_Email $email
     */
    public function display_nip_in_email($order, $sent_to_admin, $plain_text, $email) {
        $nip = $order->get_meta('_billing_vat_nip') ?: $order->get_meta('billing_vat_nip');
        if (!$nip) {
            return;
        }

        if ($plain_text) {
            echo "\n" . esc_html__('NIP / VAT ID:', 'flexext-gus-company-lookup') . ' ' . esc_html((string) $nip) . "\n";
        } else {
            echo '<p style="margin-top: 15px;"><strong>' . esc_html__('NIP / VAT ID:', 'flexext-gus-company-lookup') . '</strong> ' . esc_html((string) $nip) . '</p>';
        }
    }
}

/**
 * Bootstrap FlexExt Company Lookup for GUS
 */
function flexcolo_boot_plugin() {
    FLEXCOLO_Company_Lookup::get_instance();
}
add_action('plugins_loaded', 'flexcolo_boot_plugin');
