<?php

declare(strict_types=1);

namespace App\Days;

use Illuminate\Support\Collection;

class Day07 extends AocDay
{
    /** @var Collection<int, Hand> */
    protected Collection $hands;

    protected function parseInput(string $input): void
    {
        parent::parseInput($input);

        $this->hands = $this->lines
            ->filter()
            ->map(fn ($line) => $this->parseHand($line));
    }

    public function partOne(): mixed
    {
        return $this->hands
            ->sortBy([
                fn ($a, $b) => $a->type() <=> $b->type(),
                fn ($a, $b) => $a->strength() <=> $b->strength(),
            ])
            ->values()
            ->map(fn ($hand, $rank) => $hand->bid * ($rank + 1))
            ->sum();
    }

    public function partTwo(): mixed
    {
        Hand::hasJokers(true);

        return $this->partOne();
    }

    protected function parseHand(string $line): Hand
    {
        $hand = explode(' ', $line);

        return new Hand(
            cards: collect(str_split($hand[0])),
            cardString: $hand[0],
            bid: (int) $hand[1],
        );
    }
}

final class Hand
{
    private static bool $hasJokers = false;

    /** @var array<string, int> */
    private static array $cardStrength = [
         '2' => 2,
         '3' => 3,
         '4' => 4,
         '5' => 5,
         '6' => 6,
         '7' => 7,
         '8' => 8,
         '9' => 9,
         'T' => 10,
         'J' => 11,
         'Q' => 12,
         'K' => 13,
         'A' => 14,
    ];

    /** @var array<string, int> */
    private static array $handStrength = [];

    /** @var array<string, int> */
    private static array $handType = [];

    public function __construct(
        /** @var Collection<int, string> */
        public Collection $cards,
        private string $cardString,
        public int $bid,
    ) {
    }

    public static function hasJokers(bool $hasJokers): void
    {
        static::$hasJokers = $hasJokers;
        static::$cardStrength['J'] = $hasJokers ? 1 : 11;
        static::$handStrength = [];
        static::$handType = [];
    }

    public function strength(): int
    {
        if (isset(static::$handStrength[$this->cardString])) {
            return static::$handStrength[$this->cardString];
        }

        $strengths = $this->cards
            ->map(fn ($c) => static::$cardStrength[$c]);

        $strength = $strengths[0] << 16
            | $strengths[1] << 12
            | $strengths[2] << 8
            | $strengths[3] << 4
            | $strengths[4];

        return static::$handStrength[$this->cardString] = $strength;
    }

    public function type(): int
    {
        if (isset(static::$handType[$this->cardString])) {
            return static::$handType[$this->cardString];
        }

        $groups = $this->cards->groupBy(fn ($c) => $c)
            ->map(fn ($g) => $g->count())
            ->sortDesc();

        $jokers = static::$hasJokers
            ? $groups->pull('J')
            : null;

        $groups = $groups->values()->all();

        if ($jokers) {
            $groups[0] = ($groups[0] ?? 0) + $jokers;
        }

        $type = match ($groups[0]) {
            5 => HandType::FiveOfAKind,
            4 => HandType::FourOfAKind,
            3 => match($groups[1]) {
                2 => HandType::FullHouse,
                1 => HandType::ThreeOfAKind,
            },
            2 => match($groups[1]) {
                2 => HandType::TwoPair,
                1 => HandType::OnePair,
            },
            1 => HandType::HighCard,
        };

        return static::$handType[$this->cardString] = $type->value;
    }
}

enum HandType: int
{
    case HighCard = 1;
    case OnePair = 2;
    case TwoPair = 3;
    case ThreeOfAKind = 4;
    case FullHouse = 5;
    case FourOfAKind = 6;
    case FiveOfAKind = 7;
}
