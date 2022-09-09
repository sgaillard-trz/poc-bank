<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostPayin implements RequestInterface
{
    static function getInputValidators(Request $request): array
    {
        return [
            "id" => "prohibited",
            "createdAt" => "prohibited",
            "amount" => "numeric",
            "currency" => ["required", Rule::in(["EUR"])],
            "beneficiaryIban" => "required",
        ];
    }
}
