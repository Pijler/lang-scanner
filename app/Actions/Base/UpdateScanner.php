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

        $newTranslations = $this->returnNewTranslations($translations, $collectedKeys);

        if (filled($newTranslations)) {
            $this->addNewTranslations($newTranslations);
        }
    }

    /**
     * Get files to scan based on configuration.
     */
    private function getFilesToScan(): Collection
    {
        abort_unless(
            code: 1,
            message: 'Extensions are not set.',
            boolean: isset($this->config['extensions']),
        );

        abort_unless(
            code: 1,
            message: 'Config paths are not set.',
            boolean: isset($this->config['base_path'], $this->config['paths']),
        );

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

        abort_unless(
            code: 1,
            boolean: isset($this->config['methods']),
            message: 'Methods are not set in config.',
        );

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
