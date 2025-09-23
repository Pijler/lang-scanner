<?php

namespace App\Repositories;

use App\Project;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class CacheRepository
{
    /**
     * Path to store the cache file.
     */
    protected static ?string $cache = null;

    /**
     * Initialize the cache settings based on configuration.
     */
    public static function initializeCache(array $config): void
    {
        $cache = data_get($config, 'cache');

        if ($cache === false) {
            static::$cache = null;

            return;
        }

        static::$cache = $cache ?? Project::path().'/bootstrap/cache/cache.scanner';

        File::ensureDirectoryExists(dirname(static::$cache));
    }

    /**
     * Update the last run timestamp in the cache.
     */
    public static function lastRun(): void
    {
        $cache = rescue(function () {
            return json_decode(File::get(static::$cache), true);
        }, []);

        data_set($cache, 'last_run', Carbon::now()->toDateTimeString());

        File::put(static::$cache, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * Check if the file has changed since last scan.
     */
    public static function cacheFile(SplFileInfo $file, Closure $callback): array
    {
        if (static::$cache === null) {
            return $callback($file);
        }

        $cache = rescue(function () {
            return json_decode(File::get(static::$cache), true);
        }, []);

        $cacheKey = md5($file->getRealPath());

        $currentValue = data_get($cache, "files.{$cacheKey}");

        $cacheValue = md5($file->getRealPath().'|'.$file->getMTime());

        if ($cacheValue === $currentValue) {
            return [];
        }

        return tap($callback($file), function () use ($cache, $cacheKey, $cacheValue) {
            data_set($cache, "files.{$cacheKey}", $cacheValue);

            File::put(static::$cache, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
        });
    }
}
