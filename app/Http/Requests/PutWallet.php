<?php

namespace App\Http\Requests;

use App\Rules\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PutWallet implements RequestInterface
{
    static function getInputValidators(Request $request): array
    {
        return [
            "name" => "",
            "address" => "",
            "postcode" => "",
            "city" => "",
            "iban" => "prohibited",
            "currency" => [new Currency()],
            "createdAt" => "prohibited",
            "balance" => "prohibited",
            "id" => "prohibited",
            "updated" => "prohibited",
        ];
    }
}
