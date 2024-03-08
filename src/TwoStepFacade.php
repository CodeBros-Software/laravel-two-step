<?php

namespace CodeBros\TwoStep;

use CodeBros\TwoStep\Http\Middleware\TwoStepMiddleware;
use Illuminate\Support\Facades\Facade;

class TwoStepFacade extends Facade
{
    /**
     * Gets the facade accessor.
     *
     * @return string The facade accessor.
     */
    protected static function getFacadeAccessor()
    {
        return TwoStepMiddleware::class;
    }
}
