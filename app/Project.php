<?php

namespace App;

use App\Contracts\PathsRepository;
use Symfony\Component\Console\Input\InputInterface;

class Project
{
    /**
     * Determine the project paths to scan based on the options and arguments passed.
     */
    public static function paths(InputInterface $input): array
    {
        if ($input->getOption('dirty')) {
            return static::resolveDirtyPaths();
        }

        if ($diff = $input->getOption('diff')) {
            return static::resolveDiffPaths($diff);
        }

        return [static::path()];
    }

    /**
     * The project being analysed path.
     */
    public static function path(): string
    {
        return getcwd();
    }

    /**
     * Resolves the dirty paths, if any.
     */
    public static function resolveDirtyPaths(): array
    {
        $files = app(PathsRepository::class)->dirty();

        if (empty($files)) {
            abort(0, 'No dirty files found.');
        }

        return $files;
    }

    /**
     * Resolves the paths that have changed since branching off from the given branch, if any.
     */
    public static function resolveDiffPaths(string $branch): array
    {
        $files = app(PathsRepository::class)->diff($branch);

        if (empty($files)) {
            abort(0, "No files have changed since branching off of {$branch}.");
        }

        return $files;
    }
}
