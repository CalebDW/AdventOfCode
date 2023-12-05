<?php

declare(strict_types=1);

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
        $this->parseInput($input);

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
        return Str::of(class_basename($this))
            ->replace('Day', 'Day ')
            ->toString();
    }

    protected function parseInput(string $input): void
    {
        $this->lines = collect(explode("\n", $input));
    }

    abstract public function partOne(): mixed;
    abstract public function partTwo(): mixed;
}
