<?php

declare(strict_types=1);

namespace App\Days\Year2024;

use App\Days\AocDay;

class Day07 extends AocDay
{
    /** @var array<int, list<int>> */
    private array $equations = [];

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->equations = $this->lines
            ->filter()
            ->mapWithKeys(function (string $line) {
                [$total, $values] = explode(': ', $line);

                return [(int) $total => array_map('intval', explode(' ', $values))];
            })
            ->all();
    }

    public function partOne(): ?string
    {
        return (string) collect($this->equations)
            ->filter(fn ($values, $total) => $this->evaluate($total, $values))
            ->keys()
            ->sum();
    }

    public function partTwo(): ?string
    {
        return (string) collect($this->equations)
            ->filter(fn ($values, $total) => $this->evaluate($total, $values, true))
            ->keys()
            ->sum();
    }

    /** @param list<int> $values */
    private function evaluate(int $total, array $values, bool $canConcat = false): bool
    {
        if (count($values) === 1) {
            return $total === $values[0];
        }

        $success = false;
        $value = array_pop($values);

        if ($total % $value === 0) {
            $success = $this->evaluate(intdiv($total, $value), $values, $canConcat);
        }

        if ($canConcat && str_ends_with((string) $total, (string) $value)) {
            $success |= $this->evaluate(
                (int) substr((string) $total, 0, -strlen((string) $value)),
                $values,
                $canConcat,
            );
        }

        return $success || $this->evaluate($total - $value, $values, $canConcat);
    }
}
