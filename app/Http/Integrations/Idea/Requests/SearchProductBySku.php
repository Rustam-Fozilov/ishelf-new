<?php

namespace App\Http\Integrations\Idea\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchProductBySku extends Request
{
    public function __construct(protected int|string $sku)
    {
    }

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "products/find/sku/$this->sku";
    }
}
