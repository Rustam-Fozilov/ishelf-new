<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Resource extends ResourceCollection
{
    public function __construct(
        $resource,
        protected array $requestFilter = [],
    )
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $data = $this->collection;

        return [
            'data' => $data,
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
            ],
            'filter' => $this->requestFilter,
        ];
    }

    public function paginationInformation(): array
    {
        return [];
    }
}
