<?php

namespace Actcmsvn\Api\Tables;

use Actcmsvn\Api\Models\PersonalAccessToken;
use Actcmsvn\Table\Abstracts\TableAbstract;
use Actcmsvn\Table\Actions\DeleteAction;
use Actcmsvn\Table\BulkActions\DeleteBulkAction;
use Actcmsvn\Table\Columns\Column;
use Actcmsvn\Table\Columns\CreatedAtColumn;
use Actcmsvn\Table\Columns\DateTimeColumn;
use Actcmsvn\Table\Columns\IdColumn;
use Actcmsvn\Table\Columns\NameColumn;
use Actcmsvn\Table\HeaderActions\CreateHeaderAction;
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
