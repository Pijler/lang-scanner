<?php

namespace App\Actions\Concerns;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property string $basePath
 */
trait BaseMethods
{
    /**
     * Get all files from the specified configuration.
     */
    protected function getFiles(array $config): array
    {
        return rescue(function () use ($config) {
            return File::allFiles($config['base_path'].'/'.$config['lang_path']);
        }, [], false);
    }

    /**
     * Extract only the keys from a multi-dimensional array.
     */
    protected function extractKeys(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$key] = is_array($value) ? $this->extractKeys($value) : '';
        }

        return $result;
    }

    /**
     * Gets the translations from the language files.
     */
    protected function getTranslations(array $config): array
    {
        abort_unless(isset($config['lang_path']), 'Language path is not set.');

        $files = $this->getFiles($config);

        return collect($files)
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->map(function (SplFileInfo $file) {
                return json_decode($file->getContents(), true);
            })
            ->reduce(function (array $carry, array $item) {
                return array_replace_recursive($carry, $this->extractKeys($item));
            }, []);
    }
}
