<?php

declare(strict_types=1);

namespace App\Days;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day08 extends AocDay
{
    /** @var Collection<int, Node> */
    protected Collection $nodes;

    protected string $instructions;
    protected int $instructionsLength;

    protected function parseInput(string $input): void
    {
        [$instructions, $nodes] = explode("\n\n", $input);

        $this->instructions = $instructions;
        $this->instructionsLength = strlen($instructions);

        $this->nodes = collect(explode("\n", $nodes))
            ->filter()
            ->map(fn ($line) => $this->parseNode($line))
            ->keyBy('value');
    }

    public function partOne(): mixed
    {
        return $this->walk(from: 'AAA', to: 'ZZZ');
    }

    public function partTwo(): mixed
    {
        // $prod = $this->nodes
        //     ->filter(fn ($node) => $node->value[2] === 'A')
        //     ->values()
        //     ->map(fn ($node) => $this->walk($node->value, fn ($value) => $value[2] === 'Z'))
        // ->dd()
        //     ->reduce(fn ($carry, $count) => $carry * $count, 1);

        // return (string) $prod;

        return $this->walkParallel();
    }

    protected function parseNode(string $line): Node
    {
        [$value, $elements] = explode(' = ', $line);

        return new Node(
            value: $value,
            left: Str::of($elements)->after('(')->before(',')->toString(),
            right: Str::of($elements)->after(', ')->before(')')->toString(),
        );
    }

    protected function walk(string $from, string|Closure $to): int
    {
        if (is_string($to)) {
            $to = fn ($value) => $value === $to;
        }

        $current = $this->nodes[$from];
        $steps = 0;

        while (! $to($current->value)) {
            $node = match($this->instructions[$steps % $this->instructionsLength]) {
                'L' => $current->left,
                'R' => $current->right,
            };

            $current = $this->nodes[$node];

            $steps++;
        }

        return $steps;
    }

    protected function walkParallel(): int
    {
        $nodes = $this->nodes
            ->filter(fn ($n) => $n->value[2] === 'A')
            ->values();
        $steps = 0;

        while ($nodes->filter(fn ($n) => $n->value[2] !== 'Z')->count()) {
            $instruction = $this->instructions[$steps % $this->instructionsLength];

            foreach ($nodes as $i => $current) {
                $nodeValue = match($instruction) {
                    'L' => $current->left,
                    'R' => $current->right,
                };

                $nodes[$i] = $this->nodes[$nodeValue];
            }

            $steps++;
        }

        return $steps;
    }
}

final readonly class Node
{
    public function __construct(
        public string $value,
        public string $left,
        public string $right,
    ) {
    }
}
