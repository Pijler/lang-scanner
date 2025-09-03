<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;

class ConfigurationJsonRepository
{
    /**
     * The default configuration values.
     */
    protected const array DEFAULT = [
        'extends' => [],
        'scanner' => [
            [
                'lang_path' => 'lang/',
                'paths' => [
                    'app/',
                    'resources/',
                ],
                'extensions' => [
                    '.php',
                    '.html',
                ],
                'methods' => [
                    '__',
                    'trans',
                    'trans_choice',
                ],
            ],
        ],
    ];

    /**
     * Create a new Configuration Json Repository instance.
     */
    public function __construct(
        protected string $path,
    ) {}

    /**
     * Get the scanner configuration.
     */
    public function scanner(): array
    {
        return data_get($this->get(), 'scanner', []);
    }

    /**
     * Get the file extends to scan.
     */
    public function extends(): array
    {
        return data_get($this->get(), 'extends', []);
    }

    /**
     * Get the configuration from the "scanner.json" file.
     */
    protected function get(): array
    {
        if (! is_null($this->path) && File::exists($this->path)) {
            $baseConfig = json_decode(File::get($this->path), true);

            return tap($baseConfig, function ($configuration) {
                if (! is_array($configuration)) {
                    abort(1, sprintf('The configuration file [%s] is not valid JSON.', $this->path));
                }
            });
        }

        return self::DEFAULT;
    }
}
