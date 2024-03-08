<?php

namespace CodeBros\TwoStep\Http\Middleware;

use Closure;
use CodeBros\TwoStep\Traits\TwoStepTrait;
use Illuminate\Http\Request;
use Random\RandomException;

class TwoStepMiddleware
{
    use TwoStepTrait;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $response
     *
     * @return mixed
     * @throws RandomException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $uri = $request->path();
        $nextUri = config('app.url').'/'.$uri;

        switch ($uri) {
            case 'verification/needed':
            case 'password/reset':
            case 'register':
            case 'logout':
            case 'login':
            case '/':
                break;

            default:
                session(['nextUri' => $nextUri]);

                if (config('laravel-two-step.laravel2stepEnabled')) {
                    if ($this->twoStepVerification($request) !== true) {
                        return redirect('verification/needed');
                    }
                }
                break;
        }

        return $response;
    }
}
