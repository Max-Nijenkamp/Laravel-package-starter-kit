## Laravel Package Starter Kit

**Author**: Max Nijenkamp  
**Purpose**: Quickly scaffold fully-structured Laravel packages with an interactive Artisan command.

### What this package does

- **Interactive command**: `php artisan starterkit:make-package`
- **Generates packages into**: `packages/{vendor}/{package}/`
- **Creates only what you select**:
  - Service provider
  - Config file
  - Example command
  - Migrations folder + example migration
  - Tests + Testbench setup
  - README + LICENSE
  - Optional CI workflow and git init

The goal is to remove all the repetitive boilerplate steps when creating a new Laravel package so you can start coding features immediately.

### Installation

1. **Require the starter kit in your Laravel app:**

```bash
composer require maxnijenkamp/laravel-package-starter-kit --dev
```

2. **(Optional) Publish the config and stubs:**

```bash
php artisan vendor:publish --provider=\"MaxNijenkamp\\\\LaravelPackageStarterKit\\\\StarterKitServiceProvider\" --tag=starterkit-config
php artisan vendor:publish --provider=\"MaxNijenkamp\\\\LaravelPackageStarterKit\\\\StarterKitServiceProvider\" --tag=starterkit-stubs
```

### Usage

Run inside any Laravel application:

```bash
php artisan starterkit:make-package
```

You will be asked for:

- **Vendor name** (default: `maxnijenkamp`)
- **Package name** (e.g. `awesome-package`)
- **Description**
- **Root namespace** (default: based on vendor + package)

Then you can answer **yes/no** for:

- With config file?
- With example migration?
- With example command?
- With tests (using Orchestra Testbench)?
- With README + LICENSE?
- With GitHub Actions CI?
- Run `git init` in the new package?

The kit will then generate a ready-to-use package in:

```text
packages/{vendor}/{package}/
```

Along with a quick reminder:

```text
Package created successfully!
cd packages/{vendor}/{package}
composer install
git init   # if you didn't already generate it
```

### Configuration

After publishing the config, you can tweak defaults in `config/starterkit.php`:

- **`packages_path`**: where new packages are generated (default: `packages`)
- **`default_vendor`**: default vendor to pre-fill in the prompt (default: `maxnijenkamp`)
- **`default_license`**: license stub to use (default: `MIT`)

### License

This starter kit is open-sourced software licensed under the MIT license.

