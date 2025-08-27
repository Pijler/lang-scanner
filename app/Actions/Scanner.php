<?php

namespace App\Actions;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Scanner
{
    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
        //
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        dd($this);
    }
}
