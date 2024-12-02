<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day12 extends AocDay
{
    /** @var Collection<int, springs> */
    protected Collection $springs;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->springs = $this->lines
            ->filter()
            ->map(function ($line) {
                $row = explode(' ', $line);

                return new Springs(
                    row: $row[0],
                    damaged: collect(explode(',', $row[1])),
                );
        });
    }

    public function partOne(): mixed
    {
        return $this->springs
            ->map(fn ($spring) => $spring->arrangements())
            ->sum();
    }

    public function partTwo(): mixed
    {
        return null;
    }

}

final class Springs
{
    public function __construct(
        public string $row,
        /** @var Collection<int, int> */
        public Collection $damaged,
    ) {
    }

    public function arrangements(): int
    {
        return preg_match_all($this->regex(), $this->row, $matches);

        dd($matches);
    }

    private function regex(): string
    {
        $groups = $this->damaged
            ->map(fn ($group) => "[#?]{{$group}}")
            ->join("[.?]+");

        return "/(?=([.?]*{$groups}[.?]*))/";
    }
}
