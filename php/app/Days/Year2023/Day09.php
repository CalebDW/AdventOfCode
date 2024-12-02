<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;

class Day09 extends AocDay
{
    public function partOne(): mixed
    {
        return $this->lines
            ->filter()
            ->map(fn ($line) => $this->extrapolate(array_map('intval', explode(' ', $line))))
            ->sum();
    }

    public function partTwo(): mixed
    {
        return $this->lines
            ->filter()
            ->map(fn ($line) => $this->extrapolate(array_map('intval', explode(' ', $line)), forwards: false))
            ->sum();
    }

    /** @param array<int, int> $points */
    protected function extrapolate(array $points, bool $forwards = true): int
    {
        if (count(array_filter($points, fn ($point) => $point !== 0)) === 0) {
            return 0;
        }

        $difference = [];

        for ($i = 0; $i < count($points) - 1; $i++) {
            $difference[] = $points[$i + 1] - $points[$i];
        }

        return $forwards
            ? end($points) + $this->extrapolate($difference, $forwards)
            : $points[0] - $this->extrapolate($difference, $forwards);
    }
}
