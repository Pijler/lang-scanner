<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Output\ProgressOutput;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
        $translations = $this->getTranslations();

        $collectedKeys = $this->collectKeys($files);

        $newTranslations = $this->returnNewTranslations($translations, $collectedKeys);

        if (filled($newTranslations)) {
            $this->addNewTranslations($newTranslations);
        }
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
