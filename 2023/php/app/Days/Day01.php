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
                ->map(fn ($l) => str_split(preg_replace('/\D/', '', $l)))
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
                ->map(function ($l) use ($pattern) {
                    preg_match_all($pattern, $l, $matches);
                    return $matches[1];
                })
                ->map(fn ($l) => str_replace(
                    search: array_values($digits),
                    replace: array_keys($digits),
                    subject: $l,
                )),
        );
    }

    /** @param Collection<int, array<int, string>> $lines */
    public function concatAndSum(Collection $lines): int
    {
        return $lines
            ->filter(fn ($m) => ! empty($m))
            ->map(fn ($l) => $l[0] . end($l))
            ->sum();
    }
}
