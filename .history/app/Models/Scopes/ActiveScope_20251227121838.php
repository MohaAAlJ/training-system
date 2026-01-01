<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        // 1. Check for boolean 'is_active'
        if (\Schema::hasColumn($table, 'is_active')) {
            $builder->where($table . '.is_active', true);
            return;
        }

        // 2. Check for 'status' column
        if (\Schema::hasColumn($table, 'status')) {
            $columnType = \Schema::getColumnType($table, 'status');

            if ($columnType === 'boolean' || $columnType === 'integer' || $columnType === 'tinyint') {
                $builder->where($table . '.status', true);
            } else {
                // Assume string/enum
                $builder->where($table . '.status', 'active');
            }
        }
    }
}
