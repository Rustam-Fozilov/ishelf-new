<?php

namespace App\Http\Integrations\Invoice\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserByPinflRequest extends Request
{
    public function __construct(
        protected $pinfl
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
        return '/api/ru/rouming/company/info?tin=' . $this->pinfl;
    }
}
