<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GetPayoutBy implements RequestInterface
{
    static function getInputValidators(Request $request): array
    {
        return [
            "beneficiaryIban" => [
                Rule::requiredIf(empty($request->get("creditorIban"))),
                "prohibits:creditorIban",
            ],
            "creditorIban" => [
                Rule::requiredIf(empty($request->get("beneficiaryIban"))),
                "prohibits:beneficiaryIban",
            ],
        ];
    }
}
