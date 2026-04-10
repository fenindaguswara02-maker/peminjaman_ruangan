<?php

namespace App\Traits;

trait Filterable
{
    /**
     * Filter scope untuk model Eloquent
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            // Skip jika value null atau empty string
            if (is_null($value) || $value === '') {
                continue;
            }

            // Jika ada custom scope (scopeStatus, scopeSearch, dll)
            $scopeMethod = 'scope' . ucfirst($key);
            if (method_exists($this, $scopeMethod)) {
                $query->$key($value);
                continue;
            }

            // Filter untuk relasi
            if (str_contains($key, '.')) {
                $relation = strtok($key, '.');
                $column = strtok('.');
                
                if (method_exists($this, $relation)) {
                    $query->whereHas($relation, function($q) use ($column, $value) {
                        $q->where($column, 'like', '%'.$value.'%');
                    });
                }
                continue;
            }

            // Filter untuk kolom biasa
            if (in_array($key, $this->fillable)) {
                $query->where($key, $value);
            }
        }

        return $query;
    }
}