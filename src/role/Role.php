<?php

namespace ozings\role;

use ozings\exception\ApiException;
use think\facade\Cache;
use think\Db;

class Role
{
    protected $role_id;
    protected $mids;
    
    
    /**
     * 角色权限菜单缓存
     */
    public function authMenuCache($role_id = 0,$mids = '')
    {
        if(!$role_id) return false;
        if(!$mids) return false;

        $this->role_id = $role_id;

        $this->mids = $mids;
        
        $this->setMenuCache();
    }

    /**
     * 获取角色权限菜单缓存
     */
    public function getRoleMenu($role_id = 0)
    {
        if(!$role_id) return false;
        $menu = Cache::get('auth'.$role_id);
        if (!$menu) {
            $mids = $this->getMids($role_id);
            if (!$mids) {
                $menu = [];
            } else {
                $menu = $this->setMenuCache();
            }
        }
        return $menu;
    }

    /**
     * 获取角色权限菜单路由缓存
     */
    public function getRoleRoute($role_id = 0)
    {
        if(!$role_id) return false;
        $route = Cache::get('route'.$role_id);
        if (!$route) {
            $mids = $this->getMids($role_id);
            if (!$mids) {
                $route = [];
            } else {
                $route = $this->setRouteCache();
            }
        }
        return $route;
    }
    
    /**
     * 生成角色权限菜单缓存
     */
    public function setMenuCache()
    {
        if(is_string($this->mids)) $mids = explode(',',$this->mids);

        $mids = array_unique($mids);

        $menu = Db::name('menu')->field('id,name,module_name,route')->where([
            ['id','in',$mids],
            ['type','in',0],
        ])->select();

        if(!$menu) return false;
        Cache::set('menu'.$this->role_id,$menu);

        $route = array_column($menu,'route');
        Cache::set('route'.$this->role_id,$route);
    }


    /**
     * 生成角色权限菜单路由缓存
     */
    public function setRouteCache()
    {
        if(is_string($this->mids)) self::$mids = explode(',',$this->mids);
        
        $mids = array_unique($this->mids);

        $route = Db::name('menu')->where([
            ['id','in',$mids],
            ['type','in',0],
        ])->column('route');

        if(!$route) $route =  [];
        Cache::set('route'.$this->role_id,$route);
        return $route;
    }




    /**
     * 获取角色权限菜单id
     */
    public function getMids($role_id = 0)
    {
        $mids = Db::name('role')->where('id','=',$role_id)->value('mids');
        $this->role_id = $role_id;
        $this->mids = $mids;
        return $mids;
    }
}
