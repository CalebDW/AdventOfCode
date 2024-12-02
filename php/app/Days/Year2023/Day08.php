<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
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

        $this->nodes = $this->parseNodes($nodes);
    }

    public function partOne(): mixed
    {
        return $this->walk(
            from: $this->nodes['AAA'],
            to: $this->nodes['ZZZ'],
        );
    }

    public function partTwo(): mixed
    {
        return $this->nodes
            ->filter(fn ($node) => $node->value[2] === 'A')
            ->values()
            ->map(fn ($node) => $this->walk($node, fn ($node) => $node->value[2] === 'Z'))
            ->reduce(fn ($carry, $count) => ($carry * $count) / gmp_gcd($carry, $count), 1);
    }

    /** @return Collection<int, Node> */
    protected function parseNodes(string $nodes): Collection
    {
        $nodes = collect(explode("\n", $nodes))
            ->filter()
            ->map(fn ($line) => $this->parseNode($line))
            ->keyBy('value');

        return $this->attachNodes($nodes);
    }

    protected function parseNode(string $line): Node
    {
        [$value, $elements] = explode(' = ', $line);

        return new Node(
            value: $value,
            children: Str::of($elements)->remove(['(', ')'])->explode(', ')->all(),
        );
    }

    /**
     * @param Collection<int, Node> $nodes
     * @return Collection<int, Node>
     */
    protected function attachNodes(Collection $nodes): Collection
    {
        return $nodes->each(function ($node) use ($nodes) {
            $node->left = $nodes[$node->children[0]];
            $node->right = $nodes[$node->children[1]];
        });
    }

    protected function walk(Node $from, Node|Closure $to): int
    {
        if ($to instanceof Node) {
            $to = fn ($node) => $node === $to;
        }

        $steps = 0;

        while (! $to($from)) {
            $from = match($this->instructions[$steps % $this->instructionsLength]) {
                'L' => $from->left,
                'R' => $from->right,
            };

            $steps++;
        }

        return $steps;
    }
}

final class Node
{
    public Node $left;
    public Node $right;

    public function __construct(
        public string $value,
        /** @var array{string, string} */
        public array $children,
    ) {
    }
}
