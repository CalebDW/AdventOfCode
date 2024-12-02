<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day02 extends AocDay
{
    protected Bag $bag;

    public function __construct()
    {
        $this->bag = new Bag(red: 12, green: 13, blue: 14);
    }

    public function partOne(): mixed
    {
        return $this->lines
            ->filter()
            ->map(fn ($line) => $this->parseGame($line))
            ->filter(fn ($game) => $this->isGameValid($game))
            ->sum('id');
    }

    public function partTwo(): mixed
    {
        return $this->lines
            ->filter()
            ->map(fn ($line) => $this->parseGame($line))
            ->map(fn ($game) => $game->minCubeSet())
            ->sum(fn ($set) => $set->power());
    }

    public function parseGame(string $line): Game
    {
        preg_match('/Game (\d+):/', $line, $match);

        return new Game(
            id: (int) $match[1],
            sets: $this->parseSets(
                Str::of($line)->after(':')->explode(';'),
            ),
        );
    }

    /**
     * @param Collection<int, string> $sets
     * @return Collection<int, CubeSet>
     */
    public function parseSets(Collection $sets): Collection
    {
        return $sets->map(function ($set) {
            $red = $green = $blue = null;

            foreach (['red', 'green', 'blue'] as $color) {
                if (! preg_match("/(\d+) {$color}/", $set, $match)) {
                    continue;
                }

                ${$color} = (int) $match[1];
            }

            return new CubeSet($red, $green, $blue);
        });
    }

    public function isGameValid(Game $game): bool
    {
        return $game->sets
            ->every(fn ($set) => $set->red <= $this->bag->red
                && $set->green <= $this->bag->green
                && $set->blue <= $this->bag->blue
            );
    }
}

final readonly class Bag
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
    ) { }
}

final readonly class Game
{
    public function __construct(
        public int $id,
        /** @var Collection<int, CubeSet> */
        public Collection $sets,
    ) {
    }

    public function minCubeSet(): CubeSet
    {
        return new CubeSet(
            red: $this->sets->max('red'),
            green: $this->sets->max('green'),
            blue: $this->sets->max('blue'),
        );
    }
}

final readonly class CubeSet
{
    public function __construct(
        public ?int $red,
        public ?int $green,
        public ?int $blue,
    ) { }

    public function power(): int
    {
        return $this->red * $this->green * $this->blue;
    }
}
