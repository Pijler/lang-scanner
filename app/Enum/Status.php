<?php

namespace App\Enum;

enum Status: string
{
    case OK = 'ok';
    case ERROR = 'error';

    /**
     * Gets the symbol and format for the status.
     */
    public function symbol(): array
    {
        return match ($this) {
            self::OK => ['symbol' => '.', 'format' => '<fg=gray>%s</>'],
            self::ERROR => ['symbol' => '⨯', 'format' => '<options=bold;fg=red>%s</>'],
        };
    }
}
