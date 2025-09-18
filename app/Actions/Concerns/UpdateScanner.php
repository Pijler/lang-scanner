<?php

namespace App\Actions\Concerns;

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
        protected array $paths,
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
     * Merges old and new translations.
     */
    private function mergeTranslations(array $old, array $new): array
    {
        $merged = array_replace_recursive($new, $old);

        $diff = collect($merged)->dot()->keys()->diff(
            collect($old)->dot()->keys()
        )->values()->toArray();

        return [$merged, $diff];
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
            boolean: isset($this->config['base_path'], $this->config['lang_path']),
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

            return $end !== '' ? "/{$start}(.*?){$end}/s" : "/{$start}.*?['\"](.*?)['\"]/s";
        })->flatMap(function ($pattern) use ($content) {
            preg_match_all($pattern, $content, $matches);

            return $matches[1] ?? [];
        })->filter()->map(function ($match) {
            $match = trim($match);

            $this->extractQuotedString($match);

            return trim($match, " \t\n\r\0\x0B'\"");
        })->filter()->unique()->values()->toArray();
    }

    /**
     * Extracts the "best" quoted string from a match.
     */
    private function extractQuotedString(string &$match): void
    {
        if (! preg_match_all('/([\'"])((?:\\\\.|(?!\1).)*?)\1/s', $match, $all, PREG_SET_ORDER)) {
            return;
        }

        $best = collect($all)->reduce(function (?string $carry, array $m) {
            $content = $m[2];

            if ($content === '') {
                return $carry;
            }

            if ($carry === null || strlen($content) > strlen($carry)) {
                return $content;
            }

            return $carry;
        });

        if (filled($best)) {
            $match = stripcslashes($best);
        }
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
                $old = json_decode($file->getContents(), true);

                [$newTranslations, $diff] = $this->mergeTranslations($old, $new);

                $this->putContent($file, $newTranslations);

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
            ->map(fn ($key) => stripslashes($key))
            ->reject(fn ($key) => Arr::has($translations, $key))
            ->partition(fn ($key) => ! str_contains($key, ' '))
            ->pipe(function ($partitions) {
                [$noSpaces, $withSpaces] = $partitions;

                $undotted = $noSpaces
                    ->mapWithKeys(fn ($key) => [$key => ''])
                    ->undot();

                $spaced = $withSpaces
                    ->mapWithKeys(fn ($key) => [$key => '']);

                return $undotted->toArray() + $spaced->toArray();
            });

        return array_replace_recursive($translations, $newEntries);
    }
}
