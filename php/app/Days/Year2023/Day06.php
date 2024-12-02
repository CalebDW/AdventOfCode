<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day06 extends AocDay
{
    /** @var Collection<int, Race> */
    protected Collection $races;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $times = Str::of($this->lines[0])->after(':')->squish()->explode(' ');
        $distances = Str::of($this->lines[1])->after(':')->squish()->explode(' ');

        $this->races = $times->zip($distances)
            ->map(fn ($race) => new Race((int) $race[0], (int) $race[1]));
    }

    public function partOne(): mixed
    {
        return $this->races
            ->map(fn ($race) => $race->timeToBeatRecord())
            ->map(fn ($times) => $times[1] - $times[0] + 1)
            ->reduce(fn ($carry, $race) => $carry * $race, 1);
    }

    public function partTwo(): mixed
    {
        $this->races = collect([new Race(
            (int) Str::of($this->lines[0])->after(':')->remove(' ')->toString(),
            (int) Str::of($this->lines[1])->after(':')->remove(' ')->toString(),
        )]);

        return $this->partOne();
    }
}

final readonly class Race
{
    public const ACCELERATION = 1; // mm / ms^2

    public function __construct(
        public int $maxTime,
        public int $recordDistance,
    ) { }

    public function distance(int $timeHeld): int
    {
        return $timeHeld*self::ACCELERATION*($this->maxTime - $timeHeld);
    }

    /** @return array{int, int} */
    public function timeForDistance(int $distance): array
    {
        $a = self::ACCELERATION;

        $sqrt = sqrt(($a*$this->maxTime)**2 - 4*$a*$distance);

        return [
            (int) ceil(($a*$this->maxTime - $sqrt) / (2*$a)),
            (int) floor(($a*$this->maxTime + $sqrt) / (2*$a)),
        ];
    }

    /** @return array{int, int} */
    public function timeToBeatRecord(): array
    {
        return $this->timeForDistance($this->recordDistance + 1);
    }
}
