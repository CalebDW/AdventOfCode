<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day03 extends AocDay
{
    /** @var Collection<int, Number> */
    protected Collection $partNumbers;

    /** @var Collection<string, array<int, Number>> */
    protected Collection $gears;

    public function __construct()
    {
        $this->gears = collect();
    }

    public function partOne(): mixed
    {
        $this->partNumbers = $this->lines
            ->map(function ($line) {
                preg_match_all('/\d+/', $line, $matches, PREG_OFFSET_CAPTURE);

                return $matches[0];
            })
            ->filter()
            ->map(fn ($numbers, $y) => array_map(
                fn ($number) => new Number((int) $number[0], [
                    'x' => $number[1],
                    'y' => $y,
                ]),
                $numbers,
            ))
            ->flatten()
            ->filter(fn ($number) => $this->isPartNumber($number));

        return $this->partNumbers->sum('value');
    }

    public function partTwo(): mixed
    {
        return $this->gears
            ->groupBy('gear')
            ->reject(fn ($gear) => $gear->count() !== 2)
            ->sum(
                fn ($gear) => $gear->first()['number']->value
                    * $gear->last()['number']->value,
            );
    }

    public function isPartNumber(Number $number): bool
    {
        foreach ([-1, 0, 1] as $dy) {
            $y = $number->coords['y'] + $dy;

            if ($y < 0 || $y >= $this->lines->count()) {
                continue;
            }

            $line = $this->lines[$y];

            $x = max($number->coords['x'] - 1, 0);

            $substring = substr(
                string: $line,
                offset: $x,
                length: $number->length() + 2,
            );

            if (! preg_match_all('/[^\d.]/', $substring, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $this->buildGearList($number, $matches[0], $x, $y);

            return true;
        }

        return false;
    }

    /** @param array<int, array<int, int|string>> $matches */
    public function buildGearList(
        Number $number,
        array $matches,
        int $x,
        int $y,
    ): void {
        foreach ($matches as $match) {
            if ($match[0] !== '*') {
                continue;
            }

            $gearX = $match[1] + $x;

            $this->gears->push(['gear' => "{$gearX},{$y}", 'number' => $number]);
        }
    }
}

final readonly class Number
{
    public function __construct(
        public int $value,
        /** @var array{x: int, y: int} */
        public array $coords,
    ) {
    }

    public function length(): int
    {
        return (int) ceil(log10($this->value + 1));
    }
}
