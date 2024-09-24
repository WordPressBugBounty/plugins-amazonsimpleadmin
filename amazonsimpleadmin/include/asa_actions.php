<?php
function asa_enqueue_scripts () {
    wp_enqueue_style('asa-admin', asa_plugins_url('css/admin.css', ASA_BASE_FILE), array(), AmazonSimpleAdmin::VERSION);
    wp_enqueue_script('asa-admin', asa_plugins_url('js/admin.js', ASA_BASE_FILE), array('jquery'), AmazonSimpleAdmin::VERSION);
}

if (is_asa_admin_page()) {
    add_action('admin_enqueue_scripts', 'asa_enqueue_scripts');
}

if (is_asa_admin_page() && !isset($_GET['task'])) {
    add_action( 'admin_footer', 'asa_load_feed_news' );

    function asa_load_feed_news() { ?>
        <script type="text/javascript" >
            jQuery(document).ready(function($) {
                var data = {
                    'action': 'asa_load_feed_news'
                };
                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#asa_feed_box').html(response);
                });

                var infoBoxSetup = $('.asa_info_box_setup');
                if (infoBoxSetup.length > 0 && navigator.onLine) {
                    $.post(ajaxurl, {
                        action: 'asa_load_setup_banner'
                    }, function(data) {
                        if (typeof data != 'undefined' && data != '') {
                            infoBoxSetup.html(data);
                        }
                    });
                }
            });
        </script> <?php
    }
}

add_action( 'wp_ajax_asa_load_feed_news', 'asa_load_feed_news_callback' );

function asa_load_feed_news_callback() {
    echo wp_kses_post( asa_get_feed_items() );
    wp_die();
}

add_action( 'wp_ajax_asa_load_setup_banner', 'asa_load_setup_banner_callback' );

function asa_load_setup_banner_callback() {
    $response = wp_remote_get('http://io.wp-amazon-plugin.com/asa1banner/setup-banner.htm');
    if (is_array($response) && isset($response['response']['code']) && (int)$response['response']['code'] == 200 &&
        isset($response['body'])) {
        echo wp_kses_post( $response['body'] );
    }
    wp_die();
}

require_once ASA_LIB_DIR . 'Asa/Prefetcher.php';
Asa_Prefetcher::getInstance()->init();