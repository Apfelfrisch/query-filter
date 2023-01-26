<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Adapters\DoctrineQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\SimpleQueryParser;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Exceptions\QueryFilterException;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class Settings
{
    private bool $skipForbiddenCriterias = false;

    private QueryParser $queryParser;

    /** @var array<class-string, class-string<QueryBuilder<mixed>>> */
    private array $adapterMappings = [];

    /** @phpstan-var class-string<Filter> */
    private string $defaultFilterClass;

    public function __construct()
    {
        $this->loadDefaults();
    }

    public function setSkipForbiddenCriterias(bool $skip = true): self
    {
        $this->skipForbiddenCriterias = $skip;

        return $this;
    }

    public function skipForbiddenCriterias(): bool
    {
        return $this->skipForbiddenCriterias;
    }

    public function setQueryParser(QueryParser $queryParser): self
    {
        $this->queryParser = $queryParser;

        return $this;
    }

    public function getQueryParser(): QueryParser
    {
        return $this->queryParser;
    }

    /** @param class-string<Filter> $filterClass */
    public function setDefaultFilterClass(string $filterClass): self
    {
        if (! is_subclass_of($filterClass, Filter::class)) {
            throw new QueryFilterException("[" . self::class . "::setDefaultFilterClass] only exepts class strings of [" . Filter::class . "]");
        }

        $this->defaultFilterClass = $filterClass;

        return $this;
    }

    /** @phpstan-return class-string<Filter> */
    public function getDefaultFilterClass(): string
    {
        return $this->defaultFilterClass;
    }

    /**
     * @template T of QueryBuilder
     * @phpstan-param class-string $adaptable
     * @phpstan-param class-string<T> $adapter
     */
    public function addQueryBuilderMapping(string $adaptable, string $adapter): self
    {
        if (! class_exists($adaptable)) {
            throw new QueryFilterException("Unkown adaptable QueryBuilder class [$adaptable]");
        }

        $interfaces = class_implements($adapter) ?: [];

        if (! array_key_exists(QueryBuilder::class, $interfaces)) {
            throw new QueryFilterException("Adapter [$adapter] must implement [" . QueryBuilder::class . "].");
        }

        $this->adapterMappings[$adaptable] = $adapter;

        return $this;
    }

    /**
     * @template T of object
     * @param T $adaptableInstance
     * @return QueryBuilder<T>
     */
    public function adaptQueryBuilder(object $adaptableInstance): QueryBuilder
    {
        foreach ($this->adapterMappings as $adaptableClass => $adapter) {
            if ($adaptableInstance instanceof $adaptableClass) {
                /** @var QueryBuilder<T> */
                return new $adapter($adaptableInstance);
            }
        }

        throw new QueryFilterException("Could not find Adapter for [" . $adaptableInstance::class . "]");
    }

    private function loadDefaults(): void
    {
        try {
            $this->setQueryParser(new SimpleQueryParser);
            $this->setDefaultFilterClass(PartialFilter::class);

            if (class_exists(EloquentBuilder::class)) {
                $this->addQueryBuilderMapping(EloquentBuilder::class, EloquentQueryBuilder::class);
            }

            if (class_exists(DoctrineBuilder::class)) {
                $this->addQueryBuilderMapping(DoctrineBuilder::class, DoctrineQueryBuilder::class);
            }
        } catch (QueryFilterException) {
        }
    }
}
