<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;

class Day06 extends AocDay
{
    /** @var array{0: int, 1: int} */
    private array $start;

    private Guard $guard;

    /** @var list<list<Tile>> */
    private array $map;

    protected function parseInput(string $input): void
    {
        $this->map = collect(explode("\n", $input))
            ->filter()
            ->map(function (string $line, int $y) {
                $values = array_map([Tile::class, 'from'], str_split($line));

                if (($x = array_search(Tile::Guard, $values)) !== false) {
                    $this->start = [$x, $y];
                }

                return $values;
            })
            ->all();

        $this->guard = new Guard(...$this->start);
    }

    public function partOne(): ?string
    {
        $this->patrol();

        return (string) count($this->guard->visited);
    }

    public function partTwo(): ?string
    {
        $loops = 0;
        $visited = array_keys($this->guard->visited);

        foreach ($visited as $position) {
            [$x, $y] = explode(':', $position);
            $this->map[$y][$x] = Tile::Obstruction;
            $this->guard = new Guard(...$this->start);

            if ($this->patrol()) {
                $loops++;
            }

            $this->map[$y][$x] = Tile::Empty;
        }

        return (string) $loops;
    }

    /** @return $this */
    private function tick(): self
    {
        match ($this->peak($this->guard)) {
            Tile::Obstruction,
            Tile::Object => $this->guard->rotate(),
            default => $this->guard->move(),
        };

        return $this;
    }

    private function printMap(): void
    {
        echo collect($this->map)
            ->map(fn (array $row) => array_map(fn (Tile $tile) => $tile->value, $row))
            ->map(fn (array $row) => implode('', $row))
            ->implode("\n");
        echo "\n\n";
    }

    /** Returns true if loop is detected. */
    private function patrol(): bool
    {
        $rows = count($this->map) + 1;

        do {
            if ($this->verbose) {
                $this->printMap();
            }

            $this->tick();

            if ($this->guard->hasLooped) {
                break;
            }

            if ($this->verbose) {
                echo "\033[{$rows}F"; // restore cursor position
                usleep(100);
            }
        } while (isset($this->map[$this->guard->y][$this->guard->x]));

        return $this->guard->hasLooped;
    }

    private function peak(Guard $guard): ?Tile
    {
        $x = $guard->x;
        $y = $guard->y;

        match ($this->guard->direction) {
            Direction::Up => $y--,
            Direction::Right => $x++,
            Direction::Down => $y++,
            Direction::Left => $x--,
        };

        return $this->map[$y][$x] ?? null;
    }
}

class Guard
{
    public bool $hasLooped = false;

    /** @var array<string, list<Direction>> */
    public array $visited = [];

    public function __construct(
        public int $x,
        public int $y,
        public Direction $direction = Direction::Up,
    ) {
    }

    /** @return $this */
    public function move(): self
    {
        match ($this->direction) {
            Direction::Up => $this->y--,
            Direction::Right => $this->x++,
            Direction::Down => $this->y++,
            Direction::Left => $this->x--,
        };

        $key = "$this->x:$this->y";
        if (! isset($this->visited[$key])) {
            $this->visited[$key] = [$this->direction];
        } elseif (in_array($this->direction, $this->visited[$key])) {
            $this->hasLooped = true;
        } else {
            $this->visited[$key][] = $this->direction;
        }

        return $this;
    }

    /** @return $this */
    public function rotate(): self
    {
        $this->direction = match ($this->direction) {
            Direction::Up => Direction::Right,
            Direction::Right => Direction::Down,
            Direction::Down => Direction::Left,
            Direction::Left => Direction::Up,
        };

        return $this;
    }
}

enum Direction: string
{
    case Up = '^';
    case Right = '>';
    case Down = 'v';
    case Left = '<';
}

enum Tile : string
{
    case Object = '#';
    case Empty = '.';
    case Guard = '^';
    case Visited = 'X';
    case Obstruction = 'O';
}
