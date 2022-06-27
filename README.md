# Matronator/Generator

Automatically generate Entity and related files from the console.

## Instalation

```
composer require matronator/generator --dev
```

#### Troubleshooting

If you get this error when trying to install:

```
matronator/generator dev-master requires composer-runtime-api ^2.2 -> found composer-runtime-api[2.1.0] but it does not match the constraint.
```

Run this command to update composer to the latest version:

```
composer self-update
```

If you can't or don't want to update composer, use version `"^1.0"` of this package as that doesn't depend on Composer runtime API 2.2.

#### Additional steps (temporary - will be automated in the future)

1. Copy `mtrgen` file from `project-root/vendor/matronator/generator/bin/` to `project-root/bin/`
2. Run `chmod +x bin/mtrgen` from the project root in terminal

## Usage

You run the script from terminal using this command:

```
bin/generator
```
