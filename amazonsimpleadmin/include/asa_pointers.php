<?php
add_filter( 'asa_admin_pointers-settings_page_amazonsimpleadmin/amazonsimpleadmin', 'asa_register_pointer_testing' );

/**
 * @param $pointers
 * @return mixed
 */
function asa_register_pointer_testing( $pointers ) {
    if (!is_array($pointers)) {
        $pointers = array();
    }
    $pointers['whatnew_1-2-0'] = array(
        //'target' => '#amazonsimpleadmin-general',
        'target' => 'body',
        'options' => array(
            'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                __( 'What\'s new?', 'asa1'),
                str_replace(array(
                    '[img_url]'
                ), array(
                    plugins_url('', __FILE__)
                ),
                    file_get_contents(dirname(__FILE__) . '/pointers/whatsnew_1-2-0.html')
                )
            ),
            'pointerWidth' => 600,
            'position' => array( 'edge' => 'center', 'align' => 'middle' )
        )
    );

    return $pointers;
}
