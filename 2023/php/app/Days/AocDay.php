<?php

namespace App\Days;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AocDay
{
    /** @var Collection<int, string> */
    protected Collection $lines;

    /** @return array{mixed, mixed} */
    public function __invoke(string $input, ?int $part = null): array
    {
        $this->lines = collect($this->parseInput($input));

        $parts = [null, null];

        if ($part !== 2) {
            $parts[0] = $this->partOne();
        }

        if ($part !== 1) {
            $parts[1] = $this->partTwo();
        }


        return $parts;
    }

    public function label(): string
    {
        $day = (int) Str::afterLast(
            Str::headline(class_basename($this)),
            'Day',
        );

        return "Day {$day}";
    }

    /** @return array<int, string> */
    protected function parseInput(string $input): array
    {
        return explode("\n", $input);
    }

    abstract public function partOne(): mixed;
    abstract public function partTwo(): mixed;
}
