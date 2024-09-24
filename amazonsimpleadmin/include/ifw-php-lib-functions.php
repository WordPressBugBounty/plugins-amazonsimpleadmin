<?php
if (!function_exists('ifw_var_to_array')) {
    /**
     * @param $var
     * @param null $callback
     * @return array
     */
    function ifw_var_to_array($var, $callback = null) {
        if (!is_array($var)) {
            if (empty($var)) {
                $var = array();
            } else {
                $var = array($var);
            }
        }
        if (is_callable($callback)) {
            $var = array_map($callback, $var);
        }
        return $var;
    }
}

if (!function_exists('ifw_filter_scalar')) {
    /**
     * @param $scalar
     * @param array $valid
     * @param null $default
     * @param bool $strict
     * @return mixed|null
     */
    function ifw_filter_scalar($scalar, array $valid, $default = null, $strict = true) {
        $result = $default;
        if (in_array($scalar, $valid, $strict)) {
            $result = $scalar;
        }
        return $result;
    }
}

if (!function_exists('ifw_array_get_col')) {
    /**
     * collects a column/index of an array
     * @param  array $data  the soure array
     * @param  string $col  the column/index to collect
     * @param  string $type the variable type, leave blank for original type
     * @return array
     */
    function ifw_array_get_col(array $data, $col, $type = null) {

        $result = array();
        foreach ($data as $row) {
            if (array_key_exists($col, $row)) {
                $value = $row[$col];
                if ($type !== null) {
                    $value = settype($value, $type);
                }
                array_push($result, $value);
            }
        }
        return $result;
    }
}
