<?php

namespace App\Actions\Concerns;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

trait BaseMethods
{
    /**
     * The configuration options for the scanner.
     */
    protected array $config = [];

    /**
     * The changes made during the scan.
     */
    protected array $changes = [];

    /**
     * The total number of files scanned.
     */
    protected int $totalFiles = 0;

    /**
     * Puts the content into the specified file.
     */
    protected function putContent(SplFileInfo $file, array $content): void
    {
        if (filled($content)) {
            File::put($file->getRealPath(), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Get all files from the specified configuration.
     */
    protected function getFiles(): array
    {
        abort_unless(
            code: 1,
            message: 'Config paths are not set.',
            boolean: isset($this->config['base_path'], $this->config['lang_path']),
        );

        return rescue(function () {
            return File::allFiles($this->config['base_path'].'/'.$this->config['lang_path']);
        }, [], false);
    }

    /**
     * Extract only the keys from a multi-dimensional array.
     */
    protected function extractKeys(array $array): array
    {
        return collect($array)->map(function ($value) {
            return is_array($value) ? $this->extractKeys($value) : '';
        })->all();
    }

    /**
     * Gets the translations from the language files.
     */
    protected function getTranslations(): array
    {
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
