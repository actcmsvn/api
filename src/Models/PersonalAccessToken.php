<?php

namespace ACTCMS\Api\Models;

use ACTCMS\Base\Contracts\BaseModel;
use ACTCMS\Base\Models\Concerns\HasBaseEloquentBuilder;
use ACTCMS\Base\Models\Concerns\HasMetadata;
use ACTCMS\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken implements BaseModel
{
    use HasMetadata;
    use HasUuidsOrIntegerIds;
    use HasBaseEloquentBuilder;
}
