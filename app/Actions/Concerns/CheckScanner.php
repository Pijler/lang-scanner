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
        $translations = $this->getTranslations();

        $this->checkTranslations($translations);

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Checks if the translations should be sorted.
     */
    private function sorted(): bool
    {
        return $this->input->getOption('sort')
            || (isset($this->config['sort']) && $this->config['sort']);
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
    private function checkTranslations(array $translations): void
    {
        $sort = $this->sorted();

        $files = $this->getFiles();

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($sort, $translations) {
                $this->totalFiles++;

                $diff = array_diff(
                    collect($translations)->dot()->keys()->toArray(),
                    $this->currentTranslations($file),
                );

                if ($sort) {
                    $content = json_decode($file->getContents(), true);

                    $this->putContent($file, $this->sortRecursive($content));
                }

                $this->progressOutput->handle(blank($diff) ? Status::SKIPPED : Status::ERROR);

                $this->changes[] = [
                    'check' => true,
                    'count' => count($diff),
                    'file' => $file->getRealPath(),
                    'issues' => array_values($diff),
                ];
            });
    }
}
