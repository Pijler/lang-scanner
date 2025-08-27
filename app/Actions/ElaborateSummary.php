<?php

namespace App\Actions;

use App\Output\SummaryOutput;
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
    ) {
        //
    }

    /**
     * Elaborates the summary of the given changes.
     */
    public function execute(int $totalFiles, array $changes): int
    {
        dd($this);
    }
}
