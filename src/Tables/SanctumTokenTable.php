<?php

namespace ACTCMS\Api\Tables;

use ACTCMS\Api\Models\PersonalAccessToken;
use ACTCMS\Table\Abstracts\TableAbstract;
use ACTCMS\Table\Actions\DeleteAction;
use ACTCMS\Table\BulkActions\DeleteBulkAction;
use ACTCMS\Table\Columns\Column;
use ACTCMS\Table\Columns\CreatedAtColumn;
use ACTCMS\Table\Columns\DateTimeColumn;
use ACTCMS\Table\Columns\IdColumn;
use ACTCMS\Table\Columns\NameColumn;
use ACTCMS\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SanctumTokenTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('packages/api::table')
            ->model(PersonalAccessToken::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('api.sanctum-token.create'))
            ->addAction(DeleteAction::make()->route('api.sanctum-token.destroy'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make(),
                Column::make('abilities')
                    ->label(trans('packages/api::sanctum-token.abilities')),
                DateTimeColumn::make('last_used_at')
                    ->label(trans('packages/api::sanctum-token.last_used_at')),
                CreatedAtColumn::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'name',
                'abilities',
                'last_used_at',
                'created_at',
            ]));
    }
}
