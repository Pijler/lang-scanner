<?php

namespace App\Actions\Concerns;

use App\Enum\Status;
use App\Project;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property array $paths
 * @property array $changes
 * @property int $totalFiles
 * @property InputInterface $input
 * @property OutputInterface $output
 * @property ProgressOutput $progressOutput
 */
trait CheckScanner
{
    /**
     * Checks translation file for any issues.
     */
    protected function checkScanner(array $config): void
    {
        $translations = $this->getTranslations($config);

        $this->checkTranslations($config, $translations);
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
        $files = rescue(function () use ($config) {
            return File::allFiles(Project::path().'/'.$config['lang_path']);
        }, [], false);

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
