<?php

namespace ACTCMS\Api\Http\Requests;

use ACTCMS\Support\Http\Requests\Request;

class ResendEmailVerificationRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Địa chỉ email để gửi lại xác minh',
                'example' => 'john.smith@example.com',
            ],
        ];
    }
}
