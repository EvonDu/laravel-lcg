<?php

namespace Lcg\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleMixBaseUrl
{
    /**
     * Handle Request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next){
        //兼容子目录路径
        if(config('app.mix_url') === null)
            config(['app.mix_url' => '.']);

        //返回并传递
        return $next($request);
    }
}
