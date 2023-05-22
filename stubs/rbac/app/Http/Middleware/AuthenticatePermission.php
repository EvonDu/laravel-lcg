<?php

namespace App\Http\Middleware;

use App\Expand\Lcg\Rbac\Models\RoleUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticatePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, String $permission)
    {
        //获取当前用户
        $user = Auth::user();
        if(empty($user)){
            return redirect()->route('login');
        }

        //获取权限列表
        $permissions = [];
        $list = RoleUser::findAll(["user_id"=>$user->id]);
        foreach ($list as $item){
            $permissions = array_merge($permissions, $item->role->permissions);
        }

        //判断存在权限
        if (!in_array($permission, $permissions)) {
            throw new UnauthorizedHttpException('Task was updated prior to your request.');
        }

        //通过权限验证
        return $next($request);
    }
}
