<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;

class Day03 extends AocDay
{
    private string $input;

    protected function parseInput(string $input): void
    {
        $this->input = $input;
    }

    public function partOne(): ?string
    {
        preg_match_all('/mul\((\d+,\d+)\)/', $this->input, $matches);

        return (string) collect($matches[1])
            ->map(fn ($m) => array_product(explode(',', $m)))
            ->sum();
    }

    public function partTwo(): ?string
    {
        preg_match_all("/mul\(\d+,\d+\)|do\(\)|don't\(\)/", $this->input, $matches);

        $matches = array_reverse($matches[0]);

        $result = 0;
        $condition = true;

        while (key($matches) !== null) {
            $value = array_pop($matches);

            if (str_contains($value, 'do')) {
                $condition = $value === 'do()';

                continue;
            }

            $value = str_replace(['mul(', ')'], '', $value);

            if ($condition) {
                $result += array_product(explode(',', $value));
            }
        }

        return (string) $result;
    }
}
