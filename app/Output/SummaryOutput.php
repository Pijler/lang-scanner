<?php

namespace App\Output;

use App\Output\Concerns\InteractsWithSymbols;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryOutput
{
    use InteractsWithSymbols;

    /**
     * Creates a new Summary Output instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {}

    /**
     * Handle the given report summary.
     */
    public function handle($summary, int $totalFiles): void
    {
        dd($this);
    }
}
