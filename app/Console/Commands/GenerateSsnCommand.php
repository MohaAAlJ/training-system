<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSsnCommand extends Command
{
    protected $signature = 'generate:ssn {count=1 : Number of SSNs to generate}';

    protected $description = 'Generate valid mock Palestinian National ID(s) for testing';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        for ($i = 0; $i < $count; $i++) {
            $this->line($this->generateSsn());
        }

        return self::SUCCESS;
    }

    /**
     * Generates a valid mock Palestinian National ID.
     * Ensures it passes the checksum algorithm and avoids nonsense patterns.
     */
    private function generateSsn(): string
    {
        do {
            // 1. Generate the first 8 random digits
            $idBase = '';
            for ($i = 0; $i < 8; $i++) {
                $idBase .= mt_rand(0, 9);
            }

            // 2. Make sure the base doesn't violate nonsense-pattern rules
        } while (
            preg_match('/^(\d)\1{7}/', $idBase) ||
            preg_match('/^(01234567|12345678)/', $idBase) ||
            preg_match('/^(98765432|87654321)/', $idBase)
        );

        // 3. Calculate the checksum for the first 8 digits
        $weights = [1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $product = (int) $idBase[$i] * $weights[$i];
            if ($product > 9) {
                $product -= 9;
            }
            $sum += $product;
        }

        // 4. Determine the 9th check digit
        $checkDigit = (10 - ($sum % 10)) % 10;

        // 5. Return the full 9-digit ID
        return $idBase . $checkDigit;
    }
}
