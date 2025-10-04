<?php

namespace App\Actions\Concerns;

use App\Repositories\ConfigurationJsonRepository;

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

            return (new RecursiveConfigs($path))->execute();
        })->toArray();
    }
}
