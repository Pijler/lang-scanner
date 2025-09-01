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
     * The changes made during the scan.
     */
    protected array $changes = [];

    /**
     * The total number of files scanned.
     */
    protected int $totalFiles = 0;

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected array $paths,
        protected array $config,
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {}

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $collectedKeys = [];

        $files = $this->getFilesToScan();

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

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Merges old and new translations.
     */
    private function mergeTranslations(array $old, array $new): array
    {
        $newTranslations = array_replace_recursive($new, $old);

        $diff = array_diff(
            collect($newTranslations)->dot()->keys()->toArray(),
            collect($old)->dot()->keys()->toArray(),
        );

        return [$newTranslations, $diff];
    }

    /**
     * Get files to scan based on configuration.
     */
    private function getFilesToScan(): Collection
    {
        abort_unless(isset($this->config['paths']), 'Paths are not set.');

        abort_unless(isset($this->config['extensions']), 'Extensions are not set.');

        return collect($this->config['paths'])
            ->map(function ($path) {
                $fullPath = $this->config['base_path'].'/'.$path;

                return File::exists($fullPath) ? File::allFiles($fullPath) : [];
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
        $keys = [];

        $content = $file->getContents();

        abort_unless(isset($this->config['methods']), 'Methods are not set in config.');

        $patterns = collect($this->config['methods'])->map(function ($method) {
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
        $all = collect($collectedKeys)
            ->filter(fn ($key) => ! Arr::has($translations, $key))
            ->keyBy(fn ($key) => $key)
            ->map(fn () => '')
            ->undot()
            ->toArray();

        return $this->sortRecursive(array_replace_recursive($translations, $all));
    }
}
