<?php
if (!function_exists('asa_collection')) {
    /**
     * displays a collection
     *
     * @param string $label
     * @param mixed $type
     * @param mixed $tpl
     */
    function asa_collection($label, $type = false, $tpl = false) {
        echo wp_kses_asa(asa_get_collection($label, $type, $tpl));
    }
}

if (!function_exists('asa_get_collection')) {
    /**
     * returns the rendered collection
     *
     * @param string $label
     * @param mixed $type
     * @param mixed $tpl
     * @return string
     */
    function asa_get_collection($label, $type = false, $tpl = false) {
        global $asa;

        $options = array();

        if (is_array($type)) {
            // use second param as options array
            $options = $type;
        } else {
            // backwards compat
            if ($type != false) {
                $options['type'] = $type;
            }
            if ($tpl != false) {
                $options['tpl'] = $tpl;
            }
        }

        return $asa->handleShortcodeAsaCollection($options, $label);
    }
}

if (!function_exists('asa_item')) {
    /**
     * displays one item, can be used everywhere in php code, eg sidebar
     *
     * @param string $asin
     * @param null|string $tpl
     * @param array $options
     */
    function asa_item($asin, $tpl = null, $options = array()) {
        echo wp_kses_asa(asa_get_item($asin, $tpl, $options));
    }
}

if (!function_exists('asa_get_item')) {
    /**
     * return the rendered product template
     *
     * @param string $asin
     * @param null|string $tpl
     * @param array $options
     * @return string
     */
    function asa_get_item($asin, $tpl = null, $options = array()) {
        global $asa;
        return $asa->getItem($asin, $tpl, $options);
    }
}

if (!function_exists('asa_get_tpl_name_from_options')) {
    /**
     * @param $options
     * @return null|string
     */
    function asa_get_tpl_name_from_options($options) {

        $tplName = null;

        if (!is_array($options)) {
            $options = array($options);
        }

        foreach ($options as $k => $v) {
            if (empty($k) && is_string($v) && !empty($v)) {
                $tplName = $v;
            }
        }

        if (!isset($tplName) && isset($options['tpl'])) {
            $tplName = trim($options['tpl']);
        }

        return $tplName;
    }
}

if (!function_exists('asa_var_to_array')) {
    /**
     * @param $var
     * @return array
     */
    function asa_var_to_array($var) {
        if (!is_array($var)) {
            if (empty($var)) {
                $var = array();
            } else {
                $var = array($var);
            }
        }
        return $var;
    }
}


if (!function_exists('esc_html_asa')) {
    /**
     * Shortcut to prevent code duplication
     *
     * @param $text
     * @return string
     */
    function esc_html_asa($text) {
        $safe_text = wp_check_invalid_utf8( $text );

        // _wp_specialchars will break the output of HTML tags and makes the esc_html function useless for escaping vars containing HTML code
        // see: https://developer.wordpress.org/reference/functions/esc_html/#div-comment-595
        //$safe_text = _wp_specialchars( $text );
        // instead the contents between the HTML tags has to be escaped, so just don't check for "echo $somevar" but what is inside $somevar

        return $safe_text;
    }
}

if (!function_exists('wp_kses_asa')) {
    /**
     * Shortcut to prevent code duplication
     *
     * @param $string
     * @param array $allowed_html
     * @param array $allowed_protocols
     * @return string
     */
    function wp_kses_asa($string, $allowed_html = [], $allowed_protocols = []) {

//        $allowed_html = array_merge([
//            '<div>', '<p>', '<span>', '<a>', 'style'
//        ], $allowed_html);
//        return wp_kses_post($string, $allowed_html, $allowed_protocols);
//        return wp_kses($string, $allowed_html, $allowed_protocols);

        return $string;
    }
}

add_action( 'admin_enqueue_scripts', 'asa_pointer_load', 1000 );

function asa_pointer_load( $hook_suffix ) {

    // Don't run on WP < 3.3
    if ( get_bloginfo( 'version' ) < '3.3' )
        return;

    $screen = get_current_screen();
    $screen_id = $screen->id;

    // Get pointers for this screen
    $pointers = apply_filters( 'asa_admin_pointers-' . $screen_id, array() );

    if ( ! $pointers || ! is_array( $pointers ) )
        return;

    // Get dismissed pointers
    $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
    $valid_pointers = array();

    // Check pointers and remove dismissed ones.
    foreach ( $pointers as $pointer_id => $pointer ) {

        // Sanity check
        if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
            continue;

        $pointer['pointer_id'] = $pointer_id;

        // Add the pointer to $valid_pointers array
        $valid_pointers['pointers'][] =  $pointer;
    }

    // No valid pointers? Stop here.
    if ( empty( $valid_pointers ) )
        return;

    // Add pointers style to queue.
    wp_enqueue_style( 'wp-pointer' );

    // Add pointers script to queue. Add custom script.
    wp_enqueue_script( 'asa-pointer', plugins_url( '../js/pointers.js', __FILE__ ), array( 'wp-pointer' ) );

    // Add pointer options to script.
    wp_localize_script( 'asa-pointer', 'asaPointer', $valid_pointers );
}