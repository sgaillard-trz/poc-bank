<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

interface RequestInterface
{
    static function getInputValidators(Request $request): array;
}
