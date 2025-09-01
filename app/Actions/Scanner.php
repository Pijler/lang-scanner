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
     * The paths to scan.
     */
    protected array $paths;

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
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {
        $this->paths = Project::paths($input);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $configs = $this->getConfigs();

        collect($configs)->each(function (array $config) {
            $this->input->getOption('check')
                ? $this->checkScanner($config)
                : $this->updateScanner($config);
        });

        return [$this->totalFiles, $this->changes];
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
        [$totalFiles, $changes] = (new CheckScanner(
            input: $this->input,
            paths: $this->paths,
            output: $this->output,
            progressOutput: $this->progressOutput,
        ))->execute($config);

        $this->totalFiles += $totalFiles;
        $this->changes = array_merge($this->changes, $changes);
    }

    /**
     * Update the project with new translations.
     */
    private function updateScanner(array $config): void
    {
        [$totalFiles, $changes] = (new UpdateScanner(
            input: $this->input,
            paths: $this->paths,
            output: $this->output,
            progressOutput: $this->progressOutput,
        ))->execute($config);

        $this->totalFiles += $totalFiles;
        $this->changes = array_merge($this->changes, $changes);
    }
}
