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
            'api_key' => ['nullable', 'string', 'max:255'],
            'push_notifications_enabled' => [new OnOffRule()],
            'fcm_project_id' => ['nullable', 'string', 'max:255'],
            'fcm_service_account_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'api_enabled' => [
                'description' => 'Bật hoặc tắt API',
                'example' => 'on',
            ],
            'api_key' => [
                'description' => 'Khóa API để xác thực (tùy chọn)',
                'example' => 'your-secret-api-key',
            ],
            'push_notifications_enabled' => [
                'description' => 'Bật hoặc tắt thông báo đẩy',
                'example' => 'on',
            ],
            'fcm_project_id' => [
                'description' => 'ID dự án Firebase',
                'example' => 'my-firebase-project',
            ],
            'fcm_service_account_path' => [
                'description' => 'Đường dẫn đến tệp JSON của tài khoản dịch vụ Firebase',
                'example' => 'firebase/service-account.json',
            ],
        ];
    }
}
