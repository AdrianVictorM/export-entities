<?php

namespace adrianvm\ExportModelConstants\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ExportModelConstantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:constants
                            {output=resources/js/constants.js : The output file path for JavaScript constants}
                            {--suffix=Model : The suffix to append to each model name}
                            {--typescript : Generate a TypeScript definition file in addition to the JS file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export constants from Laravel models to JavaScript (and optionally TypeScript) file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelsPath = app_path('Models');
        $outputPath = base_path($this->argument('output'));
        $suffix = $this->option('suffix') ?: 'Model';
        $generateTypescript = $this->option('typescript');

        if (!is_dir($modelsPath)) {
            $this->error("Models directory not found at $modelsPath");
            return 1;
        }

        $constants = $this->extractConstants($modelsPath, $suffix);

        if (empty($constants)) {
            $this->warn("No constants found in models.");
            return 0;
        }

        // Generate JavaScript content
        $jsContent = $this->generateJavaScript($constants);
        File::put($outputPath, $jsContent);
        $this->info("Constants exported to {$outputPath}");

        // Optionally generate TypeScript definitions
        if ($generateTypescript) {
            $tsPath = str_replace('.js', '.d.ts', $outputPath);
            $tsContent = $this->generateTypeScript($constants);
            File::put($tsPath, $tsContent);
            $this->info("TypeScript definitions exported to {$tsPath}");
        }

        return 0;
    }

    /**
     * Extract constants from models.
     *
     * @param string $modelsPath
     * @param string $suffix
     * @return array
     */
    protected function extractConstants(string $modelsPath, string $suffix): array
    {
        $constants = [];
        $modelFiles = $this->getPhpFilesRecursively($modelsPath);

        foreach ($modelFiles as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className && class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $classConstants = $reflection->getConstants();

                if (!empty($classConstants)) {
                    $modelName = $reflection->getShortName() . $suffix;
                    $constants[$modelName] = $classConstants;
                }
            }
        }

        return $constants;
    }

    /**
     * Get PHP files recursively from the given directory.
     *
     * @param string $directory
     * @return array
     */
    protected function getPhpFilesRecursively(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Get fully qualified class name from a file.
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $namespace = null;
        $class = null;
        $tokens = token_get_all(file_get_contents($filePath));

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = $this->getTokenString($tokens, $i + 2);
            }

            if ($tokens[$i][0] === T_CLASS) {
                $class = $tokens[$i + 2][1];
                break;
            }
        }

        return $namespace ? $namespace . '\\' . $class : null;
    }

    /**
     * Get a string of tokens starting from a given index.
     *
     * @param array $tokens
     * @param int $start
     * @return string
     */
    protected function getTokenString(array $tokens, int $start): string
    {
        $string = '';
        for ($i = $start; isset($tokens[$i]) && is_array($tokens[$i]); $i++) {
            $string .= $tokens[$i][1];
        }

        return trim($string);
    }

    /**
     * Generate JavaScript content.
     *
     * @param array $constants
     * @return string
     */
    protected function generateJavaScript(array $constants): string
    {
        $jsContent = '';
        foreach ($constants as $modelName => $modelConstants) {
            $jsContent .= "export const {$modelName} = " . json_encode($modelConstants, JSON_PRETTY_PRINT) . ";\n\n";
        }

        return $jsContent;
    }

    /**
     * Generate TypeScript definition content.
     *
     * @param array $constants
     * @return string
     */
    protected function generateTypeScript(array $constants): string
    {
        $tsContent = '';
        foreach ($constants as $modelName => $modelConstants) {
            $tsContent .= "export declare const {$modelName}: {\n";
            foreach ($modelConstants as $key => $value) {
                $type = is_int($value) ? 'number' : 'string';
                $tsContent .= "  {$key}: {$type};\n";
            }
            $tsContent .= "};\n\n";
        }

        return $tsContent;
    }
}
