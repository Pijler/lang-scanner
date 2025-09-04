<?php

namespace App\Actions;

use App\Actions\Concerns\CheckScanner;
use App\Actions\Concerns\RecursiveConfigs;
use App\Actions\Concerns\UpdateScanner;
use App\Output\ProgressOutput;
use App\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Scanner
{
    /**
     * The total number of files scanned.
     */
    protected int $totalFiles = 0;

    /**
     * The changes made during the scan.
     */
    protected array $changes = [];

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
    public function execute(): array
    {
        $configs = $this->getConfigs();

        collect($configs)->each(function (array $config) {
            $this->checked($config)
                ? $this->checkScanner($config)
                : $this->updateScanner($config);
        });

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Checks if the scanner is in check-only mode.
     */
    private function checked(array $config): bool
    {
        return $this->config['check'] ?? $this->input->getOption('check');
    }

    /**
     * Get the configuration files to scan.
     */
    private function getConfigs(): array
    {
        $path = $this->input->getOption('config') ?: Project::path().'/scanner.json';

        return (new RecursiveConfigs($path))->execute();
    }

    /**
     * Check the project for translation issues.
     */
    private function checkScanner(array $config): void
    {
        [$totalFiles, $changes] = resolve(CheckScanner::class)->execute($config);

        $this->changes = $changes;
        $this->totalFiles = $totalFiles;
    }

    /**
     * Update the project with new translations.
     */
    private function updateScanner(array $config): void
    {
        [$totalFiles, $changes] = resolve(UpdateScanner::class)->execute($config);

        $this->changes = $changes;
        $this->totalFiles = $totalFiles;
    }
}
