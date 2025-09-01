<?php

namespace App\Enum;

enum Status: string
{
    case OK = 'ok';
    case ERROR = 'error';
    case SKIPPED = 'skipped';

    /**
     * Gets the symbol and format for the status.
     */
    public function symbol(): array
    {
        return match ($this) {
            self::SKIPPED => [
                'symbol' => '.',
                'color' => 'gray',
                'format' => '<fg=gray>%s</>',
            ],
            self::OK => [
                'symbol' => '✓',
                'color' => 'green',
                'format' => '<options=bold;fg=green>%s</>',
            ],
            self::ERROR => [
                'symbol' => '⨯',
                'color' => 'red',
                'format' => '<options=bold;fg=red>%s</>',
            ],
        };
    }
}
