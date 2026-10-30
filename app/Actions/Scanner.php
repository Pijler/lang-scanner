<?php

namespace App\Actions;

use App\Actions\Base\CheckScanner;
use App\Actions\Base\DuplicateScanner;
use App\Actions\Base\MergeScanner;
use App\Actions\Base\UnusedScanner;
use App\Actions\Base\UpdateScanner;
use App\Actions\Concerns\RecursiveConfigs;
use App\Output\ProgressOutput;
use App\Project;
use App\Validators\ConfigurationValidator;
use Illuminate\Support\Collection;
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
     * The Unused Scanner instance.
     */
    protected UnusedScanner $unused;

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
        $this->unused = resolve(UnusedScanner::class)->setPaths($this->paths);
        $this->duplicate = resolve(DuplicateScanner::class)->setPaths($this->paths);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $configs = collect($this->getConfigs())->map(function (array $config) {
            $mode = match (true) {
                $this->merge() => 'merge',
                $this->check($config) => 'check',
                $this->duplicate() => 'duplicate',
                $this->unused() => 'unused',
                default => 'update',
            };

            ConfigurationValidator::validateMode($config, $mode);

            return [$mode, $config];
        });

        if ($this->unused()) {
            $configs = $this->groupUnused($configs);
        }

        $configs->each(function (array $item) {
            [$mode, $config] = $item;

            $this->runScanner($mode, $config);
        });

        return [
            collect($this->stats)->pluck('files')->sum(),
            collect($this->stats)->pluck('changes')->collapse()->toArray(),
        ];
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
     * Checks if the scanner is in merge mode.
     */
    private function merge(): bool
    {
        return $this->input->getOption('merge');
    }

    /**
     * Checks if the scanner is in duplicate mode.
     */
    private function duplicate(): bool
    {
        return $this->input->getOption('duplicate');
    }

    /**
     * Checks if the scanner is in check-only mode.
     */
    private function check(array $config): bool
    {
        return $config['check'] ?? $this->input->getOption('check');
    }

    /**
     * Checks if the unused translations should be reported.
     */
    private function unused(): bool
    {
        return $this->input->getOption('prune') || $this->input->getOption('unused');
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
     * Groups the unused configs by language path, so a key used by any of them is kept.
     */
    private function groupUnused(Collection $configs): Collection
    {
        $this->ensureFullScan();

        return $configs
            ->filter(function (array $item) {
                return $item[0] === 'unused';
            })
            ->groupBy(function (array $item) {
                $path = $item[1]['base_path'].'/'.$item[1]['lang_path'];

                return realpath($path) ?: $path;
            })
            ->map(function (Collection $items) {
                return ['unused', $items->pluck(1)->all()];
            })
            ->values();
    }

    /**
     * Run the correct scanner (check/merge/update/unused/duplicate).
     */
    private function runScanner(string $mode, array $config): void
    {
        [$files, $changes] = match ($mode) {
            'check' => $this->check->execute($config),
            'merge' => $this->merge->execute($config),
            'update' => $this->update->execute($config),
            'unused' => $this->unused->execute($config),
            'duplicate' => $this->duplicate->execute($config),
        };

        data_set($this->stats, "{$mode}.files", $files);
        data_set($this->stats, "{$mode}.changes", $changes);
    }
}
