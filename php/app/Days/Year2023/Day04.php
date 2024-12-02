<?php

declare(strict_types=1);

namespace App\Days\Year2023;

use App\Days\AocDay;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day04 extends AocDay
{
    /** @var Collection<int, Card> */
    protected Collection $cards;

    /** @var array<int, int> */
    protected array $totalCounts = [];

    public function partOne(): mixed
    {
        $this->cards = $this->lines
            ->filter()
            ->map(fn ($line) => $this->parseCard($line))
            ->keyBy('id');

        return $this->cards->sum(fn ($card) => $card->score());
    }

    public function partTwo(): mixed
    {
        $this->cards->reverse()->each(fn ($card) => $this->addCount($card->id));

        return array_sum($this->totalCounts);
    }

    public function parseCard(string $line): CardDay04
    {
        preg_match('/Card\s+(\d+):/', $line, $match);

        $numbers = Str::of($line)
            ->after(':')
            ->squish()
            ->explode(' | ');

        return new CardDay04(
            id: (int) $match[1],
            winningNumbers: array_map('intval', explode(' ', $numbers[0])),
            numbers: array_map('intval', explode(' ', $numbers[1])),
        );
    }

    public function addCount(int $cardId): int
    {
        if (isset($this->totalCounts[$cardId])) {
            return $this->totalCounts[$cardId];
        }

        $this->totalCounts[$cardId] = 1;

        $copies = ($numberOfMatches = $this->cards[$cardId]->numberOfMatches())
            ? range($numberOfMatches + $cardId, $cardId + 1)
            : [];

        foreach ($copies as $newCardId) {
            $this->totalCounts[$cardId] += $this->addCount($newCardId);
        }

        return $this->totalCounts[$cardId];
    }
}

final readonly class CardDay04
{
    private int $numberOfMatches;

    public function __construct(
        public int $id,
        /** @var array<int, int> */
        public array $winningNumbers,
        /** @var array<int, int> */
        public array $numbers,
    ) {
    }

    public function score(): int
    {
        return (int) (2 ** ($this->numberOfMatches() - 1));
    }

    public function numberOfMatches(): int
    {
        if (isset($this->numberOfMatches)) {
            return $this->numberOfMatches;
        }

        return $this->numberOfMatches = array_reduce(
            $this->numbers,
            fn ($c, $number) => $c + (int) in_array(
                $number,
                $this->winningNumbers,
                strict: true,
            ));
    }
}
