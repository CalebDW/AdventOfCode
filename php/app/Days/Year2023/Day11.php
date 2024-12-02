<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day11 extends AocDay
{
    /** @var Collection<int, array<int, string>> */
    protected Collection $image;

    /** @var Collection<int, Galaxy> */
    protected Collection $galaxies;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->image = $this->lines
            ->filter();

        $this->galaxies = collect();

        $this->parseGalaxies();
    }

    public function partOne(): mixed
    {
        $sum = 0;

        for ($i = 0; $i < $this->galaxies->count() - 1; $i++) {
            $galaxy = $this->galaxies[$i];

            $sum += $this->galaxies->slice($i + 1)
                ->reduce(fn ($c, $g) => $c + $galaxy->distance($g));
        }

        return $sum;
    }

    public function partTwo(): mixed
    {
        $this->galaxies = collect();

        $this->parseGalaxies(1000000);

        return $this->partOne();
    }

    protected function parseGalaxies(int $expansionRate = 2): void
    {
        $yMod = 0;

        foreach ($this->image as $y => $row) {
            if (! str_contains($row, '#')) {
                $yMod += ($expansionRate - 1);
                continue;
            }

            $xMod = 0;

            for ($x = 0; $x < strlen($row); $x++) {
                $char = $row[$x];

                $isColumnEmpty = $this->image
                    ->every(fn ($row) => $row[$x] === '.');

                if ($isColumnEmpty) {
                    $xMod += ($expansionRate - 1);
                    continue;
                }

                if ($char !== '#') {
                    continue;
                }

                $this->galaxies->push(new Galaxy(
                    coords: ['x' => $x + $xMod, 'y' => $y + $yMod],
                ));
            }
        }
    }
}

final class Galaxy
{
    public function __construct(
        /** @var array{x: int, y: int} */
        public array $coords,
    ) {
    }

    public function distance(Galaxy $other): int
    {
        return abs($this->coords['x'] - $other->coords['x'])
            + abs($this->coords['y'] - $other->coords['y']);
    }
}
