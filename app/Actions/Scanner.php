<?php

namespace App\Actions;

use App\Actions\Base\CheckScanner;
use App\Actions\Base\DuplicateScanner;
use App\Actions\Base\MergeScanner;
use App\Actions\Base\UpdateScanner;
use App\Actions\Concerns\RecursiveConfigs;
use App\Output\ProgressOutput;
use App\Project;
use App\Repositories\CacheRepository;
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
        $this->check = resolve(CheckScanner::class);
        $this->merge = resolve(MergeScanner::class);
        $this->update = resolve(UpdateScanner::class);
        $this->duplicate = resolve(DuplicateScanner::class);

        $this->check->setPaths($this->paths);
        $this->merge->setPaths($this->paths);
        $this->update->setPaths($this->paths);
        $this->duplicate->setPaths($this->paths);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $configs = $this->getConfigs();

        collect($configs)->each(function (array $config) {
            CacheRepository::initializeCache($config);

            $mode = match (true) {
                $this->merged($config) => 'merge',
                $this->checked($config) => 'check',
                $this->duplicated($config) => 'duplicate',
                default => 'update',
            };

            $this->runScanner($mode, $config);
        });

        return [
            collect($this->stats)->pluck('files')->sum(),
            collect($this->stats)->pluck('changes')->collapse()->toArray(),
        ];
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
            'merge' => $this->merge->execute($config),
            'update' => $this->update->execute($config),
            'duplicate' => $this->duplicate->execute($config),
        };

        data_set($this->stats, "{$mode}.files", $files);
        data_set($this->stats, "{$mode}.changes", $changes);
    }
}
