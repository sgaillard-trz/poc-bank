<?php

namespace App\Http\Requests;

use App\Rules\Currency;
use Illuminate\Http\Request;

class PostWallet implements RequestInterface
{
    static function getInputValidators(Request $request): array
    {
        return [
            "name" => "required",
            "address" => "required",
            "postcode" => "required",
            "city" => "required",
            "currency" => [new Currency()],
            "iban" => "prohibited",
            "createdAt" => "prohibited",
            "balance" => "prohibited",
            "id" => "prohibited",
            "updated" => "prohibited",
        ];
    }
}
