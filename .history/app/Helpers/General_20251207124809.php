<?php

use Faker\Factory;

if (!function_exists('arabicFaker')) {
    function arabicFaker()
    {
        return Factory::create('ar_SA');
    }
}