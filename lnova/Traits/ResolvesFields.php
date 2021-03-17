<?php

namespace Lnova\Traits;

use Closure;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Nova\Contracts\Cover;
use Laravel\Nova\Contracts\Deletable;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Downloadable;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use Lnova\Contracts\Resolvable;
use Lnova\FieldCollection;

trait ResolvesFields
{
    /**
     * Resolve the index fields.
     *
     * @return FieldCollection
     */
    public function indexFields()
    {
        return $this->availableFields()
            ->filterForIndex($this->resource)
            ->withoutListableFields()
            ->authorized()
            ->each(function ($field) {
                if ($field instanceof Resolvable && ! $field->pivot) {
                    $field->resolveForDisplay($this->resource);
                }
            });
    }

    /**
     * Resolve the given fields to their values.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Closure|null  $filter
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function resolveFields(NovaRequest $request, Closure $filter = null)
    {
        $fields = $this->resolveNonPivotFields($request);

        if (! is_null($filter)) {
            $fields = $filter($fields);
        }

        return $request->viaRelationship()
            ? $this->withPivotFields($request, $fields->all())
            : $fields;
    }

    /**
     * Resolve the non pivot fields for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function resolveNonPivotFields(NovaRequest $request)
    {
        return $this->availableFields($request)
            ->resolve($this->resource)
            ->authorized($request);
    }

    protected function resolveFieldsForDetail(NovaRequest $request, Closure $filter)
    {
        $fields = $this->resolveNonPivotFields($request);

        return $request->viaRelationship()
                    ? $this->withPivotFields($request, $fields->all())
                    : $fields;
    }

    /**
     * Resolve the field for the given attribute.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $attribute
     * @return \Laravel\Nova\Fields\Field
     */
    public function resolveFieldForAttribute(NovaRequest $request, $attribute)
    {
        return $this->resolveFields($request)->findFieldByAttribute($attribute);
    }

    /**
     * Resolve the inverse field for the given relationship attribute.
     *
     * This is primarily used for Relatable rule to check if has-one / morph-one relationships are "full".
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $attribute
     * @param  string|null  $morphType
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function resolveInverseFieldsForAttribute(NovaRequest $request, $attribute, $morphType = null)
    {
        $field = $this->availableFields($request)
                      ->authorized($request)
                      ->findFieldByAttribute($attribute);

        if (! isset($field->resourceClass)) {
            return new FieldCollection;
        }

        $relatedResource = $field instanceof MorphTo
                                ? Nova::resourceForKey($morphType ?? $request->{$attribute.'_type'})
                                : ($field->resourceClass ?? null);

        $relatedResource = new $relatedResource($relatedResource::newModel());

        $result = $relatedResource->availableFields($request)->reject(function ($f) use ($field) {
            return isset($f->attribute) &&
                   isset($field->inverse) &&
                   $f->attribute !== $field->inverse;
        })->filter(function ($field) use ($request) {
            return isset($field->resourceClass) &&
                   $field->resourceClass == $request->resource();
        });

        return $result;
    }

    /**
     * Resolve the resource's avatar field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Contracts\Cover|null
     */
    public function resolveAvatarField(NovaRequest $request)
    {
        return tap($this->availableFields($request)
            ->authorized($request)
            ->whereInstanceOf(Cover::class)
            ->first(),
            function ($field) {
                if ($field instanceof Resolvable) {
                    $field->resolve($this->resource);
                }
            }
        );
    }

    /**
     * Resolve the resource's avatar URL, if applicable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return string|null
     */
    public function resolveAvatarUrl(NovaRequest $request)
    {
        $field = $this->resolveAvatarField($request);

        if ($field) {
            return $field->resolveThumbnailUrl();
        }
    }

    /**
     * Determine whether the resource's avatar should be rounded, if applicable.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return bool
     */
    public function resolveIfAvatarShouldBeRounded(NovaRequest $request)
    {
        $field = $this->resolveAvatarField($request);

        if ($field) {
            return $field->isRounded();
        }

        return false;
    }

    /**
     * Get the panels that are available for the given create request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function availablePanelsForCreate($request)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForCreate($request->newResource()), $request);
    }

    /**
     * Get the panels that are available for the given update request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Resource  $resource
     * @return array
     */
    public function availablePanelsForUpdate(NovaRequest $request, Resource $resource = null)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForUpdate($resource ?? $request->newResource()), $request);
    }

    /**
     * Get the panels that are available for the given detail request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param \Laravel\Nova\Resource $resource
     * @return array
     */
    public function availablePanelsForDetail(NovaRequest $request, Resource $resource)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForDetail($resource ?? $request->newResource()), $request);
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @return FieldCollection
     */
    public function availableFields()
    {
        return FieldCollection::make(array_values($this->filter($this->table->fields())));
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $methods
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function buildAvailableFields(NovaRequest $request, array $methods)
    {
        $fields = collect([
            method_exists($this, 'fields') ? $this->fields($request) : [],
        ]);

        $methods = collect($methods)
            ->filter(function ($method) {
                return $method != 'fields';
            })->each(function ($method) use ($request, $fields) {
                $fields->push([$this->{$method}($request)]);
            });

        return FieldCollection::make(array_values($this->filter($fields->flatten()->all())));
    }

    /**
     * Merge the available pivot fields with the given fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function withPivotFields(NovaRequest $request, array $fields)
    {
        $pivotFields = $this->resolvePivotFields($request, $request->viaResource)->all();

        if ($index = $this->indexToInsertPivotFields($request, $fields)) {
            array_splice($fields, $index + 1, 0, $pivotFields);
        } else {
            $fields = array_merge($fields, $pivotFields);
        }

        return FieldCollection::make($fields);
    }

    /**
     * Resolve the pivot fields for the requested resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function resolvePivotFields(NovaRequest $request, $relatedResource)
    {
        $fields = $this->pivotFieldsFor($request, $relatedResource);

        return FieldCollection::make($this->filter($fields->each(function ($field) use ($request, $relatedResource) {
            if ($field instanceof Resolvable) {
                $accessor = $this->pivotAccessorFor($request, $relatedResource);

                $field->resolve($this->{$accessor} ?? new Pivot);
            }
        })->authorized($request)->all()))->values();
    }

    /**
     * Get the pivot fields for the resource and relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function pivotFieldsFor(NovaRequest $request, $relatedResource)
    {
        $field = $this->availableFields($request)->first(function ($field) use ($relatedResource) {
            return isset($field->resourceName) &&
                   $field->resourceName == $relatedResource &&
                   ($field instanceof BelongsToMany || $field instanceof MorphToMany);
        });

        if ($field && isset($field->fieldsCallback)) {
            return FieldCollection::make(array_values(
                $this->filter(call_user_func($field->fieldsCallback, $request, $this->resource))
            ))->each(function ($field) {
                $field->pivot = true;
            });
        }

        return FieldCollection::make();
    }

    /**
     * Get the name of the pivot accessor for the requested relationship.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return string
     */
    public function pivotAccessorFor(NovaRequest $request, $relatedResource)
    {
        $field = $this->availableFields($request)->first(function ($field) use ($relatedResource) {
            return ($field instanceof BelongsToMany ||
                    $field instanceof MorphToMany) &&
                   $field->resourceName == $relatedResource;
        });

        return $this->resource->{$field->manyToManyRelationship}()->getPivotAccessor();
    }

    /**
     * Get the index where the pivot fields should be spliced into the field array.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $fields
     * @return int
     */
    protected function indexToInsertPivotFields(NovaRequest $request, array $fields)
    {
        foreach ($fields as $index => $field) {
            if (isset($field->resourceName) &&
                $field->resourceName == $request->viaResource) {
                return $index;
            }
        }
    }

    /**
     * Get the displayable pivot model name from a field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $field
     * @return string|null
     */
    public function pivotNameForField(NovaRequest $request, $field)
    {
        $field = $this->availableFields($request)->findFieldByAttribute($field);

        if (! $field || (! $field instanceof BelongsToMany &&
                         ! $field instanceof MorphToMany)) {
            return self::DEFAULT_PIVOT_NAME;
        }

        if (isset($field->pivotName)) {
            return $field->pivotName;
        }
    }

    /**
     * Assign the fields with the given panels to their parent panel.
     *
     * @param  string  $label
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function assignToPanels($label, FieldCollection $fields)
    {
        return $fields->map(function ($field) use ($label) {
            if (! $field->panel) {
                $field->panel = $label;
            }

            return $field;
        });
    }

    /**
     * Return the callback used for resolving fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Closure
     */
    protected function fieldResolverCallback(NovaRequest $request)
    {
        return function ($fields) use ($request) {
            $fields = $fields->values()->all();
            $pivotFields = $this->pivotFieldsFor($request, $request->viaResource)->all();

            if ($index = $this->indexToInsertPivotFields($request, $fields)) {
                array_splice($fields, $index + 1, 0, $pivotFields);
            } else {
                $fields = array_merge($fields, $pivotFields);
            }

            return FieldCollection::make($fields);
        };
    }
}
