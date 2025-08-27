<?php

namespace App\Enum;

enum Status: string
{
    case OK = 'ok';
    case ERROR = 'error';
    case SKIPPED = 'skipped';

    /**
     * Gets the color for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::OK => 'green',
            self::ERROR => 'red',
            self::SKIPPED => 'gray',
        };
    }

    /**
     * Gets the symbol and format for the status.
     */
    public function symbol(): array
    {
        return match ($this) {
            self::SKIPPED => ['symbol' => '.', 'format' => '<fg=gray>%s</>'],
            self::OK => ['symbol' => '✓', 'format' => '<options=bold;fg=green>%s</>'],
            self::ERROR => ['symbol' => '⨯', 'format' => '<options=bold;fg=red>%s</>'],
        };
    }
}
