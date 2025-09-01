<?php

namespace App\Commands;

use App\Actions\ElaborateSummary;
use App\Actions\Scanner;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputOption;

class DefaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'default';

    /**
     * The console command description.
     */
    protected $description = 'Scan files and update translations';

    /**
     * The configuration of the command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDefinition([
            new InputOption('sort', '', InputOption::VALUE_NONE, 'Sort the translations by key in check mode'),
            new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The configuration that should be used'),
            new InputOption('check', '', InputOption::VALUE_NONE, 'Check if all translations in same folder have the same keys, and the same order'),
            new InputOption('diff', '', InputOption::VALUE_REQUIRED, 'Only check files that have changed since branching off from the given branch', null, ['main', 'master', 'origin/main', 'origin/master']),
            new InputOption('dirty', '', InputOption::VALUE_NONE, 'Only check files that have uncommitted changes'),
            new InputOption('no-empty', '', InputOption::VALUE_NONE, 'Consider empty translation keys as invalid'),
        ]);
    }

    /**
     * Execute the console command.
     */
    public function handle(Scanner $scanner, ElaborateSummary $summary): int
    {
        [$totalFiles, $changes] = $scanner->execute();

        return $summary->execute($totalFiles, $changes);
    }
}
