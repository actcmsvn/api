<?php

namespace Actcmsvn\Api\Forms;

use Actcmsvn\Api\Http\Requests\StoreSanctumTokenRequest;
use Actcmsvn\Api\Models\PersonalAccessToken;
use Actcmsvn\Base\Forms\FieldOptions\NameFieldOption;
use Actcmsvn\Base\Forms\Fields\TextField;
use Actcmsvn\Base\Forms\FormAbstract;

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
