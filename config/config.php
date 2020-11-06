<?php

return [

    /*
     * --------------------------------------------------------------------------
     * The lang directory
     * --------------------------------------------------------------------------
     *
     * The path(directory) where matice finds the translations to work with.
     */
    'lang_directory' => resource_path('lang'),

    /*
    |--------------------------------------------------------------------------
    | Use existing generated file in prod
    |--------------------------------------------------------------------------
    |
    | Whether @translations should always use the generated translations in production.
    | If false, the @translations directive will always regenerate the translations.
    |
    */
    'use_generated_translations_file_in_prod' => true,

    /*
    |--------------------------------------------------------------------------
    | generated translations file name
    |--------------------------------------------------------------------------
    |
    | The place where to generate translations file.
    |
    */
    'generate_translations_path' => resource_path('assets/js/matice_translations.js'),

];
