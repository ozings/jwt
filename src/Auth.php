<?php

namespace ozings;

use ozings\exception\ApiException;
use ozings\facade\Role;

class Auth
{
    /**
     * 验证后台接口权限
     */
    public static function guard($request)
    {
        $routeInfo = $request->routeInfo();
        $route = Role::getRoleRoute($request->admin_role_id);
    
        if(!$route) throw new ApiException('权限不足');

        //检测路由规则，权限不足
        if (!in_array($routeInfo['route'],$route)) {
            throw new ApiException('权限不足');
        }
    }
}
