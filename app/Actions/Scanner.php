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
     * The stats of scanned files and changes.
     */
    protected array $stats = [
        'check' => ['files' => 0, 'changes' => []],
        'update' => ['files' => 0, 'changes' => []],
    ];

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected CheckScanner $check,
        protected UpdateScanner $update,
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
            $mode = $this->checked($config) ? 'check' : 'update';

            $this->runScanner($mode, $config);
        });

        return [
            collect($this->stats)->pluck('files')->sum(),
            collect($this->stats)->pluck('changes')->collapse()->toArray(),
        ];
    }

    /**
     * Checks if the scanner is in check-only mode.
     */
    private function checked(array $config): bool
    {
        return $config['check'] ?? $this->input->getOption('check');
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
     * Run the correct scanner (check/update).
     */
    private function runScanner(string $mode, array $config): void
    {
        [$files, $changes] = match ($mode) {
            'check' => $this->check->execute($config),
            'update' => $this->update->execute($config),
        };

        data_set($this->stats, "{$mode}.files", $files);
        data_set($this->stats, "{$mode}.changes", $changes);
    }
}
