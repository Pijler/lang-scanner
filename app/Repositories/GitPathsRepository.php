<?php

namespace App\Repositories;

use App\Contracts\PathsRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class GitPathsRepository implements PathsRepository
{
    /**
     * Creates a new Paths Repository instance.
     */
    public function __construct(
        protected string $path,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function dirty(): array
    {
        $process = tap(new Process(['git', 'status', '--short']))->run();

        if (! $process->isSuccessful()) {
            abort(1, 'The [--dirty] option is only available when using Git.');
        }

        $dirtyFiles = collect(preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file) => [substr($file, 3) => trim(substr($file, 0, 3))])
            ->reject(fn ($status) => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->values();

        return $this->processFileNames($dirtyFiles);
    }

    /**
     * {@inheritDoc}
     */
    public function diff(string $branch): array
    {
        $files = [
            'unstaged' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM']))->run(),
            'untracked' => tap(new Process(['git', 'ls-files', '--others', '--exclude-standard']))->run(),
            'staged' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM', '--cached']))->run(),
            'committed' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM', "{$branch}...HEAD"]))->run(),
        ];

        $files = collect($files)->each(function (Process $process) {
            return abort_if(
                code: 1,
                boolean: ! $process->isSuccessful(),
                message: 'The [--diff] option is only available when using Git.',
            );
        })->map(function (Process $process) {
            return preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY);
        })->flatten()->unique()->values()->map(function ($s) {
            return (string) $s;
        });

        return $this->processFileNames($files);
    }

    /**
     * Process the files.
     */
    protected function processFileNames(Collection $fileNames): array
    {
        return $fileNames->map(function ($file) {
            if (PHP_OS_FAMILY === 'Windows') {
                $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
            }

            return $this->path.DIRECTORY_SEPARATOR.$file;
        })->all();
    }
}
