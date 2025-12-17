<?php

namespace App\Http\Integrations\Anketa\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class GetPromoBankRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(protected int $category_sku)
    {
    }

    public function resolveEndpoint(): string
    {
        return '/api/all_projects/get/promo_bank_products';
    }

    protected function defaultBody(): array
    {
        return [
            'category_sku' => $this->category_sku,
        ];
    }
}
