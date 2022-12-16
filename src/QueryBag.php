<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Exception;

use function Safe\parse_url;

final class QueryBag
{
    /** @param array<mixed> $parameters */
    public function __construct(
        private array $parameters = []
    ) {
    }

    public static function fromUrl(string $url): self
    {
        $parameters = parse_url($url, PHP_URL_QUERY);

        if ($parameters === null) {
            parse_str($url, $parameters);
        } else {
            /** @var string $parameters */
            parse_str($parameters, $parameters);
        }

        return new self($parameters);
    }

    /** @return array<mixed>|string|int|float|bool|null */
    public function get(string $key): array|string|int|float|bool|null
    {
        $value = $this->parameters[$key] ?? null;

        if ($value === null || is_array($value)) {
            return $value;
        }

        if (! is_scalar($value)) {
            throw new Exception("Query Parameter value [$key] contains a non-scalar value.");
        }

        return $value;
    }

    public function getString(string $key): string
    {
        $value = $this->get($key);

        if (is_array($value)) {
            throw new Exception("Query Parameter value [$key] contains an array and cannot cast to string.");
        }

        return (string)$value;
    }

    /** @return array<mixed> */
    public function getArray(string $key): array
    {
        if (! $this->has($key)) {
            return [];
        }

        $values = $this->get($key);

        if (! is_array($values)) {
            $values = [$values];
        }

        return $values;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }
}
