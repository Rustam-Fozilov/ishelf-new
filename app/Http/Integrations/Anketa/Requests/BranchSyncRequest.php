<?php

namespace App\Http\Integrations\Anketa\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class BranchSyncRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        // TODO: url ni to'g'irlash kerak
        return '/example';
    }
}
