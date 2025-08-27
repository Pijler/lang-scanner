<?php

namespace App\Actions;

use App\Actions\Concerns\CheckScanner;
use App\Actions\Concerns\UpdateScanner;
use App\Output\ProgressOutput;
use App\Project;
use App\Repositories\ConfigurationJsonRepository;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class Scanner
{
    use CheckScanner;
    use UpdateScanner;

    /**
     * The paths to scan.
     */
    protected array $paths;

    /**
     * The changes made during the scan.
     */
    protected array $changes = [];

    /**
     * The total number of files scanned.
     */
    protected int $totalFiles = 0;

    /**
     * Creates a new Scanner instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected ProgressOutput $progressOutput,
    ) {
        $this->paths = Project::paths($input);
    }

    /**
     * Scanner the project resolved by the current input and output.
     */
    public function execute(): array
    {
        $configs = resolve(ConfigurationJsonRepository::class)->config();

        collect($configs)->each(function (array $config) {
            $this->input->getOption('check')
                ? $this->checkScanner($config)
                : $this->updateScanner($config);
        });

        return [$this->totalFiles, $this->changes];
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

        $files = rescue(function () use ($config) {
            return File::allFiles(Project::path().'/'.$config['lang_path']);
        }, [], false);

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
