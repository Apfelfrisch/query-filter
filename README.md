# Query Filter

![Unit tests](https://github.com/Apfelfrisch/query-filter/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/query-filter/actions/workflows/phpstan.yml/badge.svg)
![Mutation tests](https://github.com/Apfelfrisch/query-filter/actions/workflows/infection.yml/badge.svg)

This Package provides a Framework agnostic Query Filter wich allows you to filter and sort queries based on the provided URL parameters. It comes with built-in adapters for Eloquent and Doctrine QueryBuilder, but can be easily extended to support other query building tools as well.


## Basic Usage

### Filter Users with the Name John 

`/users?filter[name]=John`

```php
use Apfelfrisch\QueryFilter\QueryFilter;
use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;

$queryBuilder = UserModel::query();

// Explicitly parse parameters
$users = QueryFilter::new()
  ->allowFilters('name')
  ->applyOn($queryBuilder, $request->all())
  ->get();

// Implicitly parse parameters over $_GET
$users = QueryFilter::new()
  ->allowFilters('name')
  ->applyOn($queryBuilder)
  ->get();

// Obtaining the CriteriaCollection which can be passed into a Repository, for example.
$criterias = QueryFilter::new()
  ->allowFilters('name')
  ->getCriterias();

$result = $criterias->applyOn(new EloquentQueryBuilder($queryBuilder))
```

### Filter Users with the Name John or Doe

`/users?filter[name]=John,Doe`

### Filter Users with name John and last name Doe

`/users?filter[name]=John&filter[lastname]=Doe`

```php
$users = QueryFilter::new()
  ->allowFilters('name', 'lastname')
  ->applyOn($queryBuilder)
  ->get();
```

### Sort Users by descending name

`/users?sort=name`

```php
$users = QueryFilter::new()
  ->allowSorts('name')
  ->applyOn($queryBuilder)
  ->get();
```

### Sort Users by ascing name and descending created_at 

`/users?sort=name,-created_at`

```php
$users = QueryFilter::new()
  ->allowSorts('name')
  ->applyOn($queryBuilder)
  ->get();
```

### Specify FilterCriteria

`/users?filter[name]=John`

```php
use Apfelfrisch\QueryFilter\Criterias;

// Filter with name = "John"
$users = QueryFilter::new()
  ->allowFilters(new Criterias\ExactFilter('name'))
  ->applyOn($queryBuilder)
  ->get();

// Filter with name like "%John"
$users = QueryFilter::new()
  ->allowFilters(new Criterias\LeftStrictPartialFilterr('name'))
  ->applyOn($queryBuilder)
  ->get();
```

### Write custom Citerias
todo

### Write custom QueryParser
todo

### Write custom QueryBuilderAdaper
todo
