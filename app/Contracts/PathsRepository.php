<?php

namespace App\Contracts;

interface PathsRepository
{
    /**
     * Determine the "dirty" files.
     */
    public function dirty(): array;

    /**
     * Determine the files that have changed since branching off from the given branch.
     */
    public function diff(string $branch): array;
}
