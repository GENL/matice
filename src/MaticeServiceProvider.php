<?php

namespace Genl\Matice;

use Genl\Matice\Commands\TranslationsGeneratorCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

class MaticeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'matice');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'matice');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/matice.php' => config_path('matice.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/matice'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/matice'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/matice'),
            ], 'lang');*/
        }

        // Registering package commands.
        $this->commands([
            TranslationsGeneratorCommand::class,
        ]);

        Translator::macro('list', function () {
            return MaticeServiceProvider::makeFolderFilesTree(config('matice.lang_directory'));
        });

        Blade::directive('translations', function ($locale) {
            $locale = $locale ?: 'null';

            $useCache = config('matice.use_generated_translations_file_in_prod') === true
                && app()->isProduction()
                ? 'true' : 'false';

            return "<?php echo app()->make('matice')->generate($locale, true, $useCache); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/matice.php', 'matice');

        // Register the main class to use with the facade
        $this->app->singleton('matice', function () {
            return new BladeTranslationsGenerator;
        });
    }

    public static function makeFolderFilesTree($dir): array
    {
        return Helpers::makeFolderFilesTree($dir);
    }
}
