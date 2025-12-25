<?php

use Faker\Factory;

if (!function_exists('arabicFaker')) {
    /**
     * Create a Faker instance with Arabic locale.
     */
    function arabicFaker()
    {
        return Factory::create('ar_SA');
    }
}





