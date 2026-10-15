<?php

namespace App\Repositories;

use App\Contracts\PathsRepository;
use Illuminate\Support\Collection;
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
        $process = $this->run(['git', 'status', '--porcelain', '-z']);

        if (! $process->isSuccessful()) {
            abort(1, 'The [--dirty] option is only available when using Git.');
        }

        $entries = explode("\0", $process->getOutput());

        $dirtyFiles = collect();

        while (($entry = array_shift($entries)) !== null) {
            if (strlen($entry) < 4) {
                continue;
            }

            $status = substr($entry, 0, 2);

            // Renamed and copied entries are followed by their origin path.
            if (str_contains($status, 'R') || str_contains($status, 'C')) {
                array_shift($entries);
            }

            if (! str_contains($status, 'D')) {
                $dirtyFiles->push(substr($entry, 3));
            }
        }

        return $this->processFileNames($dirtyFiles);
    }

    /**
     * {@inheritDoc}
     */
    public function diff(string $branch): array
    {
        $files = [
            'unstaged' => $this->run(['git', 'diff', '--name-only', '-z', '--diff-filter=AM']),
            'untracked' => $this->run(['git', 'ls-files', '-z', '--full-name', '--others', '--exclude-standard']),
            'staged' => $this->run(['git', 'diff', '--name-only', '-z', '--diff-filter=AM', '--cached']),
            'committed' => $this->run(['git', 'diff', '--name-only', '-z', '--diff-filter=AM', "{$branch}...HEAD"]),
        ];

        $files = collect($files)->each(function (Process $process) {
            return abort_if(
                code: 1,
                boolean: ! $process->isSuccessful(),
                message: 'The [--diff] option is only available when using Git.',
            );
        })->map(function (Process $process) {
            return explode("\0", $process->getOutput());
        })->flatten()->filter()->unique()->values()->map(function ($s) {
            return (string) $s;
        });

        return $this->processFileNames($files);
    }

    /**
     * Runs the given Git command in the project path.
     */
    protected function run(array $command): Process
    {
        return tap(new Process($command, $this->path))->run();
    }

    /**
     * Get the root directory of the Git repository.
     */
    protected function root(): string
    {
        $process = $this->run(['git', 'rev-parse', '--show-toplevel']);

        return trim($process->getOutput()) ?: $this->path;
    }

    /**
     * Process the files, which are relative to the repository root.
     */
    protected function processFileNames(Collection $fileNames): array
    {
        $root = $this->root();

        return $fileNames->map(function ($file) use ($root) {
            $path = $root.'/'.$file;

            if (PHP_OS_FAMILY === 'Windows') {
                $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            }

            return $path;
        })->all();
    }
}
