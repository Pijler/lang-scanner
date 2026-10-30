<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Output\ProgressOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class UnusedScanner
{
    use BaseMethods;

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {}

    /**
     * Scanner the configs that share the same language path.
     */
    public function execute(array $configs): array
    {
        $keys = collect($configs)->flatMap(function (array $config) {
            $this->config = $config;

            return $this->collectKeys($this->getFilesToScan());
        });

        $this->unusedTranslations($keys->flip()->all());

        return [$this->totalFiles, $this->changes];
    }

    /**
     * Checks if the unused translations should be removed.
     */
    protected function prune(): bool
    {
        return $this->input->getOption('prune');
    }

    /**
     * Finds the translations that are not used in code, removing them when pruning.
     */
    private function unusedTranslations(array $used): void
    {
        collect($this->getFiles())
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'json';
            })
            ->each(function (SplFileInfo $file) use ($used) {
                [$content, $removed] = $this->removeUnusedKeys($this->decodeFile($file), $used);

                if (blank($removed)) {
                    return;
                }

                if ($this->prune()) {
                    $this->putContent($file, $content);
                }

                $this->changes[] = [
                    'unused' => true,
                    'issues' => $removed,
                    'check' => ! $this->prune(),
                    'count' => count($removed),
                    'file' => $file->getRealPath(),
                ];
            });
    }

    /**
     * Removes the keys that are not used, returning the content and the removed keys.
     */
    private function removeUnusedKeys(array $content, array $used, string $prefix = ''): array
    {
        $removed = [];

        foreach ($content as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (isset($used[$fullKey])) {
                continue;
            }

            if (is_array($value) && filled($value)) {
                [$content[$key], $children] = $this->removeUnusedKeys($value, $used, $fullKey);

                $removed = array_merge($removed, $children);

                if (blank($content[$key])) {
                    unset($content[$key]);
                }

                continue;
            }

            unset($content[$key]);

            $removed[] = $fullKey;
        }

        return [$content, $removed];
    }
}
