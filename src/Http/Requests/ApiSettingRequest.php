<?php

namespace ACTCMS\Api\Http\Requests;

use ACTCMS\Base\Rules\OnOffRule;
use ACTCMS\Support\Http\Requests\Request;

class ApiSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'api_enabled' => [new OnOffRule()],
        ];
    }
}
