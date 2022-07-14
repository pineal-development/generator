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

## Usage

You run the script from terminal using this command:

```
bin/mtrgen generate:entity
bin/mtrgen generate:facade
bin/mtrgen generate:repository
```

#### Configuration

You can specify if you want to generate the files from a config by setting the `--config` (or the shorthand `-c`) option to the path to your config file, like this:

```
bin/mtrgen generate --config=path/to/config/file.yml
```

You can find a sample config file in the `src/` folder under a name `config.sample.yml`. So if you installed this via Composer, it would be in `vendor/matronator/generator/src/config.sample.yml`.
