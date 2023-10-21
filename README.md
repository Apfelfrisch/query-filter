# Query Filter

![Unit tests](https://github.com/Apfelfrisch/query-filter/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/query-filter/actions/workflows/phpstan.yml/badge.svg)
![Mutation tests](https://github.com/Apfelfrisch/query-filter/actions/workflows/infection.yml/badge.svg)

This Package provides a Framework agnostic Query Filter wich allows you to filter and sort queries based on the provided URL parameters. It comes with built-in adapters for Eloquent and Doctrine QueryBuilder, but can be extended to support other query building tools as well. The built-in URL parser supports basic [JSON API](https://jsonapi.org/) functionality, and can be swapped out for a customized parser if needed.

## Yet another query filter - but why?

I was searching for a query-filter that separates the criteria builder from the SQL builder, this way the criteria can be created in one location and then passed to another, which is useful for adding them into repositories. Additionally, I needed the capability to write custom QueryBuilderAdapter for different implementations, mainly for using it with In-Memory Repositories for testing purposes.

## Installation

`composer require apfelfrisch/query-filter`

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

### Sort Users ascending by name

`/users?sort=name`

```php
$users = QueryFilter::new()
  ->allowSorts('name')
  ->applyOn($queryBuilder)
  ->get();
```

### Sort Users ascending by name and descending created_at 

`/users?sort=name,-created_at`

```php
$users = QueryFilter::new()
  ->allowSorts('name', 'created_at')
  ->applyOn($queryBuilder)
  ->get();
```

### Allow only specfic fields

If needed, you can restrict the selected fields. If you do so, you must specify the fields via URI parameter. In the example below, only name and lastname will be selected.

`/users?fields=name,lastname`

```php
$users = QueryFilter::new()
  ->allowFields('name', 'lastname', 'street')
  ->applyOn($queryBuilder)
  ->get();
```

### Skipping forbidden criterias

By default, this package throws an exception if a filter or sort criteria is requested but not allowed. You can silently skip forbidden criterias like so:

```php
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\QueryFilter;

// via Settings injection
$settings = new Settings;
$settings->setSkipForbiddenCriterias();

new QueryFilter($settings);

// directly on the QueryFilter
QueryFilter::new()->skipForbiddenCriterias()
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
  ->allowFilters(new Criterias\LeftStrictPartialFilter('name'))
  ->applyOn($queryBuilder)
  ->get();
```

### Write a custom Filter
Your customer Filter has to implement the [Filter interface](https://github.com/Apfelfrisch/query-filter/blob/main/src/Criterias/Filter.php). Concrete implementations can be found in the [Criterias](https://github.com/Apfelfrisch/query-filter/tree/main/src/Criterias) folder.

If you want to set your Filter as default, you can do it like so:

```php
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\QueryFilter;

$settings = new Settings();
$settings->setDefaultFilterClass(MyCustomerFilterClass::class);

$queryFilter = new QueryFilter($settings);
```

### Write custom QueryParser
todo

### Write custom QueryBuilderAdaper
todo
