<?php

namespace App\Http\Integrations\Anketa;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class AnketaConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.anketa.base_url');
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.anketa.token'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [
            'timeout' => 120,
        ];
    }
}
