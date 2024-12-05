<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Concerns\ProfilesClosures;
use App\Days\AocDay;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/** @phpstan-import-type ProfiledResult from ProfilesClosures */
class Aoc extends Command
{
    use ProfilesClosures;

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
        $input = $this->getProgramInput();

        $this->newLine();
        $this->components->info("Advent of Code {$year}");

        $this->registerDays($year, $this->option('day'))
            ->whenEmpty(fn () => $this->components->warn('No days found.'))
            ->each(function ($day) use ($input, $year) {
                $input ??= $this->getDayInput($day, $year);

                $result = $this->profile(fn () => $day($input, (int) $this->option('part')));

                $this->components->twoColumnDetail(
                    "<fg=green;options=bold>{$day->label()}</>",
                    $this->formatProfile($result),
                );

                $this->components->twoColumnDetail('Part one', $this->formatResult($result['value'][0]));
                $this->components->twoColumnDetail('Part two', $this->formatResult($result['value'][1]));
                $this->newLine();
            });
    }

    /** @param ?ProfiledResult $result */
    private function formatResult(?array $result): string
    {
        if ($result === null) {
            return "<fg=blue;options=bold>SKIPPED</>";
        }

        if ($result['value'] === null) {
            return "<fg=yellow;options=bold>INCOMPLETE</>";
        }

        return "{$this->formatProfile($result)} {$result['value']}";
    }

    /** @param ProfiledResult $result */
    private function formatProfile(array $result): string
    {
        $time = number_format($result['time_ms'], 3);
        $memory = number_format($result['memory_kb'], 3);

        return "<fg=gray>{$time} ms, {$memory} kB</>";
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
    private function registerDays(string $year, ?string $day = null): Collection
    {
        $path = "Days/Year{$year}";

        if (! is_null($day)) {
            $path .= "/Day" . str_pad($day, 2, '0', STR_PAD_LEFT);
        }

        return collect()
            ->wrap(iterator_to_array(
                Finder::create()->files()
                    ->in([app_path()])
                    ->path($path),
            ))
            ->map(fn ($file) => str_replace(
                search: ['app/', '/', '.php'],
                replace: ['App\\', '\\', ''],
                subject: Str::after($file->getRealPath(), base_path() . DIRECTORY_SEPARATOR),
            ))
            ->filter(fn ($class) => is_subclass_of($class, AocDay::class))
            ->sort()
            ->values()
            ->map(fn ($class) => new $class());
    }
}
