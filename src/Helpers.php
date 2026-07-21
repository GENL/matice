<?php


namespace Genl\Matice;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Load all folders and files (php and json) inside a directory and
     * return an array representation of them.
     *
     * @param $dir
     * @return array
     */
    public static function makeFolderFilesTree($dir): array
    {
        $tree = [];
        $localeFolders = File::directories($dir);

        foreach ($localeFolders as $localeFolder) {
            $locale = basename($localeFolder);

            $tree[$locale] = self::readLocaleFolder($localeFolder);
        }

        $jsonPaths = self::getJsonPaths($dir);

        foreach ($jsonPaths as $jsonPath) {
            if(File::exists($jsonPath)) {
                $files = File::files($jsonPath);
                foreach ($files as $file) {
                    if ($file->getExtension() !== 'json') {
                        continue;
                    }
                    $locale = $file->getFilenameWithoutExtension();
                    $tree[$locale] = array_merge(
                        $tree[$locale] ?? [],
                        json_decode($file->getContents(), true)
                    );
                }
            }
        }

        return $tree;
    }

    private static function getJsonPaths($dir): array
    {
        $jsonPaths = [];

        if (method_exists(Lang::getLoader(), 'jsonPaths')) {
            $jsonPaths = Lang::getLoader()->jsonPaths();
        } elseif (method_exists(Lang::getLoader(), 'getJsonPaths')) {
            $jsonPaths = Lang::getLoader()->getJsonPaths();
        }

        return array_unique(
            array_merge([$dir], $jsonPaths)
        );
    }

    private static function readLocaleFolder(string $localeFolder): array
    {
        $tree = [];
        $ffs = scandir($localeFolder);

        foreach ($ffs as $ff) {
            // We skip hidden folders or config files in the directory.
            if (Str::startsWith($ff, '.')) {
                continue;
            }

            $extension = '.' . Str::afterLast($ff, '.');
            $ff = basename($ff, $extension);
            $tree[$ff] = [];

            if (is_dir($localeFolder . '/' . $ff)) {
                $tree[$ff] = self::readLocaleFolder($localeFolder . '/' . $ff);
            }

            if (is_file($pathName = $localeFolder . '/' . $ff . $extension)) {
                $existingTranslations = $tree[$ff] ?? [];

                if ($extension === '.json') {
                    $tree[$ff] = array_merge(
                        $existingTranslations,
                        json_decode(File::get($pathName), true)
                    );
                } else if ($extension === '.php') {
                    $tree[$ff] = array_merge(
                        $existingTranslations,
                        require($pathName)
                    );
                }
            }
        }

        return $tree;
    }

    /**
     * This method removes the excepted namespaces from the translations
     * and add allows only the exportable translations if defined.
     *
     * When the same namespace is included and excepted at the same time, it considered excepted.
     *
     * @param array $translations
     * @param string[] $locales Locales being loaded (used to resolve short namespace names).
     * @param array{only?: string|string[], except?: string|string[]} $options
     *      Optional per-call overrides. When a key is present it replaces the matching config value.
     */
    public static function applyTranslationRestrictions(array &$translations, array $locales = [], array $options = [])
    {
        // ----------
        // Manage exported namespaces
        // ----------
        $exportables = array_key_exists('only', $options)
            ? Arr::wrap($options['only'])
            : config('matice.only');
        $exportables = self::resolveNamespaces($exportables, $translations, $locales);

        // When the user ask to export only a certain namespaces, we empty the $translation to fill them later
        // with the only ones required.
        if (! empty($exportables)) {
            $copy = $translations;
            $translations = [];
        }

        foreach ($exportables as $exportableNamespace) {
            $exportableNamespace = self::namespaceToDotPath($exportableNamespace);

            // Set only the translations for the exportable namespaces
            $value = Arr::get($copy, (string)$exportableNamespace);
            Arr::set($translations, ($exportableNamespace), $value);
        }

        // ----------
        // Manage excepted namespaces
        // ----------
        $hidden = array_key_exists('except', $options)
            ? Arr::wrap($options['except'])
            : config('matice.except');
        $hidden = self::resolveNamespaces($hidden, $translations, $locales);

        foreach ($hidden as $hiddenNamespace) {
            $hiddenNamespace = self::namespaceToDotPath($hiddenNamespace);

            // remove the translations in the array
            Arr::forget($translations, $hiddenNamespace);
        }
    }

    /**
     * Resolve namespace filters to paths relative to the lang directory.
     * Short names (e.g. "auth") are expanded to "{locale}/auth" for each loaded locale.
     * Full paths (e.g. "en/auth") are kept as-is.
     *
     * @param string[] $namespaces
     * @param array $translations
     * @param string[] $locales
     * @return string[]
     */
    private static function resolveNamespaces(array $namespaces, array $translations, array $locales): array
    {
        $localeKeys = $locales ?: array_keys($translations);
        $resolved = [];

        foreach ($namespaces as $namespace) {
            $namespace = str_replace('\\', '/', trim((string) $namespace, '/\\'));
            $namespace = Str::beforeLast($namespace, '.');

            if ($namespace === '') {
                continue;
            }

            // Full path when the first segment is a locale key (e.g. "en/auth").
            // Otherwise treat as a short name and expand under each loaded locale.
            $firstSegment = Str::before($namespace, '/');

            if (isset($translations[$firstSegment])) {
                $resolved[] = $namespace;
                continue;
            }

            foreach ($localeKeys as $locale) {
                if (isset($translations[$locale])) {
                    $resolved[] = $locale . '/' . $namespace;
                }
            }
        }

        return $resolved;
    }

    private static function namespaceToDotPath(string $namespace): string
    {
        $namespace = str_replace('\\', '/', trim($namespace, '/\\'));
        $namespace = Str::beforeLast($namespace, '.');

        return str_replace('/', '.', $namespace);
    }
}
