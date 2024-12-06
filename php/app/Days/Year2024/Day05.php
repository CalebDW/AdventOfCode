<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day05 extends AocDay
{
    /** @var array<int, list<int>> */
    private array $orderings;

    /** @var Collection<int, int> */
    private Collection $pages;

    /** @var list<bool> */
    private array $changed = [];

    protected function parseInput(string $input): void
    {
        [$orderings, $updates] = explode("\n\n", $input);

        $this->orderings = collect(explode("\n", $orderings))
            ->filter()
            ->map(fn ($line) => array_map('intval', explode('|', $line)))
            ->mapToGroups(fn ($line) => [$line[0] => $line[1]])
            ->toArray();

        $this->pages = collect(explode("\n", $updates))
            ->filter()
            ->map(fn ($line) => array_map('intval', explode(',', $line)))
            ->map(fn ($update, $index) => $this->ensureCorrectOrdering($update, $index))
            ->map(fn ($update) => $update[(int) floor(count($update) / 2)]);
    }

    public function partOne(): ?string
    {
        return (string) $this->pages
            ->reject(fn ($_, $index) => $this->changed[$index])
            ->sum();
    }

    public function partTwo(): ?string
    {
        return (string) $this->pages
            ->filter(fn ($_, $index) => $this->changed[$index])
            ->sum();
    }

    /**
     * @param list<int> $update
     * @return list<int>
     */
    private function ensureCorrectOrdering(array $update, int $index): array
    {
        $sorted = [];
        $graph = [];

        foreach ($update as $page) {
            $graph[$page] = array_intersect($this->orderings[$page] ?? [], $update);
        }

        while (count($graph) > 0) {
            foreach ($graph as $page => $edges) {
                $graph[$page] = array_diff($edges, $sorted);

                if (count($graph[$page]) === 0) {
                    unset($graph[$page]);
                    $sorted[] = $page;
                }
            }
        }

        $sorted = array_reverse($sorted, false);

        $this->changed[$index] = $update !== $sorted;

        return $sorted;
    }
}
