<?php

namespace App\Actions\Base;

use App\Actions\Concerns\BaseMethods;
use App\Output\ProgressOutput;
use Illuminate\Support\Collection;
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
     * Scanner the project resolved by the current input and output.
     */
    public function execute(array $configs): array
    {
        $this->groupConfigs($configs)->each(function (array $group) {
            $used = collect($group)->flatMap(function (array $config) {
                $this->config = $config;

                return $this->collectKeys($this->getFilesToScan());
            })->flip()->all();

            collect($group)
                ->unique(function (array $config) {
                    return $this->getLangPath($config);
                })
                ->each(function (array $config) use ($used) {
                    $this->config = $config;

                    $this->unusedTranslations($used);
                });
        });

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
     * Gets the real language path of the given config.
     */
    private function getLangPath(array $config): string
    {
        $path = $config['base_path'].'/'.$config['lang_path'];

        return realpath($path) ?: $path;
    }

    /**
     * Groups the configs that share the language path or the methods, like the duplicate scanner.
     */
    private function groupConfigs(array $configs): Collection
    {
        return collect($configs)->reduce(function (Collection $groups, array $config) {
            $keys = [$this->getLangPath($config), json_encode($config['methods'])];

            [$linked, $groups] = $groups->partition(function (array $group) use ($keys) {
                return filled(array_intersect($keys, data_get($group, 'keys')));
            });

            return $groups->push([
                'keys' => $linked->pluck('keys')->collapse()->merge($keys)->unique()->all(),
                'configs' => $linked->pluck('configs')->collapse()->push($config)->all(),
            ]);
        }, collect())->pluck('configs');
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
