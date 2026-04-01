<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogCrudOperations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log the operation based on the request method
        if ($request->isMethod('post')) {
            $this->log('create', $request);
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            $this->log('update', $request);
        } elseif ($request->isMethod('delete')) {
            $this->log('delete', $request);
        }

        return $response;
    }

    protected function log($action, Request $request)
    {
        $user = Auth::user();
        $resourceName = explode('/', $request->path())[1];
        $idPath = $request->route('id') ?? $request->route(substr($resourceName, 0, -1));

        $log = [
            'action'    => $action,
            'user_id'   => $user ? $user->id : null,
            'resource'   => $resourceName,
            'id'   => $idPath,
            'datetime'  => Carbon::now()->toDateTimeString(),
            'data'      => $request->all(),
        ];

        Log::channel('crud')->info(json_encode($log));
    }
}
