<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Enum\Status;
use App\Output\ProgressOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class MergeScanner
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

        $translations = $this->getTranslations();

        $this->addNewTranslations($translations);

        return [$this->totalFiles, $this->changes];
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
                $this->totalFiles++;

                $old = json_decode($file->getContents(), true);

                [$merged, $diff] = $this->diffTranslations($old, $new);

                $this->putContent($file, $merged);

                $this->progressOutput->handle(blank($diff) ? Status::SKIPPED : Status::ERROR);

                $this->changes[] = [
                    'count' => count($diff),
                    'file' => $file->getRealPath(),
                    'issues' => array_values($diff),
                ];
            });
    }
}
