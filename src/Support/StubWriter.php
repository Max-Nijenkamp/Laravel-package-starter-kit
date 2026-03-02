<?php

namespace MaxNijenkamp\LaravelPackageStarterKit\Support;

use Illuminate\Filesystem\Filesystem;

class StubWriter
{
    public function __construct(
        protected Filesystem $files
    ) {
    }

    public function putFromStub(string $stubName, string $targetPath, array $replacements): void
    {
        $stubPath = $this->resolveStubPath($stubName);

        if (! $this->files->exists($stubPath)) {
            throw new \RuntimeException("Stub [{$stubName}] not found at [{$stubPath}].");
        }

        $this->ensureDirectory(dirname($targetPath));

        $contents = $this->files->get($stubPath);

        $contents = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        );

        $this->files->put($targetPath, $contents);
    }

    protected function resolveStubPath(string $stubName): string
    {
        $publishedPath = base_path("stubs/starterkit/{$stubName}");

        if ($this->files->exists($publishedPath)) {
            return $publishedPath;
        }

        return __DIR__ . "/../../stubs/{$stubName}";
    }

    protected function ensureDirectory(string $directory): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }
}

