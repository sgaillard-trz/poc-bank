<?php

namespace App\Rules;

class Currency extends \Spatie\ValidationRules\Rules\Currency
{
    public function message(): string
    {
        return __('validationRules.currency', [
            'attribute' => $this->attribute,
        ]);
    }
}
