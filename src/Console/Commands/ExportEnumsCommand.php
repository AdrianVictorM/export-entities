<?php

namespace Adrianvm\ExportEntities\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ExportEnumsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:enums
                            {--path=Enums : The path to the enums directory (default: Enums)}
                            {output=resources/js/enums.js : The output file path for JavaScript enums (default: resources/js/enums.js)}
                            {--suffix= : The suffix to append to each enum name}
                            {--typescript : Generate a TypeScript definition file in addition to the JS file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export enums from Laravel to JavaScript (and optionally TypeScript) file as enums';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (version_compare(phpversion(), '8.1.0', '<')) {
            $this->error("PHP 8.1.0 or higher is required to use enums.");
            return 1;
        }

        // made another command for PHP ENUMS
        $enumsPath = app_path($this->option('path') ?: 'Enums');
        $outputPath = base_path($this->argument('output'));
        $suffix = $this->option('suffix') ?: '';
        $generateTypescript = $this->option('typescript');

        if (!is_dir($enumsPath)) {
            $this->error("Enums directory not found at $enumsPath");
            return 1;
        }

        $enums = $this->extractEnums($enumsPath, $suffix);

        if (empty($enums)) {
            $this->warn("No enums found.");
            return 0;
        }

        // Generate JavaScript content
        $jsContent = $this->generateJavaScript($enums);
        File::put($outputPath, $jsContent);
        $this->info("Enums exported to {$outputPath}");

        // Optionally generate TypeScript definitions
        if ($generateTypescript) {
            $tsPath = str_replace('.js', '.d.ts', $outputPath);
            $tsContent = $this->generateTypeScript($enums);
            File::put($tsPath, $tsContent);
            $this->info("TypeScript definitions exported to {$tsPath}");
        }

        return 0;
    }

    /**
     * Extract enums from enums.
     *
     * @param string $enumsPath
     * @param string $suffix
     * @return array
     */
    protected function extractEnums(string $enumsPath, string $suffix): array
    {
        $enums = [];
        $modelFiles = $this->getPhpFilesRecursively($enumsPath);

        foreach ($modelFiles as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className && class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $enumCases = $reflection->getMethod('cases')->invoke(null);

                $this->filterEnums($enumCases);

                if (!empty($enumCases)) {
                $modelName = $reflection->getShortName() . $suffix;
                $enums[$modelName] = collect($enumCases)
                    ->mapWithKeys(fn($case) => [$case->name => $case->value ?? $this->formatEnumValue($case->name)])
                    ->toArray();
                }
            }
        }


        return $enums;
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

            if ($tokens[$i][0] === T_ENUM) {
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
     * @param array $enums
     * @return string
     */
    protected function generateJavaScript(array $enums): string
    {
        $jsContent = '';
        foreach ($enums as $modelName => $modelEnums) {
            $jsContent .= "export const {$modelName} = " . json_encode($modelEnums, JSON_PRETTY_PRINT) . ";\n\n";
        }

        return $jsContent;
    }

    /**
     * Generate TypeScript definition content.
     *
     * @param array $enums
     * @return string
     */
    protected function generateTypeScript(array $enums): string
    {
        $tsContent = '';
        foreach ($enums as $modelName => $modelEnums) {
            $tsContent .= "export declare const {$modelName}: {\n";
            foreach ($modelEnums as $key => $value) {
                $type = is_int($value) ? 'number' : 'string';
                $readonly = 'readonly';
                $tsContent .= "  {$readonly} {$key}: {$type};\n";
            }
            $tsContent .= "};\n\n";
        }

        return $tsContent;
    }

    /**
     * Format enum value.
     *
     * @param string $value
     * @return string
     */
    protected function formatEnumValue(string $value): string
    {
        return strtolower($this->toSnakeCase($value));
    }

    /**
     * Convert a string to snake_case.
     * @param mixed $input 
     * @return string 
     */
    protected function toSnakeCase($value): string
    {
        return preg_replace('/([a-z])([A-Z])/', '$1_$2', $value);
    }

    /**
     * Filter out unwanted enums.
     *
     * @param array $enums
     */
    protected function filterEnums(array &$enums): void
    {
        $ignore = ['CREATED_AT', 'UPDATED_AT'];
        $enums = array_filter($enums, function ($enum) use ($ignore) {
            return !in_array($enum->name, $ignore);
        });
    }
}
