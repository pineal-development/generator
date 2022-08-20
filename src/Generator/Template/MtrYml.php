<?php

declare(strict_types=1);

namespace Matronator\Generator\Template;

final class MtrYml
{
    public static function parse(mixed $string, array $arguments = [])
    {
        if (!is_string($string)) return $string;

        preg_match_all('/<%\s?([a-zA-Z0-9_]+?)\s?%>/', $string, $matches);
        $args = [];
        foreach ($matches[1] as $key => $match) {
            $args[] = $arguments[$match] ?? null;
        }

        return str_replace($matches[0], $args, $string);
    }
}
