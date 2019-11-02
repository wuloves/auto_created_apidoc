<?php

Class Request
{

    public static function get($key = null, $default = null)
    {
        if (empty($key)) {
            return $_GET;
        }
        return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
    }

    public static function post($key = null, $default = null)
    {
        if (empty($key)) {
            return $_POST;
        }
        return array_key_exists($key, $_POST) ? $_POST[$key] : $default;
    }


}
