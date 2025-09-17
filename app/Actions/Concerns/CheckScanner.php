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

        $translations = $this->getTranslations();

        $this->checkTranslations($translations);

        return [$this->totalFiles, $this->changes];
    }

    protected function noUpdate(): bool
    {
        return $this->config['no-update'] ?? $this->input->getOption('no-update');
    }

    /**
     * Get the current translations from a file.
     */
    private function currentTranslations(array $content): array
    {
        return collect($content)->dot()->when(
            $this->input->getOption('no-empty'),
            fn ($collection) => $collection->filter(fn ($value) => filled($value))
        )->keys()->toArray();
    }

    /**
     * Check translations for any issues.
     */
    private function checkTranslations(array $translations): void
    {
        $files = $this->getFiles();

        collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) use ($translations) {
                $this->totalFiles++;

                $content = json_decode($file->getContents(), true);

                $diff = array_diff(
                    collect($translations)->dot()->keys()->toArray(),
                    $this->currentTranslations($content),
                );

                if (! $this->noUpdate()) {
                    $this->putContent($file, $content);
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
