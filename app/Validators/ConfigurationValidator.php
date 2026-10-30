<?php

namespace App\Validators;

class ConfigurationValidator
{
    /**
     * The allowed keys at the root of the configuration file.
     *
     * The "cache" key is no longer used, but is kept for backwards compatibility.
     */
    protected const array ROOT_KEYS = ['cache', 'extends', 'scanner'];

    /**
     * The allowed keys of each scanner entry, and their types.
     */
    protected const array SCANNER_KEYS = [
        'dot' => 'bool',
        'sort' => 'bool',
        'paths' => 'list',
        'check' => 'bool',
        'methods' => 'list',
        'no-empty' => 'bool',
        'no-update' => 'bool',
        'extensions' => 'list',
        'lang_path' => 'string',
    ];

    /**
     * The keys required by each scanner mode.
     */
    protected const array REQUIRED_KEYS = [
        'check' => ['lang_path'],
        'merge' => ['lang_path'],
        'duplicate' => ['lang_path', 'methods'],
        'update' => ['lang_path', 'paths', 'extensions', 'methods'],
        'unused' => ['lang_path', 'paths', 'extensions', 'methods'],
    ];

    /**
     * Validates the structure of the given configuration file content.
     */
    public static function validateFile(array $content, string $path): void
    {
        $unknown = array_diff(array_keys($content), self::ROOT_KEYS);

        static::assert(blank($unknown), $path, sprintf(
            'unknown key [%s]. Allowed keys are: %s.',
            implode(', ', $unknown),
            implode(', ', self::ROOT_KEYS),
        ));

        if (array_key_exists('extends', $content)) {
            static::assert(static::isListOfStrings($content['extends']), $path, '[extends] must be a list of strings.');
        }

        if (array_key_exists('scanner', $content)) {
            static::assert(is_array($content['scanner']) && array_is_list($content['scanner']), $path, '[scanner] must be a list of objects.');

            foreach ($content['scanner'] as $index => $scanner) {
                static::validateScanner($scanner, "scanner[{$index}]", $path);
            }
        }
    }

    /**
     * Validates that the given scanner entry has the keys required by the mode.
     */
    public static function validateMode(array $config, string $mode): void
    {
        $missing = array_diff(self::REQUIRED_KEYS[$mode], array_keys($config));

        static::assert(blank($missing), $config['base_path'] ?? 'scanner.json', sprintf(
            'the "%s" mode requires the [%s] %s.',
            $mode,
            implode(', ', $missing),
            str('option')->plural(count($missing)),
        ));
    }

    /**
     * Validates a single scanner entry.
     */
    protected static function validateScanner(mixed $scanner, string $name, string $path): void
    {
        static::assert(is_array($scanner) && ! array_is_list($scanner), $path, "[{$name}] must be an object.");

        $unknown = array_diff(array_keys($scanner), array_keys(self::SCANNER_KEYS));

        static::assert(blank($unknown), $path, sprintf(
            'unknown key [%s] in [%s]. Allowed keys are: %s.',
            implode(', ', $unknown),
            $name,
            implode(', ', array_keys(self::SCANNER_KEYS)),
        ));

        foreach ($scanner as $key => $value) {
            match (self::SCANNER_KEYS[$key]) {
                'bool' => static::assert(is_bool($value), $path, "[{$name}.{$key}] must be a boolean."),
                'list' => static::assert(static::isListOfStrings($value), $path, "[{$name}.{$key}] must be a list of strings."),
                'string' => static::assert(is_string($value) && $value !== '', $path, "[{$name}.{$key}] must be a non-empty string."),
            };
        }

        foreach ($scanner['methods'] ?? [] as $method) {
            static::assert(substr_count($method, '*') === 1, $path, sprintf(
                'the method [%s] in [%s.methods] must contain exactly one "*" placeholder.',
                $method,
                $name,
            ));
        }
    }

    /**
     * Determine if the given value is a list of non-empty strings.
     */
    protected static function isListOfStrings(mixed $value): bool
    {
        return is_array($value) && array_is_list($value) && collect($value)->every(
            fn ($item) => is_string($item) && $item !== ''
        );
    }

    /**
     * Aborts with an invalid configuration message when the condition fails.
     */
    protected static function assert(bool $condition, string $path, string $message): void
    {
        abort_unless($condition, 1, "Invalid configuration [{$path}]: {$message}");
    }
}
