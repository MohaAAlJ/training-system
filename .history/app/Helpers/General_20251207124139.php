<?php

use Faker\Factory;

if (!function_exists('arabicFaker')) {
    /**
     * Get an Arabic Faker instance.
     */
    function arabicFaker()
    {
        // ar_SA provides realistic Arabic names and text
        return Factory::create('ar_SA');
    }
}