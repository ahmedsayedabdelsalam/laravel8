<?php


namespace Lnova\Contracts;


interface Resolvable
{
    /**
     * Resolve the element's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null);
}
