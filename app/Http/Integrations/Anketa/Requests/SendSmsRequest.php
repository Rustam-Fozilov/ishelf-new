<?php

namespace App\Http\Integrations\Anketa\Requests;

use Saloon\Http\Request;
use Saloon\Enums\Method;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class SendSmsRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;


    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/api/projects/send_sms_service';
    }

    public function __construct(
        protected string $phone,
        protected string $message,
    ) {
        //
    }

    public function defaultBody(): array
    {
        return [
            'text'    => $this->message,
            'phone'   => $this->phone,
            'project' => 'ishelf',
        ];
    }
}
