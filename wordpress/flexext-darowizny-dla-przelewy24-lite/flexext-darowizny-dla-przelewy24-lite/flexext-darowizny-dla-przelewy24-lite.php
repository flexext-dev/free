<?php
/**
 * Plugin Name:       FlexExt Darowizny dla Przelewy24 Lite
 * Description:       Szybkie i bezpieczne darowizny dla Przelewy24 (BLIK, karty, szybki przelew). Lekki widżet z trzema stałymi kwotami darowizny (PLN) dla fundacji, zbiórek i twórców.
 * Version:           1.2.2
 * Author:            FlexExt
 * Author URI:        https://flexext.dev
 * Text Domain:       flexext-darowizny-dla-przelewy24-lite
 * Domain Path:       /languages
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FEXTL_VERSION', '1.2.2' );
define( 'FEXTL_OPTION', 'fextl_settings' );
define( 'FEXTL_SLUG', 'fextl' );
define( 'FEXTL_REST_NS', 'fextl/v1' );
define( 'FEXTL_UPGRADE_URL', 'https://flexext.dev/en-gb/product/p24-donations-pro-wordpress?utm_source=wp-plugin&utm_medium=admin-cta&utm_campaign=p24-lite' );
define( 'FEXTL_P24_LIVE_URL', 'https://secure.przelewy24.pl' );
define( 'FEXTL_P24_SANDBOX_URL', 'https://sandbox.przelewy24.pl' );
define( 'FEXTL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FEXTL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Default settings configuration (zero hardcoded credentials).
 */
function fextl_defaults() {
    return [
        'enabled'      => '0',
        'sandbox'      => '1',
        'merchant_id'  => '',
        'crc_key'      => '',
        'report_key'   => '',
        'email'        => '',
        'title'        => __( 'Wsparcie projektu (Darowizna)', 'flexext-darowizny-dla-przelewy24-lite' ),
        'amount_1'     => '10',
        'amount_2'     => '20',
        'amount_3'     => '50',
        'description'  => __( 'Darowizna na wsparcie projektu', 'flexext-darowizny-dla-przelewy24-lite' ),
        'success_url'  => '',
        'error_url'    => '',
        'button_label' => __( 'Wpłać darowiznę', 'flexext-darowizny-dla-przelewy24-lite' ),
    ];
}

/**
 * Get saved plugin settings.
 */
function fextl_get_settings() {
    return wp_parse_args( get_option( FEXTL_OPTION, [] ), fextl_defaults() );
}

/**
 * Register administration menu page.
 */
function fextl_admin_menu() {
    add_menu_page(
        __( 'P24 Darowizny', 'flexext-darowizny-dla-przelewy24-lite' ),
        __( 'P24 Darowizny', 'flexext-darowizny-dla-przelewy24-lite' ),
        'manage_options',
        FEXTL_SLUG,
        'fextl_render_admin_page',
        'dashicons-money-alt',
        56
    );
}
add_action( 'admin_menu', 'fextl_admin_menu' );

/**
 * Register settings in WordPress settings API.
 */
function fextl_register_settings() {
    register_setting( 'fextl_group', FEXTL_OPTION, 'fextl_sanitize_settings' );
}
add_action( 'admin_init', 'fextl_register_settings' );

/**
 * Sanitize monetary amount string.
 */
function fextl_sanitize_amount( $value, $fallback ) {
    $value = str_replace( ',', '.', (string) $value );
    if ( ! is_numeric( $value ) ) {
        return $fallback;
    }
    $num = (float) $value;
    if ( $num < 1 || $num > 50000 ) {
        return $fallback;
    }
    return rtrim( rtrim( number_format( $num, 2, '.', '' ), '0' ), '.' );
}

/**
 * Sanitize all plugin settings before saving.
 */
function fextl_sanitize_settings( $input ) {
    $d = fextl_defaults();
    return [
        'enabled'      => ! empty( $input['enabled'] ) ? '1' : '0',
        'sandbox'      => ! empty( $input['sandbox'] ) ? '1' : '0',
        'merchant_id'  => sanitize_text_field( $input['merchant_id'] ?? '' ),
        'crc_key'      => sanitize_text_field( $input['crc_key'] ?? '' ),
        'report_key'   => sanitize_text_field( $input['report_key'] ?? '' ),
        'email'        => sanitize_email( $input['email'] ?? '' ),
        'title'        => sanitize_text_field( $input['title'] ?? $d['title'] ),
        'amount_1'     => fextl_sanitize_amount( $input['amount_1'] ?? '', $d['amount_1'] ),
        'amount_2'     => fextl_sanitize_amount( $input['amount_2'] ?? '', $d['amount_2'] ),
        'amount_3'     => fextl_sanitize_amount( $input['amount_3'] ?? '', $d['amount_3'] ),
        'description'  => sanitize_text_field( $input['description'] ?? $d['description'] ),
        'success_url'  => esc_url_raw( $input['success_url'] ?? '' ),
        'error_url'    => esc_url_raw( $input['error_url'] ?? '' ),
        'button_label' => sanitize_text_field( $input['button_label'] ?? $d['button_label'] ),
    ];
}

/**
 * Render admin settings page.
 */
function fextl_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $s = fextl_get_settings();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?> <span style="font-size:13px;font-weight:400;color:#64748b">v<?php echo esc_html( FEXTL_VERSION ); ?> Lite</span></h1>

        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 20px;margin:16px 0;max-width:760px">
            <h2 style="margin-top:0;font-size:16px"><?php esc_html_e( 'How to add the widget to your site', 'flexext-darowizny-dla-przelewy24-lite' ); ?></h2>
            <ol style="margin:0;padding-left:20px;line-height:1.7">
                <li><?php esc_html_e( 'Fill in the Przelewy24 credentials below and enable the widget.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></li>
                <li><?php esc_html_e( 'Edit any page or post where you want the donation buttons to appear.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></li>
                <li>
                    <?php
                    /* translators: %s: shortcode tag [flexext_lite] */
                    printf( esc_html__( 'Insert the shortcode %s using a Shortcode block or directly in the classic editor.', 'flexext-darowizny-dla-przelewy24-lite' ), '<code>[flexext_lite]</code>' );
                    ?>
                </li>
                <li><?php esc_html_e( 'Publish or update the page. The widget will appear where you placed the shortcode.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></li>
            </ol>
            <p style="margin:10px 0 0;color:#64748b"><?php esc_html_e( 'You can place the shortcode on multiple pages; it will always use the settings configured here.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></p>
        </div>

        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 18px;margin:16px 0;max-width:760px">
            <strong style="color:#166534"><?php esc_html_e( 'Want custom amounts, multiple currencies, your own logo and colors, a floating widget, and a public donor list?', 'flexext-darowizny-dla-przelewy24-lite' ); ?></strong>
            <p style="margin:6px 0 0"><a href="<?php echo esc_url( FEXTL_UPGRADE_URL ); ?>" target="_blank" rel="noopener noreferrer" style="font-weight:600;color:#15803d"><?php esc_html_e( 'Upgrade to FlexExt PRO', 'flexext-darowizny-dla-przelewy24-lite' ); ?> &rarr;</a></p>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'fextl_group' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable', 'flexext-darowizny-dla-przelewy24-lite' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[enabled]" value="1" <?php checked( $s['enabled'], '1' ); ?>>
                            <?php esc_html_e( 'Enable the donation widget', 'flexext-darowizny-dla-przelewy24-lite' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Sandbox mode', 'flexext-darowizny-dla-przelewy24-lite' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[sandbox]" value="1" <?php checked( $s['sandbox'], '1' ); ?>>
                            <?php esc_html_e( 'Use Przelewy24 sandbox environment for testing', 'flexext-darowizny-dla-przelewy24-lite' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_merchant_id"><?php esc_html_e( 'Merchant ID', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_merchant_id" type="text" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[merchant_id]" value="<?php echo esc_attr( $s['merchant_id'] ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Found in your Przelewy24 panel.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_crc_key"><?php esc_html_e( 'CRC key', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_crc_key" type="password" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[crc_key]" value="<?php echo esc_attr( $s['crc_key'] ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Found in your Przelewy24 panel.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_report_key"><?php esc_html_e( 'Report key', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_report_key" type="password" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[report_key]" value="<?php echo esc_attr( $s['report_key'] ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Also called the API key in the Przelewy24 panel.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_email"><?php esc_html_e( 'Payment email', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_email" type="email" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[email]" value="<?php echo esc_attr( $s['email'] ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Required by Przelewy24 for each transaction.', 'flexext-darowizny-dla-przelewy24-lite' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_title"><?php esc_html_e( 'Title', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_title" type="text" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[title]" value="<?php echo esc_attr( $s['title'] ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Donation amounts (PLN)', 'flexext-darowizny-dla-przelewy24-lite' ); ?></th>
                    <td>
                        <label style="margin-right:12px">
                            <?php esc_html_e( 'Amount 1 (PLN)', 'flexext-darowizny-dla-przelewy24-lite' ); ?>:
                            <input type="text" style="width:70px" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[amount_1]" value="<?php echo esc_attr( $s['amount_1'] ); ?>">
                        </label>
                        <label style="margin-right:12px">
                            <?php esc_html_e( 'Amount 2 (PLN)', 'flexext-darowizny-dla-przelewy24-lite' ); ?>:
                            <input type="text" style="width:70px" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[amount_2]" value="<?php echo esc_attr( $s['amount_2'] ); ?>">
                        </label>
                        <label>
                            <?php esc_html_e( 'Amount 3 (PLN)', 'flexext-darowizny-dla-przelewy24-lite' ); ?>:
                            <input type="text" style="width:70px" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[amount_3]" value="<?php echo esc_attr( $s['amount_3'] ); ?>">
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_description"><?php esc_html_e( 'Description', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_description" type="text" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[description]" value="<?php echo esc_attr( $s['description'] ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fextl_success_url"><?php esc_html_e( 'Success URL', 'flexext-darowizny-dla-przelewy24-lite' ); ?></label></th>
                    <td>
                        <input id="fextl_success_url" type="url" name="<?php echo esc_attr( FEXTL_OPTION ); ?>[success_url]" value="<?php echo esc_attr( $s['success_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/thank-you/' ) ); ?>">
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save settings', 'flexext-darowizny-dla-przelewy24-lite' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Register REST API routes for processing payments.
 */
function fextl_register_routes() {
    register_rest_route( FEXTL_REST_NS, '/pay', [
        'methods'             => 'POST',
        'callback'            => 'fextl_create_payment',
        'permission_callback' => '__return_true',
        'args'                => [
            'amount' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ] );

    register_rest_route( FEXTL_REST_NS, '/notify', [
        'methods'             => 'POST',
        'callback'            => 'fextl_notify',
        'permission_callback' => '__return_true',
    ] );
}
add_action( 'rest_api_init', 'fextl_register_routes' );

/**
 * REST API Callback: Create Przelewy24 transaction.
 */
function fextl_create_payment( WP_REST_Request $request ) {
    $s = fextl_get_settings();

    if ( $s['enabled'] !== '1' ) {
        return new WP_REST_Response( [ 'error' => __( 'Widget is disabled.', 'flexext-darowizny-dla-przelewy24-lite' ) ], 403 );
    }

    if ( empty( $s['merchant_id'] ) || empty( $s['crc_key'] ) || empty( $s['report_key'] ) || empty( $s['email'] ) ) {
        return new WP_REST_Response( [ 'error' => __( 'Missing Przelewy24 configuration. Complete all fields in FlexExt Lite settings.', 'flexext-darowizny-dla-przelewy24-lite' ) ], 400 );
    }

    $requested_amount = (string) $request->get_param( 'amount' );
    $allowed = array_filter( [ $s['amount_1'], $s['amount_2'], $s['amount_3'] ] );

    if ( ! in_array( $requested_amount, $allowed, true ) ) {
        return new WP_REST_Response( [ 'error' => __( 'Invalid amount selection.', 'flexext-darowizny-dla-przelewy24-lite' ) ], 400 );
    }

    $base_url     = ( $s['sandbox'] === '1' ) ? FEXTL_P24_SANDBOX_URL : FEXTL_P24_LIVE_URL;
    $amount_gross = (int) round( (float) $requested_amount * 100 );
    $session_id   = wp_generate_uuid4();
    $return_url   = ! empty( $s['success_url'] ) ? $s['success_url'] : home_url( '/thank-you/' );
    $status_url   = rest_url( FEXTL_REST_NS . '/notify' );

    $sign = hash( 'sha384', wp_json_encode( [
        'sessionId'  => $session_id,
        'merchantId' => (int) $s['merchant_id'],
        'amount'     => $amount_gross,
        'currency'   => 'PLN',
        'crc'        => $s['crc_key'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );

    $payload = [
        'merchantId'  => (int) $s['merchant_id'],
        'posId'       => (int) $s['merchant_id'],
        'sessionId'   => $session_id,
        'amount'      => $amount_gross,
        'currency'    => 'PLN',
        'description' => $s['description'],
        'email'       => $s['email'],
        'country'     => 'PL',
        'language'    => ( strpos( get_locale(), 'pl_' ) === 0 ) ? 'pl' : 'en',
        'urlReturn'   => $return_url,
        'urlStatus'   => $status_url,
        'sign'        => $sign,
    ];

    $response = wp_remote_post( $base_url . '/api/v1/transaction/register', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode( $s['merchant_id'] . ':' . $s['report_key'] ),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
        'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
        'timeout' => 20,
    ] );

    if ( is_wp_error( $response ) ) {
        return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code === 200 && ! empty( $body['data']['token'] ) ) {
        return new WP_REST_Response( [ 'url' => $base_url . '/trnRequest/' . $body['data']['token'] ], 200 );
    }

    $message = $body['message'] ?? $body['error'] ?? __( 'Transaction registration failed.', 'flexext-darowizny-dla-przelewy24-lite' );
    return new WP_REST_Response( [ 'error' => $message, 'http' => $code ], 500 );
}

/**
 * Payment notification endpoint.
 */
function fextl_notify() {
    return new WP_REST_Response( [ 'status' => 'ok' ], 200 );
}

/**
 * Register frontend scripts and styles.
 */
function fextl_register_assets() {
    wp_register_style(
        'fextl-widget-css',
        FEXTL_PLUGIN_URL . 'assets/css/widget.css',
        [],
        FEXTL_VERSION
    );

    wp_register_script(
        'fextl-widget-js',
        FEXTL_PLUGIN_URL . 'assets/js/widget.js',
        [],
        FEXTL_VERSION,
        true
    );

    wp_localize_script( 'fextl-widget-js', 'fextlConfig', [
        'restUrl'         => esc_url_raw( rest_url( FEXTL_REST_NS . '/pay' ) ),
        'nonce'           => wp_create_nonce( 'wp_rest' ),
        'errorGeneric'   => esc_html__( 'Payment could not be started.', 'flexext-darowizny-dla-przelewy24-lite' ),
        'errorConnection'=> esc_html__( 'Connection error. Please try again.', 'flexext-darowizny-dla-przelewy24-lite' ),
    ] );
}
add_action( 'wp_enqueue_scripts', 'fextl_register_assets' );

/**
 * Shortcode callback: [flexext_lite]
 */
function fextl_shortcode() {
    $s = fextl_get_settings();
    if ( $s['enabled'] !== '1' ) {
        return '';
    }

    wp_enqueue_style( 'fextl-widget-css' );
    wp_enqueue_script( 'fextl-widget-js' );

    $amounts = array_filter( [ $s['amount_1'], $s['amount_2'], $s['amount_3'] ] );
    $id = 'fextl-' . wp_generate_password( 6, false, false );

    ob_start();
    ?>
    <div class="fextl-widget" id="<?php echo esc_attr( $id ); ?>">
        <h3 class="fextl-title"><?php echo esc_html( $s['title'] ); ?></h3>
        <div class="fextl-amounts" role="group" aria-label="<?php esc_attr_e( 'Choose an amount or enter your own.', 'flexext-darowizny-dla-przelewy24-lite' ); ?>">
            <?php foreach ( $amounts as $amount ) : ?>
                <?php
                /* translators: %s: donation amount in PLN */
                $fextl_aria = sprintf( __( 'Donate %s PLN', 'flexext-darowizny-dla-przelewy24-lite' ), $amount );
                ?>
                <button type="button" class="fextl-amount-btn" data-amount="<?php echo esc_attr( $amount ); ?>" aria-label="<?php echo esc_attr( $fextl_aria ); ?>">
                    <?php echo esc_html( $amount ); ?> PLN
                </button>
            <?php endforeach; ?>
        </div>
        <div class="fextl-message" aria-live="polite"></div>
        <div class="fextl-footer">
            <span class="fextl-security-badge">
                <span class="dashicons dashicons-lock" aria-hidden="true" style="font-size:14px;width:14px;height:14px"></span>
                <?php esc_html_e( 'Przelewy24 Secure', 'flexext-darowizny-dla-przelewy24-lite' ); ?>
            </span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'flexext_lite', 'fextl_shortcode' );

register_activation_hook( __FILE__, function() {
    if ( ! get_option( FEXTL_OPTION ) ) {
        add_option( FEXTL_OPTION, fextl_defaults() );
    }
} );
