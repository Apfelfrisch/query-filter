<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

class Str
{
    /** @var array<string, array<string, string>> */
    private static $snakeCache = [];
    /** @var array<string, string> */
    private static $camelCache = [];
    /** @var array<string, string> */
    private static $studlyCache = [];

    public static function camel(string $value): string
    {
        if (isset(self::$camelCache[$value])) {
            return self::$camelCache[$value];
        }

        return self::$camelCache[$value] = lcfirst(self::studly($value));
    }

    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(self::$studlyCache[$key])) {
            return self::$studlyCache[$key];
        }

        $words = mb_split('\s+', str_replace(['-', '_'], ' ', $value)) ?: [];

        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return self::$studlyCache[$key] = implode($studlyWords);
    }

    public static function kebab(string $value): string
    {
        return self::snake($value, '-');
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(self::$snakeCache[$key][$delimiter])) {
            return self::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value)) ?? '';

            $value = mb_strtolower(
                preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value) ?? '',
                'UTF-8',
            );
        }

        return self::$snakeCache[$key][$delimiter] = $value;
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function flushCache(): void
    {
        self::$snakeCache = [];
        self::$camelCache = [];
        self::$studlyCache = [];
    }
}
