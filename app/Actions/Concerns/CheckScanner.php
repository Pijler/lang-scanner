<?php

namespace App\Actions\Concerns;

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
                $current = json_decode($file->getContents(), true);

                $diffQuantity = count(array_diff(
                    collect($translations)->dot()->keys()->toArray(),
                    collect($current)->dot()->keys()->toArray(),
                ));

                $this->changes[] = [
                    'quantity' => $diffQuantity,
                    'file' => $file->getRealPath(),
                ];
            });
    }
}
