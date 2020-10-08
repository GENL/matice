<?php


namespace Genl\Matice;


use Genl\Matice\Facades\Matice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslationsGeneratorCommand extends Command
{
    protected $signature = 'matice:generate {path=./resources/assets/js/matice_translations.js}';

    protected $description = 'Generate js file for including in build process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {
        $path = $this->argument('path');

        $generatedRoutes = Matice::generate();

        $this->makeDirectory($path);

        File::put(base_path($path), $generatedRoutes);

        $this->info("Matice translations file generated at ${path} ");
    }

    protected function makeDirectory(string $path)
    {
        if (! File::isDirectory(dirname(base_path($path)))) {
            File::makeDirectory(dirname(base_path($path)), 0777, true, true);
        }
        return $path;
    }
}
