<?php

namespace App\Output;

use App\Enum\Status;
use App\Output\Concerns\InteractsWithSymbols;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class ProgressOutput
{
    use InteractsWithSymbols;

    /**
     * Holds the current number of processed files.
     */
    protected int $processed = 0;

    /**
     * Holds the number of symbols on the current terminal line.
     */
    protected int $symbolsPerLine = 0;

    /**
     * Creates a new Progress Output instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
        $this->symbolsPerLine = (new Terminal)->getWidth() - 4;
    }

    /**
     * Handle the given processed file event.
     */
    public function handle(Status $status): void
    {
        $symbolsOnCurrentLine = $this->processed % $this->symbolsPerLine;

        if ($symbolsOnCurrentLine >= (new Terminal)->getWidth() - 4) {
            $symbolsOnCurrentLine = 0;
        }

        if ($symbolsOnCurrentLine === 0) {
            $this->output->writeln('');
            $this->output->write('  ');
        }

        $this->output->write($this->getSymbol($status));

        $this->processed++;
    }
}
