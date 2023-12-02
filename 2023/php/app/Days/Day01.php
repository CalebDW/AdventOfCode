<?php

declare(strict_types=1);

namespace App\Days;

use Illuminate\Support\Collection;


class Day01 extends AocDay
{
    public function partOne(): int
    {
        return $this->concatAndSum(
            $this->lines
                ->map(fn ($line) => str_split(preg_replace('/\D/', '', $line)))
        );
    }

    public function partTwo(): int
    {
        $digits = [
            1  => 'one',
            2  => 'two',
            3  => 'three',
            4  => 'four',
            5  => 'five',
            6  => 'six',
            7  => 'seven',
            8  => 'eight',
            9  => 'nine',
        ];

        $digitsPattern = implode('|', $digits);
        $pattern = "/(?=(\d|{$digitsPattern}))/";


        return $this->concatAndSum(
            $this->lines
                ->map(function ($line) use ($pattern) {
                    preg_match_all($pattern, $line, $matches);
                    return $matches[1];
                })
                ->map(fn ($numbers) => str_replace(
                    search: array_values($digits),
                    replace: array_keys($digits),
                    subject: $numbers,
                )),
        );
    }

    /** @param Collection<int, array<int, string>> $lines */
    public function concatAndSum(Collection $lines): int
    {
        return $lines
            ->filter(fn ($numbers) => ! empty($numbers))
            ->map(fn ($numbers) => $numbers[0] . end($numbers))
            ->sum();
    }
}
