<?php

namespace Lnova;

use Illuminate\Support\Collection;
use Lnova\Contracts\ListableField;
use Lnova\Contracts\Resolvable;

class FieldCollection extends Collection
{
    /**
     * Find a given field by its attribute.
     *
     * @param  string  $attribute
     * @param  mixed  $default
     * @return Field|null
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute) &&
                $field->attribute == $attribute;
        }, $default);
    }

    /**
     * Filter elements should be displayed for the given request.
     *
     * @param  mixed  $resource
     * @return $this
     */
    public function resolve($resource)
    {
        return $this->each(function ($field) use ($resource) {
            if ($field instanceof Resolvable) {
                $field->resolve($resource);
            }
        });
    }

    /**
     * Resolve value of fields for display.
     *
     * @param  mixed  $resource
     * @return $this
     */
    public function resolveForDisplay($resource)
    {
        return $this->each(function ($field) use ($resource) {
            if ($field instanceof Resolvable) {
                $field->resolveForDisplay($resource);
            }
        });
    }

    /**
     * Filter fields for showing on index.
     *
     * @param  mixed  $resource
     * @return $this
     */
    public function filterForIndex($resource)
    {
        return $this->filter(function ($field) use ($resource) {
            return $field->isShownOnIndex($resource);
        })->values();
    }

    /**
     * Reject fields which use their own index listings.
     *
     * @return $this
     */
    public function withoutListableFields()
    {
        return $this->reject(function ($field) {
            return $field instanceof ListableField;
        });
    }

    /**
     * Filter elements should be displayed for the given request.
     *
     * @return $this
     */
    public function authorized()
    {
        return $this->filter(function ($field) {
            return $field->authorize();
        })->values();
    }
}
