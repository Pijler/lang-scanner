<?php

namespace App\Actions\Concerns;

use App\Repositories\ConfigurationJsonRepository;
use Illuminate\Support\Facades\File;

class RecursiveConfigs
{
    /**
     * The configuration repository instance.
     */
    protected ConfigurationJsonRepository $repository;

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected string $path,
        protected array $visited = [],
    ) {
        $this->repository = new ConfigurationJsonRepository($path);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $scanner = $this->getScanner();
        $extends = $this->getExtends();

        return array_merge($scanner, ...$extends);
    }

    /**
     * Get the scanner configuration.
     */
    private function getScanner(): array
    {
        $scanner = $this->repository->scanner();

        return collect($scanner)->map(function (array $config) {
            return array_merge($config, [
                'base_path' => dirname($this->path),
            ]);
        })->toArray();
    }

    /**
     * Get the extends configuration.
     */
    private function getExtends(): array
    {
        $extends = $this->repository->extends();

        return collect($extends)->map(function (string $extension) {
            $path = dirname($this->path).'/'.trim($extension, '/');

            abort_unless(
                code: 1,
                boolean: File::exists($path),
                message: "The extended configuration file [{$path}] does not exist.",
            );

            $path = realpath($path);

            $visited = [...$this->visited, realpath($this->path) ?: $this->path];

            abort_if(
                code: 1,
                boolean: in_array($path, $visited, true),
                message: "Circular extends detected in configuration file [{$path}].",
            );

            return (new RecursiveConfigs($path, $visited))->execute();
        })->toArray();
    }
}
