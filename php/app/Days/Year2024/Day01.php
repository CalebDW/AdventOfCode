<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day01 extends AocDay
{
    /** @var Collection<int, list<int>> */
    private Collection $lists;

    public function partOne(): int
    {
        return $this->parseLists()
            ->pipe(fn ($lists) => collect($lists[0])->zip($lists[1]))
            ->reduce(fn ($c, $pair) => $c + abs($pair[1] - $pair[0]), 0);
    }

    public function partTwo(): int
    {
        $left = $right = [];

        return $this->parseLists()
            ->pipe(function ($lists) use (&$left, &$right) {
                $left = array_count_values($lists[0]);
                $right = array_count_values($lists[1]);

                return collect($lists[0]);
            })
            ->reduce(fn ($c, $v) => $c + $v * $left[$v] * ($right[$v] ?? 0), 0);
    }

    /** @return Collection<int, list<int>> */
    private function parseLists(): Collection
    {
        return $this->lists ??= $this->lines
            ->filter()
            ->map(fn ($line) => array_map('intval', preg_split('/\s+/', $line)))
            ->pipe(function ($pairs) {
                $pairs = $pairs->all();
                $first = array_column($pairs, 0);
                $second = array_column($pairs, 1);
                sort($first);
                sort($second);

                return collect([$first, $second]);
            });
    }
}
