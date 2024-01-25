<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class AbstractFilter
{
    protected Builder $query;

    final public function filterByDecorators(Builder $query, array $queryStrings = []): Builder
    {
        $this->query = $query;
        $queryStrings = $this->toCamelCase($queryStrings);
        foreach ($this->getFilterMethods() as $filterMethod) {
            if (isset($queryStrings[$filterMethod])) {
                $this->$filterMethod($queryStrings[$filterMethod]);
            }
        }

        return $query;
    }

    private function toCamelCase(array $queryStrings): array
    {
        $formatted = [];
        foreach ($queryStrings as $name => $value) {
            $formatted[Str::camel($name)] = $value;
        }

        return $formatted;
    }

    private function getFilterMethods(): array
    {
        $parentMethods = get_class_methods(get_parent_class($this));
        return array_filter(get_class_methods($this), static function (string $method) use ($parentMethods) {
            // remove magic methods and parent class methods
            return !in_array($method, $parentMethods, true)
                && !Str::startsWith($method, '__');
        });
    }
}
