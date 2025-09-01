<?php

namespace App\Actions\Concerns;

use App\Repositories\ConfigurationJsonRepository;
use Illuminate\Support\Str;

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
        $extensions = $this->getExtensions();

        return array_merge($scanner, ...$extensions);
    }

    /**
     * Get the base path for the configuration files.
     */
    private function getBasePath(): string
    {
        return Str::remove('/scanner.json', $this->path);
    }

    /**
     * Get the scanner configuration.
     */
    private function getScanner(): array
    {
        $scanner = $this->repository->scanner();

        return collect($scanner)->map(function (array $config) {
            return array_merge($config, [
                'base_path' => $this->getBasePath(),
            ]);
        })->toArray();
    }

    /**
     * Get the extensions configuration.
     */
    private function getExtensions(): array
    {
        $extensions = $this->repository->extensions();

        return collect($extensions)->map(function (string $extension) {
            $path = $this->getBasePath().'/'.$extension;

            return (new RecursiveConfigs($path))->execute();
        })->toArray();
    }
}
