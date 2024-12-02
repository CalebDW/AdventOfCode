<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;

class Day10 extends AocDay
{
    /** @var Collection<int, array<int, Tile>> */
    protected Collection $tiles;
    protected int $tileXCount;
    protected int $tileYCount;

    protected Tile $squirrel;

    /** @var array<int, Tile> */
    protected array $path = [];

    /** @var array<int, array<int, bool>> */
    protected array $seen = [];

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->tiles = $this->lines
            ->filter()
            ->map(fn ($line, $y) => $this->parseTiles($line, $y))
            ->each(fn ($row) => $this->seen[] = array_fill(0, count($row), false));

        $this->tileYCount = $this->tiles->count();
        $this->tileXCount = $this->tiles->first()->count();
    }

    public function partOne(): mixed
    {
        foreach (Direction::cases() as $dir) {
            if ($this->walk($this->squirrel, $dir)) {
                break;
            }
        }

        return round(count($this->path) / 2);
    }

    public function partTwo(): mixed
    {
        if (count($this->path) === 0) {
            $this->partOne();
        }

        $area = $this->calculateEnclosedArea();

        // subtract out unit area calculated along pipes next to each other
        $pathCount = count($this->path);

        $segments = ($pathCount - 1) / 2;

        return $area - $segments;
    }

    /** @return Collection<int, Tile> */
    protected function parseTiles(string $tiles, int $y): Collection
    {
        return collect(str_split($tiles))
            ->map(function ($type, $i) use ($y) {
                $tile = new Tile(
                    type: TileType::from($type),
                    coords: ['x' => $i, 'y' => $y],
                );

                if ($tile->type === TileType::Squirrel) {
                    $this->squirrel = $tile;
                }

                return $tile;
            });
    }

    protected function walk(Tile $current, Direction $dir): bool
    {
        $x = $current->coords['x'] + $dir->dx();
        $y = $current->coords['y'] + $dir->dy();

        if (
            $x < 0 || $x >= $this->tileXCount
            || $y < 0 || $y >= $this->tileYCount
        ) {
            return false;
        }

        $next = $this->tiles[$y][$x];

        if (! $current->type->isValidPath($next->type, $dir)
        ) {
            return false;
        }

        if (
            $next->type === TileType::Squirrel
            && count($this->path) > 1
        ) {
            $this->path[] = $next;
            return true;
        }

        if ($this->seen[$y][$x]) {
            return false;
        }

        $this->path[] = $current;
        $this->seen[$current->coords['y']][$current->coords['x']] = true;

        foreach (Direction::cases() as $dir) {
            if ($this->walk($next, $dir)) {
                return true;
            }
        }

        array_pop($this->path);

        return false;
    }

    /** Green's Theorem */
    protected function calculateEnclosedArea(): float
    {
        $integral = collect($this->path)
            ->sliding(size: 2)
            ->map(function ($window) {
                $current = $window->first();
                $next = $window->last();

                $dx = $next->coords['x'] - $current->coords['x'];
                $dy = $next->coords['y'] - $current->coords['y'];

                return $next->coords['x'] * $dy
                    - $next->coords['y'] * $dx;
            })
            ->sum();

         return .5 * abs($integral);
    }
}

final class Tile
{
    public function __construct(
        public TileType $type,
        /** @var array{x: int, y: int} */
        public array $coords,
    ) {
    }
}

enum TileType: string
{
    case Vertical = '|';
    case Horizontal = '-';
    case BendNE = 'L';
    case BendNW = 'J';
    case BendSW = '7';
    case BendSE = 'F';
    case Ground = '.';
    case Squirrel = 'S';

    public function isValidPath(TileType $next, Direction $dir): bool
    {
        return match ($this) {
            self::Vertical => match ($next) {
                self::Vertical, self::Squirrel => in_array($dir, [Direction::North, Direction::South]),
                self::BendNE, self::BendNW => $dir === Direction::South,
                self::BendSE, self::BendSW => $dir === Direction::North,
                default => false,
            },
            self::Horizontal => match ($next) {
                self::Horizontal, self::Squirrel => in_array($dir, [Direction::East, Direction::West]),
                self::BendNE, self::BendSE => $dir === Direction::West,
                self::BendNW, self::BendSW => $dir === Direction::East,
                default => false,
            },
            self::BendNE => match ($next) {
                self::Vertical, self::BendSE => $dir === Direction::North,
                self::Horizontal, self::BendNW => $dir === Direction::East,
                self::BendSW, self::Squirrel => in_array($dir, [Direction::North, Direction::East]),
                default => false,
            },
            self::BendNW => match ($next) {
                self::Vertical, self::BendSW => $dir === Direction::North,
                self::Horizontal, self::BendNE => $dir === Direction::West,
                self::BendSE, self::Squirrel => in_array($dir, [Direction::North, Direction::West]),
                default => false,
            },
            self::BendSE => match ($next) {
                self::Vertical, self::BendNE => $dir === Direction::South,
                self::Horizontal, self::BendSW => $dir === Direction::East,
                self::BendNW, self::Squirrel => in_array($dir, [Direction::South, Direction::East]),
                default => false,
            },
            self::BendSW => match ($next) {
                self::Vertical, self::BendNW => $dir === Direction::South,
                self::Horizontal, self::BendSE => $dir === Direction::West,
                self::BendNE, self::Squirrel => in_array($dir, [Direction::South, Direction::West]),
                default => false,
            },
            self::Squirrel => match ($next) {
                self::Vertical => in_array($dir, [Direction::North, Direction::South]),
                self::Horizontal => in_array($dir, [Direction::East, Direction::West]),
                self::BendNE => in_array($dir, [Direction::South, Direction::West]),
                self::BendNW => in_array($dir, [Direction::South, Direction::East]),
                self::BendSE => in_array($dir, [Direction::North, Direction::West]),
                self::BendSW => in_array($dir, [Direction::North, Direction::East]),
                default => false,
            },
            default => false,
        };
    }
}

enum Direction
{
    case North;
    case East;
    case South;
    case West;

    /** Assumes (0, 0) is upper-left corner and east is positive. */
    public function dx(): int
    {
        return match ($this) {
            self::West => -1,
            self::North, self::South => 0,
            self::East => 1,
        };
    }

    /** Assumes (0, 0) is upper-left corner and south is positive. */
    public function dy(): int
    {
        return match ($this) {
            self::North => -1,
            self::East, self::West => 0,
            self::South => 1,
        };
    }
}
