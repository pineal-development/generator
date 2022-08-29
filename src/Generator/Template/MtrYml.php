<?php

declare(strict_types=1);

namespace Matronator\Generator\Template;

use InvalidArgumentException;
use Nette\FileNotFoundException;
use Nette\Neon\Neon;
use SplFileObject;
use Symfony\Component\Yaml\Yaml;

final class MtrYml
{
    public static function parse(mixed $string, array $arguments = []): mixed
    {
        if (!is_string($string)) return $string;

        preg_match_all('/<%\s?([a-zA-Z0-9_]+)\|?([a-zA-Z0-9_]+?)?\s?%>/', $string, $matches);
        $args = [];
        foreach ($matches[1] as $key => $match) {
            $args[] = $arguments[$match] ?? null;
        }

        $args = self::applyFilters($matches, $args);

        return str_replace($matches[0], $args, $string);
    }

    public static function parseFile(string $filename, array $arguments = []): object
    {
        $contents = file_get_contents($filename);
        $parsed = self::parse($contents, $arguments);
        return self::parseByExtension($filename, $parsed);
    }

    public static function parseByExtension(string $filename, ?string $contents = null): object
    {
        if (!file_exists($filename) && !$contents)
            throw new FileNotFoundException("File '$filename' does not exist.");

        $file = new SplFileObject($filename);

        $extension = $file->getExtension();

        switch ($extension) {
            case 'yml':
            case 'yaml':
                $parsed = $contents ? Yaml::parse($contents, Yaml::PARSE_OBJECT_FOR_MAP) : Yaml::parseFile($filename, Yaml::PARSE_OBJECT_FOR_MAP);
                break;
            case 'neon':
                $parsed = $contents ? Neon::decode($contents) : Neon::decodeFile($filename);
                break;
            case 'json':
                $parsed = $contents ? json_decode($contents) : json_decode(file_get_contents($filename));
                break;
            default:
                throw new InvalidArgumentException("Unsupported extension value '{$extension}'.");
        }

        return $parsed;
    }

    public static function getArguments(string $string): array
    {
        preg_match_all('/<%\s?([a-zA-Z0-9_]+?)\s?%>/m', $string, $matches);

        return array_unique($matches[1]);
    }

    public static function applyFilters(array $matches, array $arguments)
    {
        $modified = $arguments;

        foreach ($arguments as $key => $arg) {
            if ($matches[2][$key]) {
                if (function_exists($matches[2][$key])) {
                    $function = $matches[2][$key];
                    $modified[$key] = $function($arg);
                }
            }
        }

        return $modified;
    }
}
