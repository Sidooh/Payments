<?php

use JetBrains\PhpStorm\NoReturn;

if(!function_exists('ddj')) {
    #[NoReturn]
    function ddj(...$vars)
    {
        echo "<pre>";
        print_r($vars);
        die;
    }
}

if(!function_exists('base_64_url_encode')) {
    function base_64_url_encode($text): array|string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
}
