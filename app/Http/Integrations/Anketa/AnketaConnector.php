<?php

namespace App\Http\Integrations\Anketa;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class AnketaConnector extends Connector
{
    use AcceptsJson;

    public function __construct(
        protected $token = null
    )
    {
        //
    }

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
        $token = $this->token ?? config('services.anketa.token');

        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
        ];
    }
}
