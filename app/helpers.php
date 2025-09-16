<?php

if (! function_exists('pythonApi')) {
    function pythonApi($path = '') {
        $base = config('services.python_api.url');
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
