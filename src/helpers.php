<?php

if (!function_exists('is_callback_function')) {
    function is_callback_function($callback)
    {
        return is_callable($callback) && is_object($callback) && $callback instanceof Closure;
    }
}
