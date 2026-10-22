<?php

namespace App\Actions;

use App\Actions\Base\CheckScanner;
use App\Actions\Base\DuplicateScanner;
use App\Actions\Base\MergeScanner;
use App\Actions\Base\UpdateScanner;
use App\Actions\Concerns\RecursiveConfigs;
use App\Output\ProgressOutput;
use App\Project;
use App\Validators\ConfigurationValidator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Scanner
{
    /**
     * The Check Scanner instance.
     */
    protected CheckScanner $check;

    /**
     * The Merge Scanner instance.
     */
    protected MergeScanner $merge;

    /**
     * The Update Scanner instance.
     */
    protected UpdateScanner $update;

    /**
     * The Duplicate Scanner instance.
     */
    protected DuplicateScanner $duplicate;

    /**
     * The stats of scanned files and changes.
     */
    protected array $stats = [
        'check' => ['files' => 0, 'changes' => []],
        'merge' => ['files' => 0, 'changes' => []],
        'update' => ['files' => 0, 'changes' => []],
        'unused' => ['files' => 0, 'changes' => []],
        'duplicate' => ['files' => 0, 'changes' => []],
    ];

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected array $paths,
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {
        $this->check = resolve(CheckScanner::class)->setPaths($this->paths);
        $this->merge = resolve(MergeScanner::class)->setPaths($this->paths);
        $this->update = resolve(UpdateScanner::class)->setPaths($this->paths);
        $this->duplicate = resolve(DuplicateScanner::class)->setPaths($this->paths);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $configs = collect($this->getConfigs())->map(function (array $config) {
            $mode = match (true) {
                $this->merged($config) => 'merge',
                $this->checked($config) => 'check',
                $this->duplicated($config) => 'duplicate',
                default => 'update',
            };

            ConfigurationValidator::validateMode($config, $mode);

            return [$mode, $config];
        });

        if ($this->unused()) {
            $this->ensureFullScan();
        }

        $configs->each(function (array $item) {
            [$mode, $config] = $item;

            $this->runScanner($mode, $config);
        });

        if ($this->unused()) {
            data_set($this->stats, 'unused.changes', $this->update->unused($this->prune()));
        }

        return [
            collect($this->stats)->pluck('files')->sum(),
            collect($this->stats)->pluck('changes')->collapse()->toArray(),
        ];
    }

    /**
     * Checks if the unused translations should be reported.
     */
    private function unused(): bool
    {
        return $this->input->getOption('unused') || $this->prune();
    }

    /**
     * Checks if the unused translations should be removed.
     */
    private function prune(): bool
    {
        return $this->input->getOption('prune');
    }

    /**
     * Ensures that the whole project is scanned, otherwise used keys would be missed.
     */
    private function ensureFullScan(): void
    {
        abort_if(
            code: 1,
            message: 'The [--unused] and [--prune] options cannot be combined with [--dirty], [--diff] or paths.',
            boolean: $this->input->getOption('dirty') || filled($this->input->getOption('diff')) || filled($this->input->getArgument('path')),
        );
    }

    /**
     * Checks if the scanner is in merged mode.
     */
    private function merged(array $config): bool
    {
        return $config['merge'] ?? $this->input->getOption('merge');
    }

    /**
     * Checks if the scanner is in check-only mode.
     */
    private function checked(array $config): bool
    {
        return $config['check'] ?? $this->input->getOption('check');
    }

    /**
     * Checks if the scanner is in duplicated mode.
     */
    private function duplicated(array $config): bool
    {
        return $config['duplicate'] ?? $this->input->getOption('duplicate');
    }

    /**
     * Get the configuration files to scan.
     */
    private function getConfigs(): array
    {
        $path = $this->input->getOption('config');

        if (filled($path)) {
            $path = realpath($path) ?: abort(1, "The configuration file [{$path}] does not exist.");
        }

        $path = $path ?: Project::path().'/scanner.json';

        return (new RecursiveConfigs($path))->execute();
    }

    /**
     * Run the correct scanner (check/update).
     */
    private function runScanner(string $mode, array $config): void
    {
        [$files, $changes] = match ($mode) {
            'check' => $this->check->execute($config),
            'merge' => $this->merge->execute($config),
            'update' => $this->update->execute($config),
            'duplicate' => $this->duplicate->execute($config),
        };

        data_set($this->stats, "{$mode}.files", $files);
        data_set($this->stats, "{$mode}.changes", $changes);
    }
}
