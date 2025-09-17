<?php
/**
 * Plugin Name: Send WhatsApp
 * Plugin URI: https://example.com/send-whatsapp
 * Description: Crea link WhatsApp per aprire una chat con il numero configurato e il titolo del post corrente.
 * Version: 1.0.0
 * Author: ChatGPT
 * License: GPLv2 or later
 * Text Domain: send-whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SEND_WHATSAPP_OPTION_PHONE', 'send_whatsapp_phone_number' );
define( 'SEND_WHATSAPP_OPTION_PREFIX', 'send_whatsapp_text_prefix' );
define( 'SEND_WHATSAPP_SHORTCODE', 'send_whatsapp_link' );

add_action( 'admin_menu', 'send_whatsapp_register_menu' );
add_action( 'admin_init', 'send_whatsapp_register_settings' );

/**
 * Adds the plugin configuration page to the WordPress admin menu.
 */
function send_whatsapp_register_menu() {
    add_menu_page(
        __( 'Configurazione', 'send-whatsapp' ),
        __( 'Send WhatsApp', 'send-whatsapp' ),
        'manage_options',
        'send-whatsapp',
        'send_whatsapp_render_settings_page',
        'dashicons-format-chat'
    );
}

/**
 * Registers the plugin settings.
 */
function send_whatsapp_register_settings() {
    register_setting( 'send_whatsapp_settings_group', SEND_WHATSAPP_OPTION_PHONE, [
        'type'              => 'string',
        'sanitize_callback' => 'send_whatsapp_sanitize_phone',
        'default'           => '',
    ] );

    register_setting( 'send_whatsapp_settings_group', SEND_WHATSAPP_OPTION_PREFIX, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ] );
}

/**
 * Sanitizes the phone number.
 *
 * @param string $phone Phone number input.
 *
 * @return string
 */
function send_whatsapp_sanitize_phone( $phone ) {
    $phone = preg_replace( '/\s+/', '', $phone );

    if ( ! preg_match( '/^[0-9]+$/', $phone ) ) {
        add_settings_error(
            SEND_WHATSAPP_OPTION_PHONE,
            'invalid_phone',
            __( 'Il numero di telefono deve contenere solo cifre e includere il prefisso internazionale.', 'send-whatsapp' )
        );
    }

    return $phone;
}

/**
 * Renders the plugin configuration page.
 */
function send_whatsapp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $phone  = get_option( SEND_WHATSAPP_OPTION_PHONE, '' );
    $prefix = get_option( SEND_WHATSAPP_OPTION_PREFIX, '' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configurazione', 'send-whatsapp' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'send_whatsapp_settings_group' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="send_whatsapp_phone_number"><?php esc_html_e( 'Inserisci il numero di cellulare compreso di prefisso', 'send-whatsapp' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="send_whatsapp_phone_number" name="<?php echo esc_attr( SEND_WHATSAPP_OPTION_PHONE ); ?>" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" placeholder="11234567890" />
                        <p class="description"><?php esc_html_e( 'Inserisci solo numeri, senza spazi, parentesi o segni.', 'send-whatsapp' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="send_whatsapp_text_prefix"><?php esc_html_e( 'Breve testo da mostrare prima del titolo del post', 'send-whatsapp' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="send_whatsapp_text_prefix" name="<?php echo esc_attr( SEND_WHATSAPP_OPTION_PREFIX ); ?>" value="<?php echo esc_attr( $prefix ); ?>" class="regular-text" placeholder="Scrivi il tuo messaggio: " />
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
        <h2><?php esc_html_e( 'Shortcode disponibile', 'send-whatsapp' ); ?></h2>
        <p><code>[<?php echo esc_html( SEND_WHATSAPP_SHORTCODE ); ?>]</code></p>
    </div>
    <?php
}

/**
 * Generates the WhatsApp chat URL.
 *
 * @param int|null $post_id Optional. Post ID to use when outside the loop.
 *
 * @return string
 */
function send_whatsapp_generate_url( $post_id = null ) {
    $phone = get_option( SEND_WHATSAPP_OPTION_PHONE, '' );

    if ( empty( $phone ) ) {
        return '';
    }

    if ( null === $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return '';
    }

    $title       = get_the_title( $post_id );
    $prefix_text = get_option( SEND_WHATSAPP_OPTION_PREFIX, '' );
    $message     = trim( $prefix_text . ' ' . $title );
    $message     = wp_strip_all_tags( $message );

    $base_url = 'https://wa.me/' . rawurlencode( $phone );

    if ( '' === $message ) {
        return $base_url;
    }

    return $base_url . '?text=' . rawurlencode( $message );
}

/**
 * Shortcode handler to display the WhatsApp link.
 *
 * @return string
 */
function send_whatsapp_shortcode_handler() {
    $url = send_whatsapp_generate_url();

    if ( empty( $url ) ) {
        return '';
    }

    $post_id = get_the_ID();
    $title   = get_the_title( $post_id );

    return sprintf(
        '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
        esc_url( $url ),
        esc_html( $title )
    );
}
add_shortcode( SEND_WHATSAPP_SHORTCODE, 'send_whatsapp_shortcode_handler' );

