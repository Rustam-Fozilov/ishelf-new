<?php

namespace App\Http\Integrations\Idea\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ProductAttributeRequest extends Request
{
    public function __construct(
        protected int|string $product_id
    )
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
        return "v2/products/$this->product_id/attributes";
    }
}
