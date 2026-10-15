<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Enum\Status;
use App\Output\ProgressOutput;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class UpdateScanner
{
    use BaseMethods;

    /**
     * The keys used in code, and the config, grouped by language path.
     */
    protected array $usedKeys = [];

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {}

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(array $config): array
    {
        $this->config = $config;

        $files = $this->getFilesToScan();

        $this->updateTranslations($files);

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Update translations based on collected keys from files.
     */
    private function updateTranslations(Collection $files): void
    {
        $collectedKeys = [];

        $translations = $this->getTranslations();

        $files->each(function (SplFileInfo $file) use (&$collectedKeys) {
            $this->totalFiles++;

            $this->progressOutput->handle(Status::SKIPPED);

            $keys = $this->extractTranslationKeysFromFile($file);

            $collectedKeys = array_merge($collectedKeys, $keys);
        });

        $this->rememberUsedKeys($collectedKeys);

        $newTranslations = $this->returnNewTranslations($translations, $collectedKeys);

        if (filled($newTranslations)) {
            $this->addNewTranslations($newTranslations);
        }
    }

    /**
     * Finds the translations that are not used in code, removing them when pruning.
     */
    public function unused(bool $prune): array
    {
        $changes = [];

        foreach ($this->usedKeys as ['config' => $config, 'keys' => $keys]) {
            $this->config = $config;

            $used = array_fill_keys($keys, true);

            collect($this->getFiles())
                ->filter(function (SplFileInfo $file) {
                    return $file->getExtension() === 'json';
                })
                ->each(function (SplFileInfo $file) use ($prune, $used, &$changes) {
                    [$content, $removed] = $this->removeUnusedKeys($this->decodeFile($file), $used);

                    if (blank($removed)) {
                        return;
                    }

                    if ($prune) {
                        $this->putContent($file, $content);
                    }

                    $changes[] = [
                        'unused' => true,
                        'check' => ! $prune,
                        'issues' => $removed,
                        'count' => count($removed),
                        'file' => $file->getRealPath(),
                    ];
                });
        }

        return $changes;
    }

    /**
     * Remembers the keys used in code for the current language path.
     */
    private function rememberUsedKeys(array $keys): void
    {
        $langPath = $this->config['base_path'].'/'.$this->config['lang_path'];

        $langPath = realpath($langPath) ?: $langPath;

        $this->usedKeys[$langPath] = [
            'config' => $this->usedKeys[$langPath]['config'] ?? $this->config,
            'keys' => array_merge($this->usedKeys[$langPath]['keys'] ?? [], $keys),
        ];
    }

    /**
     * Removes the keys that are not used, returning the content and the removed keys.
     */
    private function removeUnusedKeys(array $content, array $used, string $prefix = ''): array
    {
        $removed = [];

        foreach ($content as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (isset($used[$fullKey])) {
                continue;
            }

            if (is_array($value) && filled($value)) {
                [$content[$key], $children] = $this->removeUnusedKeys($value, $used, $fullKey);

                $removed = array_merge($removed, $children);

                if (blank($content[$key])) {
                    unset($content[$key]);
                }

                continue;
            }

            unset($content[$key]);

            $removed[] = $fullKey;
        }

        return [$content, $removed];
    }

    /**
     * Get files to scan based on configuration.
     */
    private function getFilesToScan(): Collection
    {
        return collect($this->config['paths'])
            ->map(function ($path) {
                $fullPath = $this->config['base_path'].'/'.$path;

                return rescue(fn () => File::allFiles($fullPath), [], false);
            })
            ->collapse()
            ->filter(function (SplFileInfo $file) {
                return Str::endsWith($file->getFilename(), $this->config['extensions']);
            })
            ->filter(function (SplFileInfo $file) {
                return Str::startsWith($file->getPathname(), $this->paths);
            })
            ->values();
    }

    /**
     * Extract translation keys from a file based on config methods.
     */
    private function extractTranslationKeysFromFile(SplFileInfo $file): array
    {
        $content = $file->getContents();

        return collect($this->config['methods'])->map(function ($method) {
            $pattern = explode('*', $method);

            $end = preg_quote(data_get($pattern, 1, ''), '/');
            $start = preg_quote(data_get($pattern, 0, ''), '/');

            return $end === ''
                ? "/(?<![A-Za-z0-9_]){$start}\\s*((['\"])((?:\\\\.|(?!\\2).)*?)\\2)/s"
                : "/(?<![A-Za-z0-9_]){$start}\\s*((['\"])((?:\\\\.|(?!\\2).)*?)\\2).*?{$end}/s";
        })->flatMap(function ($pattern) use ($content) {
            preg_match_all($pattern, $content, $matches);

            return $matches[3] ?? [];
        })->map(function ($match) {
            return $this->unescape($match);
        })->filter(fn ($match) => $match !== '')->unique()->values()->toArray();
    }

    /**
     * Removes the escaping backslashes from a quoted string content.
     */
    private function unescape(string $content): string
    {
        return preg_replace('/\\\\([\\\\\'"])/', '$1', $content);
    }

    /**
     * Determine if the given key should be expanded into a nested structure.
     */
    private function isNestedKey(string $key): bool
    {
        return preg_match('/^[^\s.]+(\.[^\s.]+)*$/', $key) === 1;
    }

    /**
     * Add new translations to the translation file.
     */
    private function addNewTranslations(array $new): void
    {
        $files = $this->getFiles();

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($new) {
                $old = $this->decodeFile($file);

                [$merged, $diff] = $this->diffTranslations($old, $new);

                $this->putContent($file, $merged);

                $this->changes[] = [
                    'count' => count($diff),
                    'file' => $file->getRealPath(),
                    'issues' => array_values($diff),
                ];
            });
    }

    /**
     * Return new translations based on collected keys.
     */
    private function returnNewTranslations(array $translations, array $collectedKeys): array
    {
        $newEntries = collect($collectedKeys)
            ->reject(fn ($key) => Arr::has($translations, $key))
            ->partition(fn ($key) => $this->isNestedKey($key))
            ->pipe(function ($partitions) {
                [$nested, $literal] = $partitions;

                $undotted = $nested
                    ->mapWithKeys(fn ($key) => [$key => ''])
                    ->undot();

                $literal = $literal
                    ->mapWithKeys(fn ($key) => [$key => '']);

                return $undotted->toArray() + $literal->toArray();
            });

        return array_replace_recursive($translations, $newEntries);
    }
}
