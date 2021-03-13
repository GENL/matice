<?php


namespace Genl\Matice;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Load all folders and files (php and json) inside of a directory and
     * return an array representation of them.
     *
     * @param $dir
     * @return array
     */
    public static function makeFolderFilesTree($dir): array
    {
        $tree = [];
        $ffs = scandir($dir);

        foreach ($ffs as $ff) {
            if (!Str::startsWith($ff, '.')) { // We skip hidden folders or config files the the directory.

                $extension = '.' . Str::afterLast($ff, '.');

                $ff = basename($ff, $extension);

                $tree[$ff] = [];

                if (is_dir($dir . '/' . $ff)) {

                    $tree[$ff] = MaticeServiceProvider::makeFolderFilesTree($dir . '/' . $ff);

                }

                if (is_file($pathName = $dir . '/' . $ff . $extension)) {

                    if ($extension === '.json') {

                        $existingTranslations = $tree[$ff] ?? [];

                        $tree[$ff] = array_merge(
                            $existingTranslations,
                            json_decode(File::get($pathName), true)
                        );

                    } else if ($extension === '.php') {

                        $tree[$ff] = require($pathName);

                    }

                }

            }
        }

        return $tree;
    }

    /**
     * This method removes the excepted namespaces from the translations.
     * Then it add allows only the exportable translations if defined.
     *
     * @param array $translations
     */
    public static function applyTranslationRestrictions(array &$translations)
    {
        // ----------
        // Manage exported namespaces
        // ----------
        $exportables = config('matice.only');

        // When the user ask to export only a certain namespaces, we empty the $translation to fill them later
        // with the only ones required.
        if (!empty($exportables)) {
            $copy = $translations;
            $translations = [];
        }

        foreach ($exportables as $exportableNamespace) {
            // Force "/" as separator
            $exportableNamespace = Str::of(trim($exportableNamespace, '/\\'))->replace('\\', '/');
            // Remove the last dot that might exit when the namespace is a file.
            $exportableNamespace = $exportableNamespace->beforeLast('.');
            // Replace the "/" by "."
            $exportableNamespace = $exportableNamespace->replace('/', '.');

            // Set only the translations for the exportable namespaces
            $value = Arr::get($copy, (string)$exportableNamespace);
            Arr::set($translations, ($exportableNamespace), $value);
        }

        // ----------
        // Manage excepted namespaces
        // ----------
        $hidden = config('matice.except');

        foreach ($hidden as $hiddenNamespace) {
            // Force "/" as separator
            $hiddenNamespace = Str::of(trim($hiddenNamespace, '/\\'))->replace('\\', '/');
            // Remove the last dot that might exit when the namespace is a file.
            $hiddenNamespace = $hiddenNamespace->beforeLast('.');
            // Replace the "/" by "."
            $hiddenNamespace = $hiddenNamespace->replace('/', '.');

            // remove the translations in the array
            Arr::forget($translations, $hiddenNamespace);
        }
    }
}
