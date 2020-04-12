<?php

namespace Vtec\Crud\Http\Middleware;

use Closure;

class ReadOnly
{
    private $except = [];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isReadOnly($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('crud::admin.read_only')], 403);
            }

            return redirect(config('admin.url'));
        }

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    private function isReadOnly($request)
    {
        // Only Http Verbs different than GET are concerned
        if (! config('admin.read_only') || 'GET' === $request->method()) {
            return false;
        }

        // USer 1 has no limitation
        $user = auth()->user();

        if ($user && $user->id === 1) {
            return false;
        }

        return ! $this->inExceptArray($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ('/' !== $except) {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}