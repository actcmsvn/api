<?php

namespace ACTCMS\Api\Forms\Settings;

use ACTCMS\Api\Facades\ApiHelper;
use ACTCMS\Api\Http\Requests\ApiSettingRequest;
use ACTCMS\Base\Forms\FieldOptions\OnOffFieldOption;
use ACTCMS\Base\Forms\Fields\OnOffCheckboxField;
use ACTCMS\Setting\Forms\SettingForm;

class ApiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(ApiSettingRequest::class)
            ->setSectionTitle(trans('packages/api::api.setting_title'))
            ->setSectionDescription(trans('packages/api::api.setting_description'))
            ->contentOnly()
            ->add(
                'api_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('packages/api::api.api_enabled'))
                    ->value(ApiHelper::enabled())
                    ->toArray()
            );
    }
}
