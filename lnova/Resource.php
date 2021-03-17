<?php


namespace Lnova;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Lnova\Traits\PerformsQueries;
use Lnova\Traits\ResolvesFields;

class Resource
{
    use ResolvesFields, ConditionallyLoadsAttributes, PerformsQueries;

    public $fields;

    public $id;

    public $title;

    /**
     * The underlying model resource instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $resource;
    /**
     * @var Datatable
     */
    public $table;

    public function __construct(Model $resource, Datatable $table)
    {
        $this->resource = $resource;
        $this->table = $table;
        $this->fields = collect($this->indexFields());
    }

}
