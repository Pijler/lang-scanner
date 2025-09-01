<?php

namespace App\Actions\Concerns;

use App\Enum\Status;
use App\Output\ProgressOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class CheckScanner
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
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {}

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(array $config): array
    {
        $translations = $this->getTranslations($config);

        $this->checkTranslations($config, $translations);

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Get the current translations from a file.
     */
    private function currentTranslations(SplFileInfo $file): array
    {
        $current = json_decode($file->getContents(), true);

        return collect($current)->dot()->when(
            $this->input->getOption('no-empty'),
            fn ($collection) => $collection->filter(fn ($value) => filled($value))
        )->keys()->toArray();
    }

    /**
     * Check translations for any issues.
     */
    private function checkTranslations(array $config, array $translations): void
    {
        $files = $this->getFiles($config);

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($translations) {
                $this->totalFiles++;

                $diff = array_diff(
                    collect($translations)->dot()->keys()->toArray(),
                    $this->currentTranslations($file),
                );

                $this->progressOutput->handle(blank($diff) ? Status::SKIPPED : Status::ERROR);

                $this->changes[] = [
                    'count' => count($diff),
                    'file' => $file->getRealPath(),
                    'issues' => array_values($diff),
                ];
            });
    }
}
