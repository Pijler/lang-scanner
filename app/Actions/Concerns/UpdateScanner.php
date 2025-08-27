<?php

namespace App\Actions\Concerns;

use App\Enum\Status;
use App\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property array $paths
 * @property array $changes
 * @property int $totalFiles
 * @property InputInterface $input
 * @property OutputInterface $output
 * @property ProgressOutput $progressOutput
 */
trait UpdateScanner
{
    /**
     * Updates translation files.
     */
    protected function updateScanner(array $config): void
    {
        if (isset($config['check_only']) && $config['check_only']) {
            return;
        }

        $collectedKeys = [];

        $files = $this->getFilesToScan($config);

        $translations = $this->getTranslations($config);

        $files->each(function (SplFileInfo $file) use ($config, &$collectedKeys) {
            $this->totalFiles++;

            $this->progressOutput->handle(Status::OK);

            $keys = $this->extractTranslationKeysFromFile($file, $config);

            $collectedKeys = array_merge($collectedKeys, $keys);
        });

        $newTranslations = $this->returnNewTranslations($translations, $collectedKeys);

        if (filled($newTranslations)) {
            $this->addNewTranslations($config, $newTranslations);
        }
    }

    /**
     * Sorts a multi-dimensional array recursively.
     */
    private function sortRecursive(array $array): array
    {
        ksort($array);

        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursive($value);
            }
        }

        return $array;
    }

    /**
     * Merges old and new translations.
     */
    private function mergeTranslations(array $old, array $new): array
    {
        $newTranslations = array_replace_recursive($new, $old);

        $diffQuantity = count(array_diff(
            collect($newTranslations)->dot()->keys()->toArray(),
            collect($old)->dot()->keys()->toArray(),
        ));

        return [$newTranslations, $diffQuantity];
    }

    /**
     * Get files to scan based on configuration.
     */
    private function getFilesToScan(array $config): Collection
    {
        abort_unless(isset($config['paths']), 'Paths are not set.');

        abort_unless(isset($config['extensions']), 'Extensions are not set.');

        return collect($config['paths'])
            ->map(function ($path) {
                $fullPath = Project::path($this->input).'/'.$path;

                return File::exists($fullPath) ? File::allFiles($fullPath) : [];
            })
            ->collapse()
            ->filter(function (SplFileInfo $file) use ($config) {
                return Str::endsWith($file->getFilename(), $config['extensions']);
            })
            ->filter(function (SplFileInfo $file) {
                return Str::startsWith($file->getPathname(), $this->paths);
            })
            ->values();
    }

    /**
     * Extract translation keys from a file based on config methods.
     */
    private function extractTranslationKeysFromFile(SplFileInfo $file, array $config): array
    {
        $keys = [];

        $content = $file->getContents();

        abort_unless(isset($config['methods']), 'Methods are not set in config.');

        $patterns = collect($config['methods'])->map(function ($method) {
            $escapedMethod = preg_quote($method, '/');

            return "/\\b{$escapedMethod}\\s*\\(\\s*['\"]([^'\"]+)['\"]\\s*[\\),]/";
        })->toArray();

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            if (filled($matches[1])) {
                $keys = array_merge($keys, $matches[1]);
            }
        }

        return array_filter(array_unique($keys), fn ($key) => filled($key));
    }

    /**
     * Add new translations to the translation file.
     */
    private function addNewTranslations(array $config, array $new): void
    {
        $files = rescue(function () use ($config) {
            return File::allFiles(Project::path().'/'.$config['lang_path']);
        }, [], false);

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($new) {
                $old = json_decode($file->getContents(), true);

                [$newTranslations, $diffQuantity] = $this->mergeTranslations($old, $new);

                File::put($file->getRealPath(), json_encode($newTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                $this->changes[] = [
                    'quantity' => $diffQuantity,
                    'file' => $file->getRealPath(),
                ];
            });
    }

    /**
     * Return new translations based on collected keys.
     */
    private function returnNewTranslations(array $translations, array $collectedKeys): array
    {
        $allTranslations = collect($collectedKeys)
            ->keyBy(fn ($key) => $key)
            ->map(fn () => '')
            ->undot()
            ->toArray();

        $newTranslations = array_replace_recursive($translations, $allTranslations);

        return $this->sortRecursive($newTranslations);
    }
}
