<?php

namespace App\Output\Concerns;

use App\Enum\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property InputInterface $input
 * @property OutputInterface $output
 */
trait InteractsWithSymbols
{
    /**
     * Gets the symbol for the given status.
     */
    public function getSymbol(Status $status): string
    {
        $statusSymbol = $status->symbol();

        if ($this->output->isDecorated()) {
            return sprintf($statusSymbol['format'], (string) $statusSymbol['symbol']);
        }

        return (string) $statusSymbol['symbol'];
    }
}
