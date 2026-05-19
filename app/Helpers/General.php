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

if (!function_exists('validatePalestinianId')) {
    /**
     * Validate Palestinian National ID using checksum algorithm
     * 
     * Algorithm:
     * 1. ID must be exactly 9 digits
     * 2. Apply weights (1,2,1,2,1,2,1,2,1) to each digit
     * 3. Multiply digit × weight
     * 4. If product > 9, subtract 9
     * 5. Sum all results
     * 6. Valid if sum % 10 === 0
     * 
     * @param string|null $id The Palestinian National ID to validate
     * @return bool
     * 
     * @example
     * validatePalestinianId('410010284') // Returns true
     * validatePalestinianId('123456789') // Returns false
     */
    function validatePalestinianId(?string $id): bool
    {
        // Handle null or empty input
        if (empty($id)) {
            return false;
        }

        // Remove any whitespace
        $id = trim($id);

        // Check if exactly 9 digits
        if (!preg_match('/^\d{9}$/', $id)) {
            return false;
        }

        // Reject nonsense common numbers like 9 repeating digits, 8 repeating digits, or sequential ones.
        if (
            preg_match('/^(\d)\1{8}$/', $id) || // e.g. 000000000
            preg_match('/^(\d)\1{7}/', $id) ||  // e.g. 111111118
            preg_match('/^(01234567|12345678)/', $id) ||
            preg_match('/^(98765432|87654321)/', $id)
        ) {
            return false;
        }

        // Weights for each position (1-indexed)
        $weights = [1, 2, 1, 2, 1, 2, 1, 2, 1];

        $sum = 0;

        // Process each digit
        for ($i = 0; $i < 9; $i++) {
            $digit = (int)$id[$i];
            $weight = $weights[$i];
            $product = $digit * $weight;

            // If product > 9, subtract 9 (equivalent to sum of digits)
            if ($product > 9) {
                $product -= 9;
            }

            $sum += $product;
        }

        // Valid if sum is divisible by 10
        return ($sum % 10 === 0);
    }
}

if (!function_exists('toEnglishNumbers')) {
    /**
     * Convert Arabic numerals to English numerals.
     */
    function toEnglishNumbers($value): string
    {
        return str_replace(
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            (string) $value
        );
    }
}
