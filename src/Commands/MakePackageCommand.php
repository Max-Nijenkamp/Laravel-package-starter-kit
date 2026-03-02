<?php

namespace MaxNijenkamp\LaravelPackageStarterKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use MaxNijenkamp\LaravelPackageStarterKit\Support\StubWriter;

class MakePackageCommand extends Command
{
    protected $signature = 'starterkit:make-package';

    protected $description = 'Interactively generate a new Laravel package in the packages folder.';

    public function __construct(
        protected Filesystem $files,
        protected StubWriter $stubWriter
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Laravel Package Starter Kit');
        $this->line('Let\'s create a new package...');
        $this->newLine();

        $basePath = base_path(config('starterkit.packages_path', 'packages'));

        $vendor = $this->ask(
            'Vendor name (kebab-case, e.g. your-vendor)'
        );

        $package = $this->ask(
            'Package name (kebab-case, e.g. awesome-package)'
        );

        if (! $vendor || ! $package) {
            $this->error('Vendor and package name are required.');

            return self::FAILURE;
        }

        $description = $this->ask('Package description', 'A Laravel package.');

        $vendorStudly = Str::studly(str_replace(['-', '_'], ' ', $vendor));
        $packageStudly = Str::studly(str_replace(['-', '_'], ' ', $package));

        $defaultNamespace = "{$vendorStudly}\\{$packageStudly}";

        $rootNamespace = $this->ask(
            'Root PHP namespace',
            $defaultNamespace
        );

        $includeConfig = $this->confirm('With config file?', true);
        $includeMigration = $this->confirm('With example migration?', false);
        $includeCommand = $this->confirm('With example Artisan command?', false);
        $includeTests = $this->confirm('With tests (Orchestra Testbench)?', true);
        $includeReadmeAndLicense = $this->confirm('With README and LICENSE?', true);
        $includeCi = $this->confirm('With GitHub Actions CI workflow?', false);
        $initGit = $this->confirm('Initialize git in the new package?', false);

        $packagePath = $basePath . '/' . $vendor . '/' . $package;

        if ($this->files->exists($packagePath)) {
            if (! $this->confirm("Directory [{$packagePath}] already exists. Continue and potentially overwrite files?", false)) {
                $this->warn('Aborted.');

                return self::FAILURE;
            }
        }

        $this->generatePackage(
            $packagePath,
            compact(
                'vendor',
                'package',
                'description',
                'rootNamespace',
                'vendorStudly',
                'packageStudly',
                'includeConfig',
                'includeMigration',
                'includeCommand',
                'includeTests',
                'includeReadmeAndLicense',
                'includeCi',
                'initGit'
            )
        );

        $this->info('Package created successfully!');
        $this->line("Location: {$packagePath}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line("  cd {$packagePath}");
        $this->line('  composer install');

        if (! $initGit) {
            $this->line('  git init   # if you want version control here');
        }

        return self::SUCCESS;
    }

    protected function generatePackage(string $packagePath, array $context): void
    {
        $this->files->ensureDirectoryExists($packagePath . '/src');
        $this->files->ensureDirectoryExists($packagePath . '/config');
        $this->files->ensureDirectoryExists($packagePath . '/tests');

        if ($context['includeMigration']) {
            $this->files->ensureDirectoryExists($packagePath . '/database/migrations');
        }

        if ($context['includeCommand']) {
            $this->files->ensureDirectoryExists($packagePath . '/src/Commands');
        }

        $replacements = $this->buildReplacements($context);

        // Core files
        $this->stubWriter->putFromStub('package-composer.stub', $packagePath . '/composer.json', $replacements);
        $this->stubWriter->putFromStub('package-service-provider.stub', $packagePath . '/src/' . $context['packageStudly'] . 'ServiceProvider.php', $replacements);

        if ($context['includeConfig']) {
            $this->stubWriter->putFromStub('package-config.stub', $packagePath . '/config/' . $replacements['{{ package_snake }}'] . '.php', $replacements);
        }

        if ($context['includeCommand']) {
            $this->stubWriter->putFromStub('package-command.stub', $packagePath . '/src/Commands/ExampleCommand.php', $replacements);
        }

        if ($context['includeMigration']) {
            $this->stubWriter->putFromStub(
                'package-migration.stub',
                $packagePath . '/database/migrations/' . date('Y_m_d_His') . '_create_example_table.php',
                $replacements
            );
        }

        if ($context['includeTests']) {
            $this->stubWriter->putFromStub('package-phpunit.stub', $packagePath . '/phpunit.xml', $replacements);
            $this->stubWriter->putFromStub('package-testbench.stub', $packagePath . '/tests/ExampleTest.php', $replacements);
        }

        if ($context['includeReadmeAndLicense']) {
            $this->stubWriter->putFromStub('package-readme.stub', $packagePath . '/README.md', $replacements);
            $this->stubWriter->putFromStub('package-license.stub', $packagePath . '/LICENSE', $replacements);
        }

        if ($context['includeCi']) {
            $this->files->ensureDirectoryExists($packagePath . '/.github/workflows');
            $this->stubWriter->putFromStub('package-github-actions.stub', $packagePath . '/.github/workflows/tests.yml', $replacements);
        }

        if ($context['initGit']) {
            $this->initGitRepository($packagePath);
        }
    }

    protected function buildReplacements(array $context): array
    {
        $packageSnake = Str::snake(str_replace('-', '_', $context['package']));
        $packageKebab = Str::of($context['package'])->lower()->kebab();

        return [
            '{{ vendor }}' => $context['vendor'],
            '{{ package }}' => $context['package'],
            '{{ description }}' => $context['description'],
            '{{ root_namespace }}' => trim($context['rootNamespace'], '\\'),
            '{{ vendor_studly }}' => $context['vendorStudly'],
            '{{ package_studly }}' => $context['packageStudly'],
            '{{ package_snake }}' => $packageSnake,
            '{{ package_kebab }}' => (string) $packageKebab,
            '{{ year }}' => date('Y'),
            '{{ author }}' => $context['vendorStudly'],
        ];
    }

    protected function initGitRepository(string $packagePath): void
    {
        // Best-effort git init; ignore failures on environments without git.
        try {
            $process = proc_open(
                'git init',
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                $packagePath
            );

            if (is_resource($process)) {
                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }

                proc_close($process);
            }
        } catch (\Throwable $e) {
            // Silently ignore; user can init git manually.
        }
    }
}

