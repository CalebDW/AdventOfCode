<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day02 extends AocDay
{
    /** @var Collection<int, list<int>> */
    private Collection $levelDiffs;

    public function partOne(): ?string
    {
        return (string) $this->parseReports()
            ->filter(fn ($d) => $this->isSafeReport($d))
            ->count();
    }

    public function partTwo(): ?string
    {
        return (string) $this->parseReports()
            ->reject(function ($diff) {
                if ($this->isSafeReport($diff)) {
                    return false;
                }

                $chances = 1;

                foreach (range(0, count($diff) - 1) as $index) {
                    $newDiff = array_splice($diff, $index, 1);

                    if ($this->isSafeReport($diff)) {
                        return false;
                    }

                    if (--$chances) {
                        return true;
                    }
                }
            })
            ->count();
    }

    /** @param list<int> $levelDiff */
    private function isSafeReport(array $levelDiff): bool
    {
        $min = min($levelDiff);
        $max = max($levelDiff);

        return ! ($min < 0 && $max > 0)
            && (abs($min) >= 1 && abs($min) <= 3)
            && (abs($max) >= 1 && abs($max) <= 3);
    }

    /** @return Collection<int, list<int>> */
    private function parseReports(): Collection
    {
        return $this->levelDiffs ??= $this->lines
            ->filter()
            ->map(fn ($line) => array_map('intval', explode(' ', $line)))
            ->map(fn ($l) => array_map(
                fn ($c, $p) => $c - $p,
                array_slice($l, 1),
                array_slice($l, 0, -1),
            ));
    }
}
