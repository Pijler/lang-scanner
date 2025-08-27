<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConfigurationJsonRepository
{
    /**
     * The default configuration values.
     */
    protected const array DEFAULT = [
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
    ];

    /**
     * Create a new Configuration Json Repository instance.
     */
    public function __construct(
        protected string $path,
        protected ?string $option,
    ) {}

    /**
     * Get the config options.
     */
    public function config(): array
    {
        $scan = $this->get() ?? [];

        return is_null($this->option) ? $scan : array_slice($scan, $this->option, 1);
    }

    /**
     * Get the configuration from the "scan.json" file.
     */
    protected function get(): array
    {
        if (! is_null($this->path) && $this->fileExists((string) $this->path)) {
            $baseConfig = json_decode(File::get($this->path), true);

            return tap($baseConfig, function ($configuration) {
                if (! is_array($configuration)) {
                    abort(1, sprintf('The configuration file [%s] is not valid JSON.', $this->path));
                }
            });
        }

        return self::DEFAULT;
    }

    /**
     * Determine if a local or remote file exists.
     */
    protected function fileExists(string $path): bool
    {
        return match (true) {
            Str::startsWith($path, ['http://', 'https://']) => Str::of(get_headers($path)[0])->contains('200 OK'),
            default => File::exists($path)
        };
    }
}
