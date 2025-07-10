<?php

namespace Actcmsvn\Api\Models;

use Actcmsvn\Base\Contracts\BaseModel;
use Actcmsvn\Base\Models\Concerns\HasBaseEloquentBuilder;
use Actcmsvn\Base\Models\Concerns\HasMetadata;
use Actcmsvn\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken implements BaseModel
{
    use HasMetadata;
    use HasUuidsOrIntegerIds;
    use HasBaseEloquentBuilder;
}
