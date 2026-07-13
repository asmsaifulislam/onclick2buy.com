<?php
if (!function_exists('currency')) {
    function currency($amount, $decimals = 2)
    {
        $symbol = config('app.currency_symbol', '৳');
        return $symbol . number_format($amount, $decimals);
    }
}
if (!function_exists('currency_symbol')) {
    function currency_symbol()
    {
        return config('app.currency_symbol', '৳');
    }
}
if (!function_exists('currency_code')) {
    function currency_code()
    {
        return config('app.currency', 'BDT');
    }
}