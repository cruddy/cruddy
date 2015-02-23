<?php

namespace Kalnoy\Cruddy\Http\Middleware;

use Closure;
use DB;
use Illuminate\Http\Request;

class RunInTransaction {

    /**
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return DB::transaction(function () use ($request, $next)
        {
            return $next($request);
        });
    }
}