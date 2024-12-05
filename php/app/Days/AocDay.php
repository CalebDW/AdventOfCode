<?php

declare(strict_types=1);

namespace App\Days;

use App\Concerns\ProfilesClosures;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** @phpstan-import-type ProfiledResult from ProfilesClosures */
abstract class AocDay
{
    use ProfilesClosures;

    /** @var Collection<int, string> */
    protected Collection $lines;

    final public function __construct()
    {
    }

    /** @return array{0: ?ProfiledResult, 1: ?ProfiledResult} */
    final public function __invoke(string $input, ?int $part = null): array
    {
        $this->parseInput($input);

        $parts = [null, null];

        if ($part !== 2) {
            $parts[0] = $this->profile(fn () => $this->partOne($input));
        }

        if ($part !== 1) {
            $parts[1] = $this->profile(fn () => $this->partTwo($input));
        }

        return $parts;
    }

    abstract protected function partOne(): ?string;
    abstract protected function partTwo(): ?string;

    public function label(): string
    {
        return Str::of(class_basename($this))
            ->replace('Day', 'Day ')
            ->value();
    }

    protected function parseInput(string $input): void
    {
        $this->lines = collect(explode("\n", $input));
    }
}
