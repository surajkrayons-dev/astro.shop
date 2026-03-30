<?php

namespace App\CustomFacade;

class Can
{
    public function is_accessible($feature, $action = 'view')
    {
        $user_id = auth()->id();

        $permissions = session()->get('permissions');
        if (is_null($permissions) && $user_id == 1) {
            return true;
        } elseif (is_array($permissions) && isset($permissions[$feature]) && in_array($action, $permissions[$feature])) {
            return true;
        }

        return false;
    }

    public function access($feature, $action = 'view')
    {
        $user_id = auth()->id();

        $permissions = session()->get('permissions');
        if (is_null($permissions) && $user_id == 1) {
            return true;
        } elseif (is_array($permissions) && isset($permissions[$feature]) && in_array($action, $permissions[$feature])) {
            return true;
        }

        return abort(403);
    }

    public static function has($module, $action = null)
    {
        return (new self)->is_accessible($module, $action);
    }

}
