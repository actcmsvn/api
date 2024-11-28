<?php

namespace ACTCMS\Api\Forms;

use ACTCMS\Api\Http\Requests\StoreSanctumTokenRequest;
use ACTCMS\Api\Models\PersonalAccessToken;
use ACTCMS\Base\Forms\FieldOptions\NameFieldOption;
use ACTCMS\Base\Forms\Fields\TextField;
use ACTCMS\Base\Forms\FormAbstract;

class SanctumTokenForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new PersonalAccessToken())
            ->setValidatorClass(StoreSanctumTokenRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->toArray());
    }
}
