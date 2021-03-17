<?php

namespace Lnova\Traits;

use Closure;

trait AuthorizedToSee
{
    /**
     * The callback used to authorize viewing the filter or action.
     *
     * @var \Closure|null
     */
    public $seeCallback;

    /**
     * Determine if the filter or action should be available for the given request.
     *
     * @return bool
     */
    public function authorizedToSee()
    {
        return $this->seeCallback ? call_user_func($this->seeCallback) : true;
    }

    /**
     * Set the callback to be run to authorize viewing the filter or action.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function canSee(Closure $callback)
    {
        $this->seeCallback = $callback;

        return $this;
    }
}
