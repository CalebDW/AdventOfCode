<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Days\AocDay;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class Aoc extends Command
{
    protected $signature = 'aoc
        {input? : The challenge input}
        {--d|day= : The day to execute, if null then executes all days}
        {--p|part= : The part to execute, if null then executes all parts}
    ';

    protected $description = 'Execute AoC solutions.';

    public function __invoke(): void
    {
        $input = $this->getProgramInput();

        $this->registerDays()
            ->when(
                $this->option('day'),
                fn ($days, $day) => $days->filter(
                    fn ($_, $class) => str_contains(
                        $class,
                        str_pad($day, 2, '0', STR_PAD_LEFT),
                    ),
                ),
            )
            ->whenEmpty(fn () => $this->components->warn('No days found.'))
            ->each(function ($day) use ($input) {
                $startTime = microtime(true);

                if ($input === null) {
                    $input = $this->getDayInput($day);
                }

                $result = $day($input, (int) $this->option('part'));

                $runTime = number_format((microtime(true) - $startTime) * 1000);

                $this->components->twoColumnDetail(
                    "<fg=green;options=bold>{$day->label()}</>",
                    "<fg=gray>$runTime ms</>",
                );
                $this->components->twoColumnDetail('Part one', $result[0]);
                $this->components->twoColumnDetail('Part two', $result[1]);
                $this->newLine();
            });
    }

    private function getProgramInput(): ?string
    {
        if (! posix_isatty(STDIN)) {
            return file_get_contents('php://stdin');
        }

        return $this->argument('input');
    }

    private function getDayInput(AocDay $day): string
    {
        $file = Str::camel(class_basename($day)) . '.txt';

        return file_get_contents(
            dirname(__DIR__, levels: 4) . "/inputs/{$file}",
        );
    }

    /** @return Collection<int, AocDay> */
    private function registerDays(): Collection
    {
        return collect()
            ->wrap(iterator_to_array(
                resolve(Finder::class)->files()
                    ->in([app_path()])
                    ->path('Days'),
            ))
            ->map(fn ($file) => str_replace(
                search: ['app/', '/', '.php'],
                replace: ['App\\', '\\', ''],
                subject: Str::after($file->getRealPath(), base_path()),
            ))
            ->sort()
            ->filter(fn ($class) => is_subclass_of($class, AocDay::class))
            ->mapWithKeys(fn ($c) => [$c => $c])
            ->map(fn ($class) => resolve($class));
    }
}
