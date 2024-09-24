<?php
if (!function_exists('asa_plugins_url')) {
    /**
     * @param string $path
     * @param string $plugin
     * @return string
     */
    function asa_plugins_url($path = '', $plugin = '')
    {
        if (getenv('ASA_APPLICATION_ENV') == 'development') {
            return get_bloginfo('wpurl') . '/wp-content/plugins/amazonsimpleadmin/' . $path;
        }
        return plugins_url($path, $plugin);
    }
}

if (!function_exists('is_asa_admin_page')) {
    /**
     * @return bool
     */
    function is_asa_admin_page()
    {
        return is_admin() && isset($_GET['page']) && $_GET['page'] == 'amazonsimpleadmin/amazonsimpleadmin.php';
    }
}

if (!function_exists('asa_get_feed_items')) {
    /**
     * Retrieves the ASA news feed items
     * @return string
     */
    function asa_get_feed_items()
    {
        $rss = fetch_feed('http://www.wp-amazon-plugin.com/feed/');
        $result = array();

        if ($rss instanceof SimplePie) {

            foreach ($rss->get_items(0, 3) as $item) {

                $item_tmp = array();

                $item_tmp['url'] = esc_url_raw($item->get_link());

                $title = sanitize_text_field($item->get_title());

                if (empty($title)) {
                    $title = __('Untitled');
                }
                $item_tmp['title'] = $title;

                $desc = str_replace(array("\n", "\r"), ' ', sanitize_post($item->get_description()));
                $desc = wp_html_excerpt($desc, 360);

                if (strstr($desc, 'Continue reading →')) {
                    $desc = str_replace('Continue reading →', '<a href="' . $item_tmp['url'] . '" target="_blank">Continue reading →</a>', $desc);
                }
                $item_tmp['desc'] = $desc;

                $date = $item->get_date();
                $diff = '';

                if ($date) {

                    $diff = human_time_diff(strtotime($date, time()));
                    $date_stamp = strtotime($date);
                    if ($date_stamp) {
                        $date = '<span class="rss-date">' . date_i18n(get_option('date_format'), $date_stamp) . '</span>';
                    } else {
                        $date = '';
                    }
                }

                $item_tmp['date'] = $date;
                $item_tmp['diff'] = $diff;

                $result[] = $item_tmp;
            }
        }

        $output = '';
        if (count($result) > 0) {
            $output .= '<div id="">';
            //$output .= '<h3>' . __('ASA News', 'asa1') . '</h3>';
            $output .= '<ul>';
            foreach ($result as $item) {
                $output .= '<li>';
                $date = !empty($item['data']) ? $item['data'] : '';
                $output .= sprintf('<a class="rsswidget" title="" href="%s" target="_blank">%s</a><span class="rss-date">%s</span>',
                    $item['url'], $item['title'], $date
                );
                $output .= sprintf('<div class="rssSummary"><strong>%s</strong> - %s</div>',
                    $item['diff'], $item['desc']
                );
                $output .= '</li>';
            }
            $output .= '</ul>';
        }
        $output .= '</div>';

        return $output;
    }
}

if (!function_exists('asa_debug')) {
    /**
     * @param $var
     * @param bool $backtrace
     * @param bool $verbose
     * @return void
     */
    function asa_debug($var, $backtrace = false, $verbose = true)
    {
        if (WP_DEBUG === true) {

            $bt = debug_backtrace();
            $pathinfo = pathinfo($bt[0]['file']);

            $output = '';
            if ($verbose) {
                $output .= date('Y-m-d H:i:s') . ' ' . __FUNCTION__ . ' in ';
                $output .= $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['basename'] . ':' . $bt[0]['line'] . ':' .
                    ' ('. gettype($var) . ') ';
            }

            if (is_array($var) || is_object($var)) {
                $output .= print_r($var, true);
            } elseif (is_bool($var)) {
                $output .= var_export($var, true);
            } else {
                $output .= $var;
            }
            //error_log($output);
            $output .= PHP_EOL;

            file_put_contents(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'debug.log', $output, FILE_APPEND);

            if ($backtrace) {
                $backtrace = array_reverse(debug_backtrace());

                $backtrace_output = '';

                $counter = 0;

                foreach ($backtrace as $row) {
                    if ((count($backtrace)-1) == $counter) {
                        break;
                    }

                    $file = (isset($row['file'])) ? $row['file'] : '';
                    $line = (isset($row['line'])) ? $row['line'] : '';
                    $class = (isset($row['class'])) ? $row['class'] : '';
                    $function = (isset($row['function'])) ? $row['function'] : '';

                    $backtrace_output .= $counter .': '. $file .':'. $line .
                        ', class: '. $class .', function: '. $function . PHP_EOL;
                    $counter++;
                }
                error_log(__FUNCTION__ . ' backtrace:' . PHP_EOL . $backtrace_output);
            }
        }
    }
}

if (!function_exists('asa_compress_css')) {
    /**
     * @param $css
     * @return mixed
     */
    function asa_compress_css($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove space after colons
        $css = str_replace(': ', ':', $css);
        // Remove whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

        return $css;
    }
}

if (!function_exists('asa_compress_html')) {
    /**
     * @param $html
     * @return mixed
     */
    function asa_compress_html($html) {
        // Remove whitespace
        $html = asa_remove_comments($html);
        $html = asa_strip_script($html);

        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $html = preg_replace($search, $replace, $html);
        $html = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $html);
        return $html;
    }
}

if (!function_exists('asa_remove_comments')) {
    /**
     * @param $html
     * @return mixed
     */
    function asa_remove_comments($html) {
        return preg_replace('/<!--(.|\s)*?-->/', '', $html);
    }
}

if (!function_exists('asa_strip_script')) {
    /**
     * @param $html
     * @return mixed
     */
    function asa_strip_script($html) {
        do {
            if (isset($result)) {
                $html = $result;
            }
            $result = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
        } while ($result != $html);

        return $result;
    }
}

if (!function_exists('asa_sanitize_shortcode_option_value')) {

    /**
     * @param $value
     * @return mixed
     */
    function asa_sanitize_shortcode_option_value($value) {
        $value = str_replace(
            array(','),
            array(''),
            $value
        );

        $value = trim($value);

        return $value;
    }
}

if (!function_exists('asa_is_pa_api_5')) {
    /**
     * @return bool
     */
    function asa_is_pa_api_5() {
        return true;
        //return (int)get_option('_asa_pa_api_version') === AmazonSimpleAdmin::PA_API_5;
    }
}