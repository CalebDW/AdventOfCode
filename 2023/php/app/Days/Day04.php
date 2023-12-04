<?php

declare(strict_types=1);

namespace App\Days;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Day04 extends AocDay
{
    /** @var Collection<int, Card> */
    protected Collection $cards;

    /** @var array<int, int> */
    protected array $cardStack;

    /** @var array<int, int> */
    protected array $processedCardIds = [];

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
        $this->cardStack = $this->cards->pluck('id')
            ->reverse()
            ->all();

        while (! empty($this->cardStack)) {
            $card = $this->cards[array_pop($this->cardStack)];

            $this->processedCardIds[] = $card->id;

            if (! $numberOfMatches = $card->numberOfMatches()) {
                continue;
            }

            for ($i = $numberOfMatches; $i > 0; $i--) {
                $this->cardStack[] = $this->cards[$card->id + $i]->id;
            }
        }

        return count($this->processedCardIds);
    }

    public function parseCard(string $line): Card
    {
        preg_match('/Card\s+(\d+):/', $line, $match);

        $numbers = Str::of($line)
            ->after(':')
            ->squish()
            ->explode(' | ');

        return new Card(
            id: (int) $match[1],
            winningNumbers: array_map('intval', explode(' ', $numbers[0])),
            numbers: array_map('intval', explode(' ', $numbers[1])),
        );
    }
}

final readonly class Card
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
