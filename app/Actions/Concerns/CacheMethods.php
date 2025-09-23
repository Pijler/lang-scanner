<?php

namespace App\Actions\Concerns;

use App\Project;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property array $config
 */
trait CacheMethods
{
    /**
     * Path to store the cache file.
     */
    protected ?string $cachePath = null;

    /**
     * Initialize the cache settings based on configuration.
     */
    protected function initializeCache(): void
    {
        $cache = data_get($this->config, 'cache');

        if ($cache === false) {
            $this->cachePath = null;

            return;
        }

        $this->cachePath = $cache
            ? dirname($cache).'/scanner.json'
            : Project::path().'/storage/framework/cache/scanner.json';

        File::ensureDirectoryExists(dirname($this->cachePath));
    }

    /**
     * Check if the file has changed since last scan.
     */
    protected function cacheFile(SplFileInfo $file, Closure $callback): array
    {
        if ($this->cachePath === null) {
            return $callback($file);
        }

        $cache = rescue(function () {
            return json_decode(File::get($this->cachePath), true);
        }, []);

        $cacheKey = md5($file->getRealPath());

        $currentValue = data_get($cache, "files.{$cacheKey}");

        $cacheValue = md5($file->getRealPath().'|'.$file->getMTime());

        if ($cacheValue === $currentValue) {
            return [];
        }

        return tap($callback($file), function () use ($cache, $cacheKey, $cacheValue) {
            data_set($cache, "files.{$cacheKey}", $cacheValue);

            data_set($cache, 'last_run', Carbon::now()->toDateTimeString());

            File::put($this->cachePath, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
        });
    }
}
