<?php

namespace App\Actions;

use App\Output\SummaryOutput;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ElaborateSummary
{
    /**
     * Creates a new Elaborate Summary instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected SummaryOutput $summaryOutput,
    ) {}

    /**
     * Elaborates the summary of the given changes.
     */
    public function execute(int $totalFiles, array $changes): int
    {
        $count = $this->countIssues($changes);

        $this->summaryOutput->handle($totalFiles, $changes);

        return $count > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Counts the total number of issues in the changes.
     */
    private function countIssues(array $changes): int
    {
        return collect($changes)->sum(fn ($change) => $change['count']);
    }
}
