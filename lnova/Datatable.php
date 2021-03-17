<?php

namespace Lnova;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

abstract class Datatable extends Component
{
    /**
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public $tableStyle = 'table-default';

    /**
     * Whether to show borders for each column on the X-axis.
     *
     * @var bool
     */
    public $showColumnBorders = false;

    abstract public function fields(): array;

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery($query)
    {
        return $query;
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    protected function getResources(): Collection
    {
        return User::all()->transform(function ($el) {
            return new Resource($el, $this);
        });
    }

    public function render()
    {
        return view('livewire.lnova-table', [
            'resources' => $this->getResources()
        ]);
    }
}
