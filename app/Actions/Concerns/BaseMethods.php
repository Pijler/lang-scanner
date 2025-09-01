<?php

namespace App\Actions\Concerns;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property array $config
 */
trait BaseMethods
{
    /**
     * Puts the content into the specified file.
     */
    protected function putContent(SplFileInfo $file, array $content): void
    {
        $file->openFile('w')->fwrite(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get all files from the specified configuration.
     */
    protected function getFiles(): array
    {
        return rescue(function () {
            return File::allFiles($this->config['base_path'].'/'.$this->config['lang_path']);
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
     * Sorts a multi-dimensional array recursively.
     */
    protected function sortRecursive(array $array): array
    {
        ksort($array);

        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursive($value);
            }
        }

        return $array;
    }

    /**
     * Gets the translations from the language files.
     */
    protected function getTranslations(): array
    {
        abort_unless(isset($this->config['lang_path']), 'Language path is not set.');

        $files = $this->getFiles();

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
