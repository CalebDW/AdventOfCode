<?php

declare(strict_types=1);

namespace App\Concerns;

use Closure;

/**
 * @phpstan-type ProfiledResult array{value: mixed, time_ms: float, memory_kb: float}
 */
trait ProfilesClosures
{
    /** @return ProfiledResult */
    protected function profile(Closure $closure): array
    {
        $result = [
            'value' => null,
            'time_ms' => 0,
            'memory_kb' => 0,
        ];

        // $startMemory = memory_get_peak_usage();
        if (gc_enabled()) {
            gc_collect_cycles();
            gc_disable();
        }

        $startMemory = memory_get_usage();
        $startTime = microtime(true);

        $result['value'] = $closure();

        $result['time_ms'] = (microtime(true) - $startTime) * 1000;
        // $result['memory_kb'] = (memory_get_peak_usage() - $startMemory) / 1024;
        $result['memory_kb'] = (memory_get_usage() - $startMemory) / 1024;

        if (! gc_enabled()) {
            gc_enable();
        }

        return $result;
    }
}
