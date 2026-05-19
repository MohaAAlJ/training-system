<?php

namespace App\Support\AiSearch;

final class AiTableSearchResult
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly string $resource,
        public readonly string $rawQuery,
        public readonly bool $success,
        public readonly array $filters = [],
        public readonly ?string $summary = null,
        public readonly ?string $keyword = null,
        public readonly ?string $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function success(
        string $resource,
        string $rawQuery,
        array $filters,
        ?string $summary = null,
        ?string $keyword = null,
    ): self {
        return new self(
            resource: $resource,
            rawQuery: $rawQuery,
            success: true,
            filters: $filters,
            summary: $summary,
            keyword: $keyword,
        );
    }

    public static function failure(string $resource, string $rawQuery, string $error): self
    {
        return new self(
            resource: $resource,
            rawQuery: $rawQuery,
            success: false,
            error: $error,
        );
    }
}
