<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Enum\Status;
use App\Output\ProgressOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class DuplicateScanner
{
    use BaseMethods;

    /**
     * The translation array.
     */
    protected static array $cache = [];

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

        $translations = $this->getTranslations();

        $this->duplicateTranslations($translations);

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Determine if we should remove duplicate entries.
     */
    protected function remove(): bool
    {
        return $this->input->getOption('remove');
    }

    /**
     * Find duplicate entries based on cached translations.
     */
    private function getDuplicates(array $current, string $cacheKey): array
    {
        $cached = data_get(static::$cache, $cacheKey, []);

        $duplicates = collect($current)->dot()->keys()->intersect(
            collect($cached)->dot()->keys()
        )->values()->toArray();

        $result = collect($current)->except($duplicates)->all();

        return [$result, $duplicates];
    }

    /**
     * Check translations for any issues.
     */
    private function duplicateTranslations(array $new): void
    {
        $files = $this->getFiles();

        $cacheKey = md5(json_encode(data_get($this->config, 'paths', [])));

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($new, $cacheKey) {
                $this->totalFiles++;

                $old = json_decode($file->getContents(), true);

                [$merged, $diff] = $this->diffTranslations($old, $new);

                [$result, $duplicates] = $this->getDuplicates($merged, $cacheKey);

                $this->putContent($file, $this->remove() ? $result : $merged);

                $status = match (true) {
                    filled($diff) || filled($duplicates) => Status::ERROR,
                    blank($diff) && blank($duplicates) => Status::SKIPPED,
                    default => Status::OK,
                };

                $this->progressOutput->handle($status);

                $this->changes[] = [
                    'file' => $file->getRealPath(),
                    'count' => count($diff) + count($duplicates),
                    'issues' => array_values($diff) + array_values($duplicates),
                ];

                data_set(static::$cache, $cacheKey, $merged);
            });
    }
}
