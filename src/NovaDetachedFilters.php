<?php

namespace OptimistDigital\NovaDetachedFilters;

use Laravel\Nova\Card;

class NovaDetachedFilters extends Card
{
    public $width = '1/3'; // (full, 1/3, 1/2 etc..)
    public $filters = [];
    protected $withReset = false;
    protected $withToggle = false;
    protected $persistFilters = false;
    protected $perPageOptions = null;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function component()
    {
        return 'nova-detached-filters';
    }

    public function width($width)
    {
        if (empty($width)) return $this;
        $this->width = $width;
        return $this;
    }

    public function withReset(bool $value = true)
    {
        $this->withReset = $value;
        return $this;
    }

    public function persistFilters(bool $value = true)
    {
        $this->persistFilters = $value;
        return $this;
    }

    public function withPerPage($perPageOptions = null)
    {
        $this->perPageOptions = $perPageOptions;
        return $this;
    }

    public function withToggle(bool $value = true)
    {
        $this->withToggle = $value;
        return $this;
    }

    public function getFilters($filters = null)
    {
        $flatFilters = [];
        if (empty($filters)) $filters = is_callable($this->filters) ? $this->filters() : $this->filters;

        collect($filters)->each(function ($filter) use (&$flatFilters) {
            if (property_exists($filter, 'filters')) {
                $childFilters = $this->getFilters($filter->filters);
                $flatFilters = array_merge($childFilters, $flatFilters);
                return true;
            }

            return $flatFilters[] = $filter;
        });

        return $flatFilters;
    }

    private function serializeFilters()
    {
        return collect($this->getFilters())->each(function ($filter) {
            return $filter->jsonSerialize();
        });
    }

    private function getPerPageOptions()
    {
        if (is_callable($this->perPageOptions)) return call_user_func($this->perPageOptions);
        return is_array($this->perPageOptions) ? $this->perPageOptions : null;
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'withReset' => $this->withReset,
            'withToggle' => $this->withToggle,
            'perPageOptions' => $this->getPerPageOptions(),
            'persistFilters' => $this->persistFilters,
            'filters' => $this->serializeFilters(),
        ]);
    }
}
