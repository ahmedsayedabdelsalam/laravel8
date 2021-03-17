<?php

namespace App\Http\Livewire;

use Lnova\Datatable;
use Lnova\ID;
use Lnova\Text;

class UsersTable extends Datatable
{
    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name', function ($model) {
                return '<em class="underline">'.$model->name.'</em>';
            })
                ->asHtml()
                ->sortable(),
        ];
    }
}
