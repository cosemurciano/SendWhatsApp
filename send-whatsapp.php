<?php
/**
 * Plugin Name: Send WhatsApp
 * Plugin URI: https://example.com/send-whatsapp
 * Description: Crea link WhatsApp per aprire una chat con il numero configurato e il titolo del post corrente.
 * Version: 1.0.0
 * Author: Cosè Murciano
 * License: GPLv2 or later
 * Text Domain: send-whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SEND_WHATSAPP_OPTION_PHONE', 'send_whatsapp_phone_number' );
define( 'SEND_WHATSAPP_OPTION_PREFIX', 'send_whatsapp_text_prefix' );
define( 'SEND_WHATSAPP_SHORTCODE', 'send_whatsapp_link' );
define( 'SEND_WHATSAPP_OPTION_LINK_TEXT', 'send_whatsapp_link_text' );
define( 'SEND_WHATSAPP_OPTION_DISPLAY_MODE', 'send_whatsapp_display_mode' );

define( 'SEND_WHATSAPP_DISPLAY_TEXT', 'text' );
define( 'SEND_WHATSAPP_DISPLAY_BUTTON_SMALL', 'button_small' );
define( 'SEND_WHATSAPP_DISPLAY_BUTTON_MEDIUM', 'button_medium' );
define( 'SEND_WHATSAPP_DISPLAY_BUTTON_LARGE', 'button_large' );

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

    register_setting( 'send_whatsapp_settings_group', SEND_WHATSAPP_OPTION_LINK_TEXT, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ] );

    register_setting( 'send_whatsapp_settings_group', SEND_WHATSAPP_OPTION_DISPLAY_MODE, [
        'type'              => 'string',
        'sanitize_callback' => 'send_whatsapp_sanitize_display_mode',
        'default'           => SEND_WHATSAPP_DISPLAY_TEXT,
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

    $phone        = get_option( SEND_WHATSAPP_OPTION_PHONE, '' );
    $prefix       = get_option( SEND_WHATSAPP_OPTION_PREFIX, '' );
    $link_text    = get_option( SEND_WHATSAPP_OPTION_LINK_TEXT, '' );
    $display_mode = get_option( SEND_WHATSAPP_OPTION_DISPLAY_MODE, SEND_WHATSAPP_DISPLAY_TEXT );
    $display_mode = send_whatsapp_sanitize_display_mode( $display_mode );

    $shortcode_attributes = [
        'phone'  => $phone,
        'prefix' => $prefix,
        'text'   => $link_text,
        'mode'   => $display_mode,
    ];

    $shortcode_parts = [];

    foreach ( $shortcode_attributes as $attribute_key => $attribute_value ) {
        $shortcode_parts[] = sprintf(
            '%1$s="%2$s"',
            esc_attr( $attribute_key ),
            esc_attr( $attribute_value )
        );
    }

    $shortcode_example = sprintf(
        '[%1$s %2$s]',
        SEND_WHATSAPP_SHORTCODE,
        implode( ' ', $shortcode_parts )
    );

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
                <tr>
                    <th scope="row">
                        <label for="send_whatsapp_link_text"><?php esc_html_e( 'Testo da mostrare insieme al link', 'send-whatsapp' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="send_whatsapp_link_text" name="<?php echo esc_attr( SEND_WHATSAPP_OPTION_LINK_TEXT ); ?>" value="<?php echo esc_attr( $link_text ); ?>" class="regular-text" placeholder="Contattaci su WhatsApp" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="send_whatsapp_display_mode"><?php esc_html_e( 'Modalità di visualizzazione del link', 'send-whatsapp' ); ?></label>
                    </th>
                    <td>
                        <select id="send_whatsapp_display_mode" name="<?php echo esc_attr( SEND_WHATSAPP_OPTION_DISPLAY_MODE ); ?>">
                            <option value="<?php echo esc_attr( SEND_WHATSAPP_DISPLAY_TEXT ); ?>" <?php selected( $display_mode, SEND_WHATSAPP_DISPLAY_TEXT ); ?>><?php esc_html_e( 'Solo testo con link', 'send-whatsapp' ); ?></option>
                            <option value="<?php echo esc_attr( SEND_WHATSAPP_DISPLAY_BUTTON_SMALL ); ?>" <?php selected( $display_mode, SEND_WHATSAPP_DISPLAY_BUTTON_SMALL ); ?>><?php esc_html_e( 'Pulsante piccolo', 'send-whatsapp' ); ?></option>
                            <option value="<?php echo esc_attr( SEND_WHATSAPP_DISPLAY_BUTTON_MEDIUM ); ?>" <?php selected( $display_mode, SEND_WHATSAPP_DISPLAY_BUTTON_MEDIUM ); ?>><?php esc_html_e( 'Pulsante medio', 'send-whatsapp' ); ?></option>
                            <option value="<?php echo esc_attr( SEND_WHATSAPP_DISPLAY_BUTTON_LARGE ); ?>" <?php selected( $display_mode, SEND_WHATSAPP_DISPLAY_BUTTON_LARGE ); ?>><?php esc_html_e( 'Pulsante grande', 'send-whatsapp' ); ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
        <h2><?php esc_html_e( 'Shortcode disponibile', 'send-whatsapp' ); ?></h2>
        <p class="send-whatsapp-shortcode-wrapper">
            <code><?php echo esc_html( $shortcode_example ); ?></code>
            <button type="button" class="button send-whatsapp-copy-button" data-shortcode="<?php echo esc_attr( $shortcode_example ); ?>" aria-label="<?php esc_attr_e( 'Copia shortcode', 'send-whatsapp' ); ?>">
                <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                <span class="screen-reader-text"><?php esc_html_e( 'Copia shortcode', 'send-whatsapp' ); ?></span>
            </button>
        </p>
    </div>
    <script>
        (function() {
            const copyButton = document.querySelector('.send-whatsapp-copy-button');

            if (!copyButton || !window.navigator || !window.navigator.clipboard) {
                return;
            }

            const shortcode = copyButton.getAttribute('data-shortcode');
            const originalLabel = copyButton.getAttribute('aria-label');

            copyButton.addEventListener('click', function() {
                if (!shortcode) {
                    return;
                }

                window.navigator.clipboard.writeText(shortcode).then(function() {
                    copyButton.classList.add('send-whatsapp-copy-button--copied');
                    copyButton.setAttribute('aria-label', '<?php echo esc_js( __( 'Shortcode copiato!', 'send-whatsapp' ) ); ?>');
                    copyButton.querySelector('.dashicons').classList.remove('dashicons-clipboard');
                    copyButton.querySelector('.dashicons').classList.add('dashicons-yes');

                    window.setTimeout(function() {
                        copyButton.classList.remove('send-whatsapp-copy-button--copied');
                        copyButton.setAttribute('aria-label', originalLabel);
                        copyButton.querySelector('.dashicons').classList.remove('dashicons-yes');
                        copyButton.querySelector('.dashicons').classList.add('dashicons-clipboard');
                    }, 2000);
                });
            });
        })();
    </script>
    <?php
}

/**
 * Sanitizes the selected display mode.
 *
 * @param string $mode Display mode input.
 *
 * @return string
 */
function send_whatsapp_sanitize_display_mode( $mode ) {
    $allowed_modes = [
        SEND_WHATSAPP_DISPLAY_TEXT,
        SEND_WHATSAPP_DISPLAY_BUTTON_SMALL,
        SEND_WHATSAPP_DISPLAY_BUTTON_MEDIUM,
        SEND_WHATSAPP_DISPLAY_BUTTON_LARGE,
    ];

    if ( ! in_array( $mode, $allowed_modes, true ) ) {
        return SEND_WHATSAPP_DISPLAY_TEXT;
    }

    return $mode;
}

/**
 * Generates the WhatsApp chat URL.
 *
 * @param int|null $post_id Optional. Post ID to use when outside the loop.
 *
 * @return string
 */
function send_whatsapp_generate_url( $post_id = null, $phone = null, $prefix_text = null ) {
    if ( null === $phone ) {
        $phone = get_option( SEND_WHATSAPP_OPTION_PHONE, '' );
    }

    if ( empty( $phone ) ) {
        return '';
    }

    if ( null === $post_id ) {
        $post_id = get_the_ID();
    }

    $title = send_whatsapp_get_content_title( $post_id ? $post_id : null );

    if ( '' === $title ) {
        return '';
    }

    if ( null === $prefix_text ) {
        $prefix_text = get_option( SEND_WHATSAPP_OPTION_PREFIX, '' );
    }
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
function send_whatsapp_get_content_title( $post_id = null ) {
    if ( null !== $post_id && $post_id ) {
        $title = get_the_title( $post_id );

        if ( '' !== $title ) {
            return $title;
        }
    }

    $queried_object = get_queried_object();

    if ( $queried_object instanceof \WP_Post ) {
        $title = get_the_title( $queried_object );

        if ( '' !== $title ) {
            return $title;
        }
    }

    if ( $queried_object instanceof \WP_Term ) {
        return $queried_object->name;
    }

    if ( $queried_object instanceof \WP_User ) {
        return $queried_object->display_name;
    }

    $title = single_post_title( '', false );

    if ( '' !== $title ) {
        return $title;
    }

    return get_bloginfo( 'name', 'display' );
}

function send_whatsapp_shortcode_handler( $atts = [] ) {
    $atts = shortcode_atts(
        [
            'phone'  => '',
            'prefix' => '',
            'text'   => '',
            'mode'   => '',
        ],
        $atts,
        SEND_WHATSAPP_SHORTCODE
    );

    $phone = '' !== $atts['phone'] ? preg_replace( '/\s+/', '', $atts['phone'] ) : null;

    if ( null !== $phone && ! preg_match( '/^[0-9]+$/', $phone ) ) {
        $phone = null;
    }

    $prefix_text = '' !== $atts['prefix'] ? $atts['prefix'] : null;

    $url = send_whatsapp_generate_url( null, $phone, $prefix_text );

    if ( empty( $url ) ) {
        return '';
    }

    if ( '' !== $atts['text'] ) {
        $link_text = $atts['text'];
    } else {
        $link_text = get_option( SEND_WHATSAPP_OPTION_LINK_TEXT, '' );
    }

    if ( '' !== $atts['mode'] ) {
        $display_mode = $atts['mode'];
    } else {
        $display_mode = get_option( SEND_WHATSAPP_OPTION_DISPLAY_MODE, SEND_WHATSAPP_DISPLAY_TEXT );
    }

    if ( '' === $link_text ) {
        $display_text = __( 'Apri chat WhatsApp', 'send-whatsapp' );
    } else {
        $display_text = $link_text;
    }

    $display_mode = send_whatsapp_sanitize_display_mode( $display_mode );

    if ( SEND_WHATSAPP_DISPLAY_TEXT === $display_mode ) {
        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url( $url ),
            esc_html( $display_text )
        );
    }

    send_whatsapp_enqueue_frontend_styles();

    $size_class = '';

    switch ( $display_mode ) {
        case SEND_WHATSAPP_DISPLAY_BUTTON_SMALL:
            $size_class = 'send-whatsapp-button--small';
            break;
        case SEND_WHATSAPP_DISPLAY_BUTTON_MEDIUM:
            $size_class = 'send-whatsapp-button--medium';
            break;
        case SEND_WHATSAPP_DISPLAY_BUTTON_LARGE:
            $size_class = 'send-whatsapp-button--large';
            break;
    }

    $icon_markup  = send_whatsapp_get_icon_markup();
    $label_markup = sprintf( '<span class="send-whatsapp-button__label">%s</span>', esc_html( $display_text ) );

    $button_markup = sprintf(
        '<a class="send-whatsapp-button %2$s" href="%1$s" target="_blank" rel="noopener noreferrer">%3$s%4$s</a>',
        esc_url( $url ),
        esc_attr( $size_class ),
        $icon_markup,
        $label_markup
    );

    return $button_markup;
}
add_shortcode( SEND_WHATSAPP_SHORTCODE, 'send_whatsapp_shortcode_handler' );

/**
 * Enqueues the frontend styles for the button display modes.
 */
function send_whatsapp_enqueue_frontend_styles() {
    if ( wp_style_is( 'send-whatsapp-frontend', 'enqueued' ) ) {
        return;
    }

    wp_register_style( 'send-whatsapp-frontend', false, [], '1.0.0' );
    wp_enqueue_style( 'send-whatsapp-frontend' );

    $css = '.send-whatsapp-button{' .
        'display:inline-flex;' .
        'align-items:center;' .
        'gap:0.5em;' .
        'border-radius:999px;' .
        'background-color:#25d366;' .
        'color:#ffffff;' .
        'text-decoration:none;' .
        'font-weight:600;' .
        'transition:background-color 0.2s ease-in-out;' .
        'box-shadow:0 2px 4px rgba(0,0,0,0.1);' .
        '}' .
        '.send-whatsapp-button:hover,' .
        '.send-whatsapp-button:focus{' .
        'background-color:#1ebe5d;' .
        'color:#ffffff;' .
        '}' .
        '.send-whatsapp-button__icon{' .
        'display:inline-flex;' .
        'width:1.1em;' .
        'height:1.1em;' .
        '}' .
        '.send-whatsapp-button__icon svg{' .
        'width:100%;' .
        'height:100%;' .
        'fill:currentColor;' .
        '}' .
        '.send-whatsapp-button--small{' .
        'font-size:0.85rem;' .
        'padding:0.35em 0.9em;' .
        '}' .
        '.send-whatsapp-button--medium{' .
        'font-size:1rem;' .
        'padding:0.5em 1.2em;' .
        '}' .
        '.send-whatsapp-button--large{' .
        'font-size:1.15rem;' .
        'padding:0.65em 1.5em;' .
        '}' .
        '.send-whatsapp-button--small .send-whatsapp-button__icon{' .
        'font-size:1rem;' .
        '}' .
        '.send-whatsapp-button--medium .send-whatsapp-button__icon{' .
        'font-size:1.1rem;' .
        '}' .
        '.send-whatsapp-button--large .send-whatsapp-button__icon{' .
        'font-size:1.2rem;' .
        '}' .
        '.send-whatsapp-button__label{' .
        'line-height:1;' .
        '}';

    wp_add_inline_style( 'send-whatsapp-frontend', $css );
}

/**
 * Returns the WhatsApp SVG icon markup.
 *
 * @return string
 */
function send_whatsapp_get_icon_markup() {
    $svg = '<span class="send-whatsapp-button__icon" aria-hidden="true">'
        . '<svg viewBox="0 0 32 32" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg">'
        . '<path d="M16 3C9.383 3 4 8.383 4 15c0 2.11.56 4.072 1.535 5.78L4 29l8.38-1.492A11.836 11.836 0 0 0 16 27c6.617 0 12-5.383 12-12S22.617 3 16 3m0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10a9.82 9.82 0 0 1-3.932-.81l-.279-.12-4.97.885.92-4.816-.176-.287A9.93 9.93 0 0 1 6 15c0-5.523 4.477-10 10-10m-3.396 4.656-.307.007c-.395.018-.771.184-1.037.466-.358.38-1.039 1.015-1.039 2.41s1.064 2.792 1.213 2.988c.15.195 2.1 3.204 5.195 4.36.728.278 1.296.445 1.739.571.73.21 1.395.18 1.922.109.586-.079 1.803-.738 2.058-1.451.255-.714.255-1.326.179-1.451-.075-.124-.283-.199-.592-.348s-1.803-.89-2.082-.99c-.279-.099-.483-.148-.688.15-.205.299-.79.989-.968 1.195-.179.209-.356.223-.665.075-.31-.148-1.308-.482-2.492-1.535-.921-.82-1.542-1.833-1.721-2.142-.179-.309-.019-.476.13-.624.133-.132.31-.344.46-.516.151-.172.201-.297.302-.495.1-.198.05-.372-.025-.521-.075-.149-.667-1.607-.912-2.199-.24-.583-.483-.602-.688-.61"/></svg>'
        . '</span>';

    return $svg;
}

