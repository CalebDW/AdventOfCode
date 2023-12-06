<?php

declare(strict_types=1);

namespace App\Days;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day05 extends AocDay
{
    /** @var array<int, Map> */
    protected array $maps;

    /** @var Collection<int, int> */
    protected Collection $seeds;

    protected function parseInput(string $input): void
    {
        $blocks = explode("\n\n", $input);

        $this->seeds = Str::of(array_shift($blocks))
            ->after('seeds: ')
            ->squish()
            ->explode(' ')
            ->map(fn ($n) => (int) $n);

        $this->maps = collect($blocks)
            ->map(fn ($map) => $this->parseMap($map))
            ->keyBy(fn ($map) => $map->src)
            ->all();
    }

    public function parseMap(string $map): Map
    {
        $lines = explode("\n", $map);

        $categories = Str::of(array_shift($lines))
            ->before(' map')
            ->explode('-to-');

        return new Map(
            src: $categories->first(),
            dest: $categories->last(),
            ranges: $this->parseRanges($lines)
        );
    }

    /**
     * @param array<int, string> $lines
     * @return array<int, array{dest: int, src: int, length: int}>
     */
    public function parseRanges(array $lines): array
    {
        return $this->fillMissingRanges(
            collect($lines)
                ->filter()
                ->map(fn ($line) => array_map('intval', explode(' ', $line)))
                ->map(fn ($values) => [
                    'src' => $values[1],
                    'dest' => $values[0],
                    'length' => $values[2],
                ])
                ->sort(fn ($a, $b) => $a['src'] <=> $b['src'])
                ->values(),
        );
    }

    /**
     * @param Collection<int, string> $ranges
     * @return array<int, array{dest: int, src: int, length: int}>
     */
    public function fillMissingRanges(Collection $ranges): array
    {
        $start = 0;

        for ($i = 0; $i < $ranges->count(); $i++) {
            $range = $ranges[$i];

            if ($start < $range['src']) {
                $ranges->splice($i, 0, [[
                    'src' => $start,
                    'dest' => $start,
                    'length' => $range['src'] - $start,
                ]]);

                $i++;
            }

            $start = $range['src'] + $range['length'];
        }

        return $ranges->all();
    }

    public function partOne(): mixed
    {
        return $this->seeds
            ->map(fn ($seed) => $this->walk('seed', 'location', $seed, 1)[0])
            ->min();
    }

    public function partTwo(): mixed
    {
        $ranges = $this->seeds
            ->sliding(2, step: 2)
            ->map(fn ($s) => $s->values()->all())
            ->all();

        $minimum = PHP_INT_MAX;

        foreach ($ranges as $range) {
            $start = $range[0];
            $remainder = $range[1];

            while ($remainder > 0) {
                [$value, $consumed] = $this->walk('seed', 'location', $start, $remainder);

                $remainder -= $consumed;
                $start += $consumed;

                $minimum = min($minimum, $value);
            }
        }

        return $minimum;
    }

    /** @return array{int, int} */
    public function walk(string $src, string $dest, int $value, int $length): array
    {
        if ($src === $dest) {
            return [$value, $length];
        }

        $map = $this->maps[$src];

        foreach ($map->ranges as $range) {
            $diff = $value - $range['src'];

            if ($diff < 0 || $diff >= $range['length']) {
                continue;
            }

            $newValue = $range['dest'] + $diff;
            $newLength = min($length, $range['length'] - $diff);

            return $this->walk($map->dest, $dest, $newValue, $newLength);
        }

        return $this->walk($map->dest, $dest, $value, 1);
    }
}

final readonly class Map
{
    public function __construct(
        public string $src,
        public string $dest,
        /** @var array<int, array{src: int, dest: int, length: int}>*/
        public array $ranges
    ) { }
}
