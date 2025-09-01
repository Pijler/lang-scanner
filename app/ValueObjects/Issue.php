<?php

namespace App\ValueObjects;

use App\Enum\Status;

class Issue
{
    /**
     * Creates a new Issue instance.
     */
    public function __construct(
        protected int $count,
        protected bool $check,
        protected string $path,
        protected string $file,
        protected array $changes,
    ) {}

    /**
     * Returns the issue's count.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Returns the issue's color.
     */
    public function color(): string
    {
        return $this->status()->symbol()['color'];
    }

    /**
     * Returns the issue's symbol.
     */
    public function symbol(): string
    {
        return $this->status()->symbol()['symbol'];
    }

    /**
     * Returns the issue's description.
     */
    public function description(): string
    {
        return collect($this->changes)->implode(', ');
    }

    /**
     * Gets the status for the issue.
     */
    private function status(): Status
    {
        return $this->check ? Status::ERROR : Status::OK;
    }

    /**
     * Returns the file where the change occur.
     */
    public function file(): string
    {
        return str_replace($this->path.DIRECTORY_SEPARATOR, '', $this->file);
    }
}
