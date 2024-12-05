<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;

class Day04 extends AocDay
{
    /** @var list<list<string>> */
    private array $chars;

    protected function parseInput(string $input): void
    {
        $this->chars = collect(explode("\n", $input))
            ->filter()
            ->map(fn ($line) => str_split($line))
            ->toArray();
    }

    public function partOne(): ?string
    {
        $result = 0;
        $needle = 'XMAS';
        $directions = [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]];
        $rows = count($this->chars);
        $cols = count($this->chars[0]);
        $length = strlen($needle);

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if ($this->chars[$r][$c] !== $needle[0]) {
                    continue;
                }

                foreach ($directions as [$dx, $dy]) {
                    for ($i = 1; $i < $length; $i++) {
                        $x = $c + $i * $dx;
                        $y = $r + $i * $dy;

                        if (($this->chars[$y][$x] ?? null) !== $needle[$i]) {
                            continue 2;
                        }
                    }

                    $result++;
                }
            }
        }

        return (string) $result;
    }

    public function partTwo(): ?string
    {
        $result = 0;
        $needle = 'MAS';
        $directions = [[1, 1], [-1, -1], [1, -1], [-1, 1]];
        $rows = count($this->chars);
        $cols = count($this->chars[0]);
        $length = strlen($needle);

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $count = 0;

                if ($this->chars[$r][$c] !== $needle[1]) {
                    continue;
                }

                foreach ($directions as [$dx, $dy]) {
                    for ($i = -1; $i < $length-1; $i++) {
                        $x = $c + $i * $dx;
                        $y = $r + $i * $dy;

                        if (($this->chars[$y][$x] ?? null) !== $needle[$i+1]) {
                            continue 2;
                        }
                    }

                    $count++;
                }

                if ($count == 2) {
                    $result++;
                }
            }
        }

        return (string) $result;
    }
}
