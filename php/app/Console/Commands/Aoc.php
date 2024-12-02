<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Days\AocDay;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class Aoc extends Command
{
    protected $signature = 'aoc
        {input? : The challenge input}
        {--y|year= : The year to execute, if null then executes current year}
        {--d|day= : The day to execute, if null then executes all days}
        {--p|part= : The part to execute, if null then executes all parts}
    ';

    protected $description = 'Execute AoC solutions.';

    public function __invoke(): void
    {
        $year = $this->option('year') ?? date('Y');

        $this->newLine();
        $this->components->info("Advent of Code {$year}");

        $input = $this->getProgramInput();

        $this->registerDays($year)
            ->when(
                $this->option('day'),
                fn ($days, $day) => $days->filter(fn ($_, $class) => str_contains(
                    $class,
                    str_pad($day, 2, '0', STR_PAD_LEFT),
                )),
            )
            ->whenEmpty(fn () => $this->components->warn('No days found.'))
            ->each(function ($day) use ($input, $year) {
                $startTime = microtime(true);
                $input ??= $this->getDayInput($day, $year);
                $result = $day($input, (int) $this->option('part'));
                $runTime = number_format((microtime(true) - $startTime) * 1000);

                $this->components->twoColumnDetail(
                    "<fg=green;options=bold>{$day->label()}</>",
                    "<fg=gray>$runTime ms</>",
                );
                $this->components->twoColumnDetail('Part one', $this->formatResult($result[0]));
                $this->components->twoColumnDetail('Part two', $this->formatResult($result[1]));
                $this->newLine();
            });
    }

    private function formatResult(mixed $value): string
    {
        if ($value !== null) {
            return (string) $value;
        }

        return "<fg=yellow;options=bold>INCOMPLETE</>";
    }

    private function getProgramInput(): ?string
    {
        if (! posix_isatty(STDIN)) {
            return file_get_contents('php://stdin');
        }

        return $this->argument('input');
    }

    private function getDayInput(AocDay $day, string $year): string
    {
        $file = Str::camel(class_basename($day)) . '.txt';
        $path = dirname(__DIR__, levels: 4) . "/inputs/{$year}/{$file}";

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        $day = (int) Str::of($file)->match('/\d+/')->value();
        $session = env('AOC_SESSION');
        throw_unless($session, 'No session cookie found. Please set the AOC_SESSION environment variable.');

        $response = Http::withHeaders(['Cookie' => "session={$session}"])
            ->get("https://adventofcode.com/{$year}/day/{$day}/input")
            ->throw();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, recursive: true);
        }

        file_put_contents($path, $response->body());

        return $response->body();
    }

    /** @return Collection<int, AocDay> */
    private function registerDays(string $year): Collection
    {
        return collect()
            ->wrap(iterator_to_array(
                Finder::create()->files()
                    ->in([app_path()])
                    ->path("Days/Year{$year}"),
            ))
            ->map(fn ($file) => str_replace(
                search: ['app/', '/', '.php'],
                replace: ['App\\', '\\', ''],
                subject: Str::after($file->getRealPath(), base_path() . DIRECTORY_SEPARATOR),
            ))
            ->sort()
            ->filter(fn ($class) => is_subclass_of($class, AocDay::class))
            ->mapWithKeys(fn ($c) => [$c => resolve($c)]);
    }
}
