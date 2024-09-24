<?php
// add the ajax actions
add_action('wp_ajax_asa_async_load', 'asa_async_load_callback');
add_action('wp_ajax_nopriv_asa_async_load', 'asa_async_load_callback');

/**
 * Load asynchronous
 */
function asa_async_load_callback() {
    global $asa;
    check_ajax_referer('amazonsimpleadmin', 'nonce');
    define('ASA_ASYNC_REQUEST', 1);

    $asin = sanitize_text_field($_POST['asin']);
    $tpl = $asa->getTpl(sanitize_text_field(asa_get_tpl_name_from_options($_POST)));

    $params = $_POST['params'];

    $params = json_decode(stripcslashes($params), true);
    if (is_array($params)) {
        $params = array_map('sanitize_text_field', $params);
    }
    // debug
    //echo '<pre>' . print_r($_POST) . '</pre>';

    if (isset($params['asa-block-errorlog'])) {
        $asa->getLogger()->setBlock(true);
    }

    echo wp_kses_asa( $asa->parseTpl($asin, $tpl, $params) );
    exit;
}

