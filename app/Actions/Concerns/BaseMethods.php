<?php

namespace App\Actions\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @property InputInterface $input
 */
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
     * JSON encoding flags.
     */
    protected int $flags = JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES;

    /**
     * Checks if the translations should be dotted.
     */
    protected function dotted(): bool
    {
        return $this->config['dot'] ?? $this->input->getOption('dot');
    }

    /**
     * Checks if the translations should be sorted.
     */
    protected function sorted(): bool
    {
        return $this->config['sort'] ?? $this->input->getOption('sort');
    }

    /**
     * Checks if the translations should be sorted.
     */
    private function dotArray(array $array): array
    {
        $dotted = $this->dotted();

        return $dotted ? Arr::dot($array) : $array;
    }

    /**
     * Checks if the translations should be sorted.
     */
    private function sortArray(array $array): array
    {
        $sorted = $this->sorted();

        return $sorted ? Arr::sortRecursive($array) : $array;
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
     * Puts the content into the specified file.
     */
    protected function putContent(SplFileInfo $file, array $content): void
    {
        if (filled($content)) {
            $content = $this->dotArray($content);

            $content = $this->sortArray($content);

            File::put($file->getRealPath(), json_encode($content, $this->flags));
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
            return File::files($this->config['base_path'].'/'.$this->config['lang_path']);
        }, [], false);
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
