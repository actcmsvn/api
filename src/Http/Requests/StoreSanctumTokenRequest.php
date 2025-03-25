<?php

namespace ACTCMS\Api\Http\Requests;

use ACTCMS\Support\Http\Requests\Request;

class StoreSanctumTokenRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
        ];
    }
}
